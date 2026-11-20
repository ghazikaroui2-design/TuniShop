-- =====================================================================
-- TuniShop — Fonctions et procédures stockées
-- À importer APRÈS schema.sql et AVANT triggers.sql / seed.sql
-- US-023, US-024, US-035, US-037, US-038, US-041, US-044, US-050
-- =====================================================================

USE tunishop;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Rendre le script réexécutable
DROP FUNCTION IF EXISTS fn_audit_user;
DROP FUNCTION IF EXISTS fn_audit_ip;
DROP FUNCTION IF EXISTS fn_vat_from_ttc;
DROP FUNCTION IF EXISTS fn_vat_amount_from_ttc;
DROP FUNCTION IF EXISTS fn_next_sequence;
DROP FUNCTION IF EXISTS fn_is_valid_transition;
DROP FUNCTION IF EXISTS fn_can_review;
DROP PROCEDURE IF EXISTS sp_log_action;
DROP PROCEDURE IF EXISTS sp_create_cart;
DROP PROCEDURE IF EXISTS sp_add_to_cart;
DROP PROCEDURE IF EXISTS sp_calculate_cart_totals;
DROP PROCEDURE IF EXISTS sp_purge_abandoned_carts;
DROP PROCEDURE IF EXISTS sp_place_order;
DROP PROCEDURE IF EXISTS sp_simulate_payment;
DROP PROCEDURE IF EXISTS sp_update_order_status;
DROP PROCEDURE IF EXISTS sp_validate_shop;
DROP PROCEDURE IF EXISTS sp_moderate_review;
DROP PROCEDURE IF EXISTS sp_get_dashboard_stats;
DROP PROCEDURE IF EXISTS sp_get_seller_stats;
DROP PROCEDURE IF EXISTS sp_top_products;

DELIMITER //

-- ---------------------------------------------------------------------
-- A. Fonctions utilitaires
-- ---------------------------------------------------------------------

-- Utilisateur courant transmis par la couche PHP via @current_user_id
CREATE FUNCTION fn_audit_user() RETURNS INT UNSIGNED
NO SQL
BEGIN
  RETURN NULLIF(CAST(@current_user_id AS UNSIGNED), 0);
END//

-- Adresse IP transmise par la couche PHP via @client_ip
CREATE FUNCTION fn_audit_ip() RETURNS VARCHAR(45)
NO SQL
BEGIN
  RETURN CAST(@client_ip AS CHAR(45));
END//

-- RG-02 : prix TTC → montant HT (TVA 19 % incluse)
CREATE FUNCTION fn_vat_from_ttc(p_ttc DECIMAL(10,2)) RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
  RETURN ROUND(p_ttc / 1.19, 2);
END//

-- RG-02 : prix TTC → montant de TVA
CREATE FUNCTION fn_vat_amount_from_ttc(p_ttc DECIMAL(10,2)) RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
  RETURN ROUND(p_ttc - (p_ttc / 1.19), 2);
END//

-- RG-08 : prochain numéro de séquence (ORDER / INVOICE / PAYMENT)
CREATE FUNCTION fn_next_sequence(p_name VARCHAR(30), p_year SMALLINT UNSIGNED)
RETURNS VARCHAR(10)
NOT DETERMINISTIC
MODIFIES SQL DATA
BEGIN
  DECLARE v_val INT UNSIGNED;
  INSERT INTO sequences (seq_name, seq_year, next_value)
  VALUES (p_name, p_year, 1)
  ON DUPLICATE KEY UPDATE seq_name = seq_name;
  SELECT next_value INTO v_val
  FROM sequences WHERE seq_name = p_name AND seq_year = p_year FOR UPDATE;
  UPDATE sequences SET next_value = next_value + 1
  WHERE seq_name = p_name AND seq_year = p_year;
  RETURN LPAD(v_val, 4, '0');
END//

-- RG-04 : transition de statut autorisée
CREATE FUNCTION fn_is_valid_transition(p_from VARCHAR(30), p_to VARCHAR(30))
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
  IF p_from = 'en_attente'   AND p_to IN ('annulée') THEN RETURN TRUE; END IF;
  IF p_from = 'payée'        AND p_to IN ('en_préparation','annulée','remboursée') THEN RETURN TRUE; END IF;
  IF p_from = 'en_préparation' AND p_to IN ('expédiée','annulée') THEN RETURN TRUE; END IF;
  IF p_from = 'expédiée'     AND p_to IN ('livrée') THEN RETURN TRUE; END IF;
  IF p_from = 'livrée'       AND p_to IN ('remboursée') THEN RETURN TRUE; END IF;
  RETURN FALSE;
END//

-- RG-05 : le client peut-il aviser ce produit pour cette commande ?
CREATE FUNCTION fn_can_review(
  p_user_id INT UNSIGNED, p_product_id INT UNSIGNED, p_order_id INT UNSIGNED
) RETURNS BOOLEAN
READS SQL DATA
BEGIN
  DECLARE v_ok INT DEFAULT 0;
  SELECT COUNT(*) INTO v_ok
  FROM order_items oi
  JOIN orders o ON o.id = oi.order_id
  WHERE oi.product_id = p_product_id
    AND o.id = p_order_id
    AND o.user_id = p_user_id
    AND o.status = 'livrée';
  IF v_ok > 0 AND NOT EXISTS (
    SELECT 1 FROM reviews r
    WHERE r.user_id = p_user_id AND r.product_id = p_product_id AND r.order_id = p_order_id
  ) THEN
    RETURN TRUE;
  END IF;
  RETURN FALSE;
END//

-- ---------------------------------------------------------------------
-- B. Journalisation et panier
-- ---------------------------------------------------------------------

CREATE PROCEDURE sp_log_action(
  IN p_action VARCHAR(80), IN p_entity VARCHAR(50), IN p_entity_id VARCHAR(50),
  IN p_details TEXT
)
BEGIN
  INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details)
  VALUES (fn_audit_user(), p_action, p_entity, p_entity_id,
          fn_audit_ip(), CAST(@client_ua AS CHAR(255)), p_details);
END//

CREATE PROCEDURE sp_create_cart(
  IN  p_user_id INT UNSIGNED,
  IN  p_guest_token VARCHAR(64),
  OUT p_cart_id INT UNSIGNED
)
BEGIN
  INSERT INTO carts (user_id, guest_token, status, expires_at)
  VALUES (p_user_id, p_guest_token, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
  SET p_cart_id = LAST_INSERT_ID();
END//

-- RG-01 : refuses l'ajout si stock insuffisant ou produit indisponible
CREATE PROCEDURE sp_add_to_cart(
  IN p_cart_id INT UNSIGNED, IN p_product_id INT UNSIGNED, IN p_quantity INT UNSIGNED
)
BEGIN
  DECLARE v_stock INT UNSIGNED;
  DECLARE v_status VARCHAR(20);
  DECLARE v_shop_status VARCHAR(20);

  SELECT p.stock_qty, p.status, s.status
    INTO v_stock, v_status, v_shop_status
  FROM products p JOIN shops s ON s.id = p.shop_id
  WHERE p.id = p_product_id;

  IF v_stock IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produit introuvable';
  END IF;
  IF v_status <> 'publié' OR v_shop_status <> 'validée' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produit non disponible';
  END IF;
  IF p_quantity > v_stock THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG-01 : stock insuffisant';
  END IF;

  INSERT INTO cart_items (cart_id, product_id, quantity)
  VALUES (p_cart_id, p_product_id, p_quantity)
  ON DUPLICATE KEY UPDATE quantity = p_quantity, updated_at = NOW();

  UPDATE carts
     SET status = 'actif', expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
   WHERE id = p_cart_id;
END//

-- RG-02 / RG-03 : récapitulatif financier d'un panier
CREATE PROCEDURE sp_calculate_cart_totals(IN p_cart_id INT UNSIGNED)
BEGIN
  SELECT
    v.subtotal_ttc,
    v.subtotal_ht,
    v.vat_amount,
    v.shipping_fee,
    ROUND(v.subtotal_ttc + v.shipping_fee, 2) AS total_ttc,
    v.remaining_for_free_shipping
  FROM v_cart_totals v
  WHERE v.cart_id = p_cart_id;
END//

-- RG-10 : purge des paniers abandonnés (à planifier en cron)
CREATE PROCEDURE sp_purge_abandoned_carts()
BEGIN
  UPDATE carts
     SET status = 'abandonné'
   WHERE status = 'actif' AND expires_at < NOW();

  DELETE FROM cart_items
   WHERE cart_id IN (
     SELECT id FROM carts
     WHERE status = 'abandonné' AND expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
   );

  DELETE FROM carts
   WHERE status = 'abandonné' AND expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
END//

-- ---------------------------------------------------------------------
-- C. Checkout, paiement, facturation
-- ---------------------------------------------------------------------

-- US-037 : transforme un panier en commande (transaction + verrous)
CREATE PROCEDURE sp_place_order(
  IN  p_cart_id INT UNSIGNED,
  IN  p_user_id INT UNSIGNED,
  IN  p_guest_email VARCHAR(190),
  IN  p_delivery_mode ENUM('domicile','retrait'),
  IN  p_address_id INT UNSIGNED,
  IN  p_promo_code VARCHAR(30),
  OUT p_order_id INT UNSIGNED,
  OUT p_order_number VARCHAR(30)
)
BEGIN
  DECLARE v_cart_status VARCHAR(20);
  DECLARE v_subtotal_ttc DECIMAL(10,2) DEFAULT 0;
  DECLARE v_subtotal_ht  DECIMAL(10,2) DEFAULT 0;
  DECLARE v_vat          DECIMAL(10,2) DEFAULT 0;
  DECLARE v_shipping     DECIMAL(10,2) DEFAULT 0;
  DECLARE v_discount     DECIMAL(10,2) DEFAULT 0;
  DECLARE v_total        DECIMAL(10,2) DEFAULT 0;
  DECLARE v_year         SMALLINT UNSIGNED;
  DECLARE v_seq          VARCHAR(10);
  DECLARE v_done         BOOLEAN DEFAULT FALSE;
  DECLARE v_pid          INT UNSIGNED;
  DECLARE v_qty          INT UNSIGNED;
  DECLARE v_price        DECIMAL(10,2);
  DECLARE v_promo_id     INT UNSIGNED DEFAULT NULL;
  DECLARE v_promo_type   VARCHAR(20);
  DECLARE v_promo_value  DECIMAL(10,2);
  DECLARE v_line1        VARCHAR(180) DEFAULT NULL;
  DECLARE v_postal       VARCHAR(10)  DEFAULT NULL;
  DECLARE v_city         VARCHAR(80)  DEFAULT NULL;

  -- Le curseur ne lit QUE cart_items : le trigger de stock peut ainsi mettre à jour
  -- products sans conflit « table déjà utilisée par l'instruction appelante ».
  DECLARE cur_items CURSOR FOR
    SELECT ci.product_id, ci.quantity, ci.unit_price_ttc
      FROM cart_items ci
     WHERE ci.cart_id = p_cart_id
     FOR UPDATE;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SELECT status INTO v_cart_status FROM carts WHERE id = p_cart_id FOR UPDATE;
  IF v_cart_status IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Panier introuvable';
  END IF;
  IF v_cart_status <> 'actif' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Panier deja converti ou abandonne';
  END IF;

  OPEN cur_items;
  read_loop: LOOP
    FETCH cur_items INTO v_pid, v_qty, v_price;
    IF v_done THEN LEAVE read_loop; END IF;
    SET v_subtotal_ttc = v_subtotal_ttc + (v_qty * v_price);
  END LOOP;
  CLOSE cur_items;

  IF v_subtotal_ttc = 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Panier vide';
  END IF;
  IF p_address_id IS NOT NULL THEN
    SELECT line1, postal_code, city INTO v_line1, v_postal, v_city
      FROM addresses WHERE id = p_address_id;
  END IF;

  SET v_subtotal_ht = fn_vat_from_ttc(v_subtotal_ttc);
  SET v_vat         = fn_vat_amount_from_ttc(v_subtotal_ttc);
  SET v_shipping    = CASE
                        WHEN p_delivery_mode = 'retrait' OR v_subtotal_ttc >= 100 THEN 0
                        ELSE 8
                      END;

  IF p_promo_code IS NOT NULL THEN
    SELECT id, type, value INTO v_promo_id, v_promo_type, v_promo_value
      FROM promo_codes
     WHERE code = p_promo_code AND is_active = TRUE
       AND (starts_at IS NULL OR starts_at <= NOW())
       AND (ends_at   IS NULL OR ends_at   >= NOW())
       AND (max_uses  IS NULL OR uses_count < max_uses)
       AND v_subtotal_ttc >= min_amount_ttc
     LIMIT 1;
    IF v_promo_id IS NOT NULL THEN
      SET v_discount = CASE
        WHEN v_promo_type = 'pourcentage' THEN ROUND(v_subtotal_ttc * v_promo_value / 100, 2)
        ELSE LEAST(v_promo_value, v_subtotal_ttc)
      END;
      UPDATE promo_codes SET uses_count = uses_count + 1 WHERE id = v_promo_id;
    END IF;
  END IF;

  SET v_total = ROUND(v_subtotal_ttc - v_discount + v_shipping, 2);
  SET v_year  = YEAR(NOW());
  SET v_seq   = fn_next_sequence('ORDER', v_year);
  SET p_order_number = CONCAT('CMD-', v_year, '-', v_seq);

  INSERT INTO orders (
    order_number, user_id, guest_email, status, delivery_mode,
    subtotal_ht, vat_amount, subtotal_ttc, discount_amount, shipping_fee, total_ttc,
    promo_code_id, address_id, shipping_line1, shipping_postal_code, shipping_city
  ) VALUES (
    p_order_number, p_user_id, p_guest_email, 'en_attente', p_delivery_mode,
    v_subtotal_ht, v_vat, v_subtotal_ttc, v_discount, v_shipping, v_total,
    v_promo_id, p_address_id, v_line1, v_postal, v_city
  );
  SET p_order_id = LAST_INSERT_ID();

  INSERT INTO order_items (
    order_id, product_id, shop_id, product_name, product_slug,
    unit_price_ttc, quantity, line_total_ttc
  )
  SELECT p_order_id, ci.product_id, ci.shop_id, ci.product_name, ci.product_slug,
         ci.unit_price_ttc, ci.quantity, ROUND(ci.quantity * ci.unit_price_ttc, 2)
    FROM cart_items ci
   WHERE ci.cart_id = p_cart_id;

  UPDATE carts SET status = 'converti' WHERE id = p_cart_id;

  CALL sp_log_action('ORDER_CREATED', 'orders', CAST(p_order_id AS CHAR),
                     CONCAT(p_order_number, ' — total ', v_total, ' DT'));

  COMMIT;
END//

-- US-038 / RG-07 : paiement sandbox (aucune donnée bancaire stockée)
CREATE PROCEDURE sp_simulate_payment(
  IN  p_order_id INT UNSIGNED,
  OUT p_transaction_ref VARCHAR(60)
)
BEGIN
  DECLARE v_status VARCHAR(30);
  DECLARE v_total DECIMAL(10,2);
  DECLARE v_year SMALLINT UNSIGNED;
  DECLARE v_seq  VARCHAR(10);
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;
  SELECT status, total_ttc INTO v_status, v_total
    FROM orders WHERE id = p_order_id FOR UPDATE;

  IF v_status IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Commande introuvable';
  END IF;
  IF v_status <> 'en_attente' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Paiement deja effectue ou commande clos';

  END IF;

  SET v_year = YEAR(NOW());
  SET v_seq  = fn_next_sequence('PAYMENT', v_year);
  SET p_transaction_ref = CONCAT('SBX-', v_year, '-', LPAD(v_seq, 6, '0'));

  INSERT INTO payments (order_id, transaction_ref, method, amount, status, processed_at)
  VALUES (p_order_id, p_transaction_ref, 'sandbox', v_total, 'accepté', NOW());

  UPDATE orders
     SET status = 'payée', payment_method = 'sandbox',
         transaction_ref = p_transaction_ref, paid_at = NOW()
   WHERE id = p_order_id;

  CALL sp_log_action('PAYMENT_SIMULATED', 'orders', CAST(p_order_id AS CHAR),
                     CONCAT(p_transaction_ref, ' — montant ', v_total, ' DT'));

  COMMIT;
END//

-- RG-04 : changement de statut contrôlé (le trigger écrit l'historique)
CREATE PROCEDURE sp_update_order_status(
  IN p_order_id INT UNSIGNED, IN p_new_status VARCHAR(30),
  IN p_note VARCHAR(255)
)
BEGIN
  DECLARE v_current VARCHAR(30);
  DECLARE v_msg VARCHAR(255);

  SELECT status INTO v_current FROM orders WHERE id = p_order_id FOR UPDATE;
  IF v_current IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Commande introuvable';
  END IF;
  IF p_new_status = 'payée' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Utiliser sp_simulate_payment pour passer en payee';
  END IF;
  IF v_current = p_new_status THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Statut identique';
  END IF;
  IF fn_is_valid_transition(v_current, p_new_status) = FALSE THEN
    SET v_msg = CONCAT('Transition interdite : ', v_current, ' -> ', p_new_status);
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
  END IF;

  -- La note est lue par le trigger trg_orders_au : elle doit être posée AVANT l'UPDATE
  SET @status_note = p_note;
  UPDATE orders SET status = p_new_status WHERE id = p_order_id;
END//

-- ---------------------------------------------------------------------
-- D. Validation vendeur, avis
-- ---------------------------------------------------------------------

-- RG-06 / US-041 : décision admin, publication automatique des produits en attente
CREATE PROCEDURE sp_validate_shop(
  IN p_shop_id INT UNSIGNED, IN p_admin_id INT UNSIGNED,
  IN p_decision VARCHAR(20), IN p_reason TEXT
)
BEGIN
  IF p_decision NOT IN ('validée', 'refusée') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Decision invalide';
  END IF;

  UPDATE shops
     SET status = p_decision,
         validated_at = NOW(),
         validated_by = p_admin_id,
         rejection_reason = CASE WHEN p_decision = 'refusée' THEN p_reason ELSE NULL END
   WHERE id = p_shop_id;

  IF p_decision = 'validée' THEN
    UPDATE products
       SET status = 'publié', published_at = NOW()
     WHERE shop_id = p_shop_id AND status = 'en_attente';
  END IF;

  -- L'audit SHOP_STATUS_CHANGE est déjà écrit par le trigger trg_shops_au (RG-11)
END//

-- RG-05 : modération des avis
CREATE PROCEDURE sp_moderate_review(
  IN p_review_id INT UNSIGNED, IN p_status VARCHAR(20), IN p_admin_id INT UNSIGNED
)
BEGIN
  IF p_status NOT IN ('publié', 'masqué', 'refusé', 'en_attente') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Statut de moderation invalide';
  END IF;
  UPDATE reviews
     SET status = p_status, moderated_by = p_admin_id, moderated_at = NOW()
   WHERE id = p_review_id;
  CALL sp_log_action('REVIEW_MODERATION', 'reviews', CAST(p_review_id AS CHAR), p_status);
END//

-- ---------------------------------------------------------------------
-- E. Statistiques (US-024, US-046)
-- ---------------------------------------------------------------------

CREATE PROCEDURE sp_get_dashboard_stats(IN p_start DATE, IN p_end DATE)
BEGIN
  SELECT
    (SELECT COALESCE(SUM(total_ttc), 0) FROM orders
      WHERE status IN ('payée','en_préparation','expédiée','livrée')
        AND placed_at >= p_start AND placed_at < DATE_ADD(p_end, INTERVAL 1 DAY)
    ) AS chiffre_affaires,
    (SELECT COUNT(*) FROM orders
      WHERE placed_at >= p_start AND placed_at < DATE_ADD(p_end, INTERVAL 1 DAY)
    ) AS commandes,
    (SELECT COUNT(*) FROM orders WHERE status = 'en_attente') AS en_attente,
    (SELECT COUNT(*) FROM shops WHERE status = 'validée') AS vendeurs_actifs,
    (SELECT COUNT(*) FROM shops WHERE status = 'en_attente') AS vendeurs_a_valider,
    (SELECT COUNT(*) FROM v_low_stock) AS produits_stock_faible,
    (SELECT COALESCE(AVG(total_ttc), 0) FROM orders
      WHERE status <> 'annulée'
        AND placed_at >= p_start AND placed_at < DATE_ADD(p_end, INTERVAL 1 DAY)
    ) AS panier_moyen;
END//

CREATE PROCEDURE sp_get_seller_stats(
  IN p_shop_id INT UNSIGNED, IN p_start DATE, IN p_end DATE
)
BEGIN
  SELECT
    COALESCE(SUM(oi.line_total_ttc), 0) AS chiffre_affaires,
    COUNT(DISTINCT o.id) AS commandes,
    COALESCE(SUM(oi.quantity), 0) AS articles_vendus,
    (SELECT COUNT(*) FROM products pr
      WHERE pr.shop_id = p_shop_id AND pr.status = 'publié'
        AND pr.stock_qty <= pr.low_stock_threshold
    ) AS produits_stock_faible
  FROM orders o
  JOIN order_items oi ON oi.order_id = o.id
  WHERE oi.shop_id = p_shop_id
    AND o.status IN ('payée','en_préparation','expédiée','livrée')
    AND o.placed_at >= p_start
    AND o.placed_at < DATE_ADD(p_end, INTERVAL 1 DAY);
END//

CREATE PROCEDURE sp_top_products(
  IN p_limit INT, IN p_start DATE, IN p_end DATE
)
BEGIN
  SELECT p.id, p.name, s.name AS shop_name,
         SUM(oi.quantity) AS units_sold,
         COALESCE(SUM(oi.line_total_ttc), 0) AS revenue_ttc
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN shops s ON s.id = oi.shop_id
    JOIN orders o ON o.id = oi.order_id
   WHERE o.status IN ('payée','en_préparation','expédiée','livrée')
     AND o.placed_at >= p_start
     AND o.placed_at < DATE_ADD(p_end, INTERVAL 1 DAY)
   GROUP BY p.id, p.name, s.name
   ORDER BY units_sold DESC, revenue_ttc DESC
   LIMIT p_limit;
END//

DELIMITER ;

-- Fin des procédures

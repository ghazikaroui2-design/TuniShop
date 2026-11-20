-- =====================================================================
-- TuniShop — Triggers d'intégrité métier
-- À importer APRÈS schema.sql ET procedures.sql
-- RG-01, RG-04, RG-05, RG-06, RG-08, RG-11
-- =====================================================================

USE tunishop;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Rendre le script réexécutable
DROP TRIGGER IF EXISTS trg_shops_bi;
DROP TRIGGER IF EXISTS trg_shops_au;
DROP TRIGGER IF EXISTS trg_products_bi;
DROP TRIGGER IF EXISTS trg_products_bu;
DROP TRIGGER IF EXISTS trg_cart_items_bi;
DROP TRIGGER IF EXISTS trg_cart_items_bu;
DROP TRIGGER IF EXISTS trg_order_items_ai;
DROP TRIGGER IF EXISTS trg_orders_au;
DROP TRIGGER IF EXISTS trg_reviews_bi;
DROP TRIGGER IF EXISTS trg_reviews_bu;
DROP TRIGGER IF EXISTS trg_users_bu;

DELIMITER //

-- ---------------------------------------------------------------------
-- RG-06 : une boutique est TOUJOURS créée en attente de validation
-- ---------------------------------------------------------------------
CREATE TRIGGER trg_shops_bi BEFORE INSERT ON shops FOR EACH ROW
BEGIN
  SET NEW.status = 'en_attente';
  SET NEW.validated_at = NULL;
  SET NEW.validated_by = NULL;
END//

-- RG-11 : journalisation de la décision admin
CREATE TRIGGER trg_shops_au AFTER UPDATE ON shops FOR EACH ROW
BEGIN
  IF OLD.status <> NEW.status THEN
    INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, details)
    VALUES (fn_audit_user(), 'SHOP_STATUS_CHANGE', 'shops', CAST(NEW.id AS CHAR),
            fn_audit_ip(), CONCAT(OLD.status, ' -> ', NEW.status));
  END IF;
END//

-- RG-06 : publication impossible si la boutique n'est pas validée
CREATE TRIGGER trg_products_bi BEFORE INSERT ON products FOR EACH ROW
BEGIN
  DECLARE v_shop_status VARCHAR(20);
  IF NEW.status = 'publié' THEN
    SELECT status INTO v_shop_status FROM shops WHERE id = NEW.shop_id;
    IF v_shop_status IS NULL OR v_shop_status <> 'validée' THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'RG-06 : publication impossible, boutique non validee';
    END IF;
    SET NEW.published_at = CURRENT_TIMESTAMP;
  END IF;
END//

CREATE TRIGGER trg_products_bu BEFORE UPDATE ON products FOR EACH ROW
BEGIN
  DECLARE v_shop_status VARCHAR(20);
  IF NEW.status = 'publié' THEN
    SELECT status INTO v_shop_status FROM shops WHERE id = NEW.shop_id;
    IF v_shop_status IS NULL OR v_shop_status <> 'validée' THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'RG-06 : publication impossible, boutique non validee';
    END IF;
    IF OLD.status <> 'publié' OR OLD.shop_id <> NEW.shop_id THEN
      SET NEW.published_at = CURRENT_TIMESTAMP;
    END IF;
  END IF;
END//

-- RG-01 : garde-fou sur le panier (quantité <= stock, produit publié)
CREATE TRIGGER trg_cart_items_bi BEFORE INSERT ON cart_items FOR EACH ROW
BEGIN
  DECLARE v_stock INT UNSIGNED;
  DECLARE v_status VARCHAR(20);
  DECLARE v_shop_status VARCHAR(20);
  DECLARE v_price DECIMAL(10,2);
  DECLARE v_shop_id INT UNSIGNED;
  DECLARE v_name VARCHAR(150);
  DECLARE v_slug VARCHAR(170);

  SELECT p.stock_qty, p.status, s.status, p.price_ttc, p.shop_id, p.name, p.slug
    INTO v_stock, v_status, v_shop_status, v_price, v_shop_id, v_name, v_slug
  FROM products p JOIN shops s ON s.id = p.shop_id
  WHERE p.id = NEW.product_id;

  IF v_stock IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produit introuvable';
  END IF;
  IF v_status <> 'publié' OR v_shop_status <> 'validée' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produit non disponible a la vente';
  END IF;
  IF NEW.quantity > v_stock THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG-01 : stock insuffisant';
  END IF;
  SET NEW.unit_price_ttc = v_price;
  SET NEW.shop_id = v_shop_id;
  SET NEW.product_name = v_name;
  SET NEW.product_slug = v_slug;
END//

CREATE TRIGGER trg_cart_items_bu BEFORE UPDATE ON cart_items FOR EACH ROW
BEGIN
  DECLARE v_stock INT UNSIGNED;
  IF NEW.quantity <> OLD.quantity THEN
    SELECT stock_qty INTO v_stock FROM products WHERE id = NEW.product_id;
    IF v_stock IS NULL OR NEW.quantity > v_stock THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG-01 : stock insuffisant';
    END IF;
  END IF;
END//

-- RG-01 : décrément automatique du stock à l'ajout d'une ligne de commande
CREATE TRIGGER trg_order_items_ai AFTER INSERT ON order_items FOR EACH ROW
BEGIN
  UPDATE products
     SET stock_qty = stock_qty - NEW.quantity
   WHERE id = NEW.product_id
     AND stock_qty >= NEW.quantity;

  IF ROW_COUNT() = 0 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'RG-01 : stock insuffisant, insertion commande annulee';
  END IF;
END//

-- RG-04 / RG-08 / RG-11 : historique, facture, restock et audit
CREATE TRIGGER trg_orders_au AFTER UPDATE ON orders FOR EACH ROW
BEGIN
  DECLARE v_invoice VARCHAR(30);
  DECLARE v_restore BOOLEAN DEFAULT FALSE;
  DECLARE v_done BOOLEAN DEFAULT FALSE;
  DECLARE v_pid INT UNSIGNED;
  DECLARE v_qty INT UNSIGNED;

  DECLARE cur_restore CURSOR FOR
    SELECT product_id, quantity FROM order_items WHERE order_id = NEW.id;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;

  IF NEW.status <> OLD.status THEN

    -- Historique des statuts
    INSERT INTO order_status_history (order_id, from_status, to_status, changed_by, note)
    VALUES (NEW.id, OLD.status, NEW.status, fn_audit_user(), @status_note);

    -- RG-08 : facture générée au premier passage en « payée »
    IF NEW.status = 'payée' AND OLD.status <> 'payée' THEN
      SET v_invoice = CONCAT('F-', YEAR(NEW.placed_at), '-',
                             fn_next_sequence('INVOICE', YEAR(NEW.placed_at)));
      INSERT INTO invoices (
        invoice_number, order_id, subtotal_ht, vat_amount,
        shipping_fee, discount_amount, total_ttc
      ) VALUES (
        v_invoice, NEW.id, NEW.subtotal_ht, NEW.vat_amount,
        NEW.shipping_fee, NEW.discount_amount, NEW.total_ttc
      );
    END IF;

    -- Annulation / remboursement : restitution du stock une seule fois
    IF NEW.status IN ('annulée','remboursée')
       AND OLD.status NOT IN ('annulée','remboursée') THEN
      SET v_restore = TRUE;
    END IF;
    IF v_restore THEN
      OPEN cur_restore;
      restore_loop: LOOP
        FETCH cur_restore INTO v_pid, v_qty;
        IF v_done THEN LEAVE restore_loop; END IF;
        UPDATE products SET stock_qty = stock_qty + v_qty WHERE id = v_pid;
      END LOOP;
      CLOSE cur_restore;
    END IF;

    -- RG-11 : journalisation
    INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, details)
    VALUES (fn_audit_user(), 'ORDER_STATUS_CHANGE', 'orders', CAST(NEW.id AS CHAR),
            fn_audit_ip(), CONCAT(OLD.status, ' -> ', NEW.status));
  END IF;
END//

-- RG-05 : avis réservé à un client ayant reçu le produit dans cette commande
CREATE TRIGGER trg_reviews_bi BEFORE INSERT ON reviews FOR EACH ROW
BEGIN
  DECLARE v_ok INT DEFAULT 0;
  SELECT COUNT(*) INTO v_ok
  FROM order_items oi
  JOIN orders o ON o.id = oi.order_id
  WHERE oi.product_id = NEW.product_id
    AND o.id = NEW.order_id
    AND o.user_id = NEW.user_id
    AND o.status = 'livrée';

  IF v_ok = 0 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'RG-05 : avis reserve aux clients ayant recu le produit';
  END IF;
  SET NEW.status = 'en_attente';
END//

-- RG-11 : journalisation de la modération
CREATE TRIGGER trg_reviews_bu AFTER UPDATE ON reviews FOR EACH ROW
BEGIN
  IF OLD.status <> NEW.status THEN
    INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, details)
    VALUES (fn_audit_user(), 'REVIEW_MODERATION', 'reviews', CAST(NEW.id AS CHAR),
            fn_audit_ip(), CONCAT(OLD.status, ' -> ', NEW.status));
  END IF;
END//

-- RG-11 : journalisation des changements de rôle / suspension
CREATE TRIGGER trg_users_bu AFTER UPDATE ON users FOR EACH ROW
BEGIN
  IF OLD.role_id <> NEW.role_id THEN
    INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, details)
    VALUES (fn_audit_user(), 'ROLE_CHANGE', 'users', CAST(NEW.id AS CHAR),
            fn_audit_ip(), CONCAT(CAST(OLD.role_id AS CHAR), ' -> ', CAST(NEW.role_id AS CHAR)));
  END IF;
  IF OLD.is_active <> NEW.is_active THEN
    INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, details)
    VALUES (fn_audit_user(), 'USER_ACTIVATION', 'users', CAST(NEW.id AS CHAR),
            fn_audit_ip(), IF(NEW.is_active, 'active', 'suspendu'));
  END IF;
END//

DELIMITER ;

-- Fin des triggers

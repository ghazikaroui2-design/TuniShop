-- =====================================================================
-- TuniShop — Scénarios de vérification SQL (US-023, US-049)
-- À importer APRÈS seed.sql. Résultat : une table de tests PASS/FAIL.
-- =====================================================================

USE tunishop;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SELECT id INTO @oid1  FROM orders WHERE order_number = 'CMD-2026-0001';
SELECT id INTO @oid7  FROM orders WHERE order_number = 'CMD-2026-0007';
SELECT id INTO @oid10 FROM orders WHERE order_number = 'CMD-2026-0010';

DROP TEMPORARY TABLE IF EXISTS test_results;
CREATE TEMPORARY TABLE test_results (
  test_name VARCHAR(80), attendu VARCHAR(120), obtenu VARCHAR(120), resultat VARCHAR(6)
);

-- Procédure utilitaire : vérifie qu'une instruction lève bien un SIGNAL SQLSTATE '45000'
DELIMITER //
DROP PROCEDURE IF EXISTS sp_expect_signal//
CREATE PROCEDURE sp_expect_signal(IN p_sql TEXT, IN p_test VARCHAR(80))
BEGIN
  DECLARE v_msg VARCHAR(255) DEFAULT 'AUCUNE_ERREUR';
  DECLARE CONTINUE HANDLER FOR SQLSTATE '45000' SET v_msg = 'BLOQUE';
  SET @sql_a_tester = p_sql;
  PREPARE stmt_test FROM @sql_a_tester;
  EXECUTE stmt_test;
  DEALLOCATE PREPARE stmt_test;
  INSERT INTO test_results VALUES (p_test, 'SIGNAL SQLSTATE 45000', v_msg,
                                   IF(v_msg = 'BLOQUE', 'PASS', 'FAIL'));
END//
DELIMITER ;

-- ---------------------------------------------------------------------
-- Structure et jeu d'essai
-- ---------------------------------------------------------------------
INSERT INTO test_results VALUES
('T01 — 20 tables présentes (hors vues)', '20',
 (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='tunishop' AND TABLE_TYPE='BASE TABLE'),
 IF((SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='tunishop' AND TABLE_TYPE='BASE TABLE')=20,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T02 — 24 produits chargés', '24', (SELECT COUNT(*) FROM products),
 IF((SELECT COUNT(*) FROM products)=24,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T03 — 21 produits publiés', '21', (SELECT COUNT(*) FROM products WHERE status='publié'),
 IF((SELECT COUNT(*) FROM products WHERE status='publié')=21,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T04 — 3 vendeurs en attente (RG-06)', '3',
 (SELECT COUNT(*) FROM shops WHERE status='en_attente'),
 IF((SELECT COUNT(*) FROM shops WHERE status='en_attente')=3,'PASS','FAIL'));

-- ---------------------------------------------------------------------
-- RG-02 : TVA 19 % incluse
-- ---------------------------------------------------------------------
INSERT INTO test_results VALUES
('T05 — HT d’un TTC 86,00', '72.27', fn_vat_from_ttc(86.00),
 IF(fn_vat_from_ttc(86.00)=72.27,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T06 — TVA d’un TTC 86,00', '13.73', fn_vat_amount_from_ttc(86.00),
 IF(fn_vat_amount_from_ttc(86.00)=13.73,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T07 — CMD-2026-0001 : 72.27 HT / 13.73 TVA / 94.00 TTC', '94.00',
 (SELECT total_ttc FROM orders WHERE order_number='CMD-2026-0001'),
 IF((SELECT total_ttc FROM orders WHERE order_number='CMD-2026-0001')=94.00,'PASS','FAIL'));

-- ---------------------------------------------------------------------
-- RG-03 : frais de livraison
-- ---------------------------------------------------------------------
INSERT INTO test_results VALUES
('T08 — Panier 86 DT → livraison 8 DT', '8.00',
 (SELECT shipping_fee FROM v_cart_totals WHERE cart_id=1),
 IF((SELECT shipping_fee FROM v_cart_totals WHERE cart_id=1)=8.00,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T09 — Panier 120 DT → livraison offerte', '0.00',
 (SELECT shipping_fee FROM v_cart_totals WHERE cart_id=2),
 IF((SELECT shipping_fee FROM v_cart_totals WHERE cart_id=2)=0.00,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T10 — CMD-2026-0006 retrait atelier → 0 DT', '0.00',
 (SELECT shipping_fee FROM orders WHERE order_number='CMD-2026-0006'),
 IF((SELECT shipping_fee FROM orders WHERE order_number='CMD-2026-0006')=0.00,'PASS','FAIL'));

-- ---------------------------------------------------------------------
-- RG-01 : trigger de stock
-- ---------------------------------------------------------------------
INSERT INTO test_results VALUES
('T11 — Stock vase après 2 commandes', '10',
 (SELECT stock_qty FROM products WHERE slug='vase-bleu-et-blanc'),
 IF((SELECT stock_qty FROM products WHERE slug='vase-bleu-et-blanc')=10,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T12 — Stock restauré après annulation CMD-0010', '3',
 (SELECT stock_qty FROM products WHERE slug='vase-miniature'),
 IF((SELECT stock_qty FROM products WHERE slug='vase-miniature')=3,'PASS','FAIL'));

-- ---------------------------------------------------------------------
-- RG-04 / RG-08 / RG-11
-- ---------------------------------------------------------------------
INSERT INTO test_results VALUES
('T13 — CMD-2026-0001 livrée', 'livrée',
 (SELECT status FROM orders WHERE order_number='CMD-2026-0001'),
 IF((SELECT status FROM orders WHERE order_number='CMD-2026-0001')='livrée','PASS','FAIL'));

INSERT INTO test_results VALUES
('T14 — Historique de statut renseigné', '>= 4',
 (SELECT COUNT(*) FROM order_status_history WHERE order_id=@oid1),
 IF((SELECT COUNT(*) FROM order_status_history WHERE order_id=@oid1)>=4,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T15 — Factures générées après paiement', '9',
 (SELECT COUNT(*) FROM invoices),
 IF((SELECT COUNT(*) FROM invoices)=9,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T16 — Format F-YYYY-NNNN (RG-08)', 'F-2026-0001',
 (SELECT MIN(invoice_number) FROM invoices),
 IF((SELECT MIN(invoice_number) FROM invoices)='F-2026-0001','PASS','FAIL'));

INSERT INTO test_results VALUES
('T17 — Journal d’audit non vide (RG-11)', '> 0',
 (SELECT COUNT(*) FROM audit_logs),
 IF((SELECT COUNT(*) FROM audit_logs)>0,'PASS','FAIL'));

-- ---------------------------------------------------------------------
-- RG-05 / RG-06 / RG-07 / RG-10
-- ---------------------------------------------------------------------
INSERT INTO test_results VALUES
('T18 — Avis modérés (RG-05)', '6',
 (SELECT COUNT(*) FROM reviews),
 IF((SELECT COUNT(*) FROM reviews)=6,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T19 — Aucun produit publié d’une boutique en attente (RG-06)', '0',
 (SELECT COUNT(*) FROM v_catalogue WHERE shop_id IN (6,7,8)),
 IF((SELECT COUNT(*) FROM v_catalogue WHERE shop_id IN (6,7,8))=0,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T20 — Aucune colonne bancaire dans payments (RG-07)', '0',
 (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema='tunishop' AND table_name='payments'
     AND column_name REGEXP 'card|cvc|expir|iban|pan'),
 IF((SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema='tunishop' AND table_name='payments'
     AND column_name REGEXP 'card|cvc|expir|iban|pan')=0,'PASS','FAIL'));

INSERT INTO test_results VALUES
('T21 — Paniers actifs non expirés (RG-10)', '0',
 (SELECT COUNT(*) FROM carts WHERE status='actif' AND expires_at < NOW()),
 IF((SELECT COUNT(*) FROM carts WHERE status='actif' AND expires_at < NOW())=0,'PASS','FAIL'));

-- ---------------------------------------------------------------------
-- Tests d'erreur : chaque instruction DOIT être bloquée par un trigger
-- ---------------------------------------------------------------------
CALL sp_expect_signal(
 'INSERT INTO products (shop_id, category_id, name, slug, price_ttc, status) VALUES (6, 1, ''Test RG-06'', ''test-rg06'', 10.00, ''publié'')',
 'T22 — RG-06 : publication boutique non validée bloquée');

CALL sp_expect_signal(
 'INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (1, 2, 999)',
 'T23 — RG-01 : quantité > stock bloquée');

CALL sp_expect_signal(
 CONCAT('INSERT INTO reviews (user_id, product_id, order_id, rating, comment) VALUES (2, 1, ', @oid7, ', 5, ''Test RG-05'')'),
 'T24 — RG-05 : avis sans commande livrée bloqué');

CALL sp_expect_signal(
 CONCAT('CALL sp_update_order_status(', @oid1, ', ''en_attente'', ''test transition'')'),
 'T25 — RG-04 : transition interdite bloquée');

-- ---------------------------------------------------------------------
-- Résultat
-- ---------------------------------------------------------------------
SELECT * FROM test_results ORDER BY test_name;
SELECT COUNT(*) AS tests_passes FROM test_results WHERE resultat='PASS';
SELECT COUNT(*) AS tests_echoues FROM test_results WHERE resultat='FAIL';

DROP PROCEDURE IF EXISTS sp_expect_signal;

-- Fin des tests

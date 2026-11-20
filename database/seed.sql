-- =====================================================================
-- TuniShop — Jeu d'essai (DML) — US-018 / NFR-07
-- À importer APRÈS schema.sql, procedures.sql, triggers.sql
-- Mot de passe de tous les comptes : Test1234!
-- 19 comptes (1 admin, 10 clients, 8 vendeurs), 24 produits, 10 commandes
-- =====================================================================

USE tunishop;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 1. Rôles et séquences
-- ---------------------------------------------------------------------
INSERT INTO roles (id, code, label, description) VALUES
 (1, 'visiteur', 'Visiteur',         'Navigation et panier sans compte'),
 (2, 'client',   'Client',           'Commande, paiement, avis, suivi'),
 (3, 'vendeur',  'Vendeur / Artisan','Boutique, produits, stocks, commandes'),
 (4, 'admin',    'Administrateur',   'Validation, modération, statistiques');

INSERT INTO sequences (seq_name, seq_year, next_value) VALUES
 ('ORDER', 2026, 1), ('INVOICE', 2026, 1), ('PAYMENT', 2026, 1);

-- ---------------------------------------------------------------------
-- 2. Utilisateurs (Argon2id ; le compte admin utilise bcrypt pour démontrer le repli RG-09)
-- ---------------------------------------------------------------------
SET @hash_argon = '$argon2id$v=19$m=65536,t=4,p=1$ZUthS2NQdXB4R2NPOVVKMg$ykrhoPgQFMWBK6Or4mvVrCHmVpr4cic98gkgzEWyWqM';
SET @hash_bcrypt = '$2y$10$brttOiJTRlhJuj9KNyVx2etM619Sj28/chowGWK./Uy2K1MuFctWO';

INSERT INTO users (id, role_id, email, password_hash, first_name, last_name, phone, is_active, email_verified_at) VALUES
 (1,  4, 'ghazi.karoui@tunishop.tn', @hash_bcrypt, 'Ghazi',   'KAROUI',    '+216 98 123 456', TRUE, NOW()),
 (2,  2, 'aymen.benali@tunishop.tn', @hash_argon,  'Aymen',   'BEN ALI',   '+216 71 234 567', TRUE, NOW()),
 (3,  2, 'syrine.kacem@tunishop.tn', @hash_argon,  'Syrine',  'KACEM',     '+216 72 345 678', TRUE, NOW()),
 (4,  2, 'mohamed.trabelsi@tunishop.tn', @hash_argon, 'Mohamed', 'TRABELSI', '+216 73 456 789', TRUE, NOW()),
 (5,  2, 'leila.mansour@tunishop.tn', @hash_argon, 'Leila',   'MANSOUR',   '+216 74 567 890', TRUE, NOW()),
 (6,  2, 'karim.bouzid@tunishop.tn', @hash_argon,  'Karim',   'BOUZID',    '+216 75 678 901', TRUE, NOW()),
 (7,  2, 'ines.hamdi@tunishop.tn',   @hash_argon,  'Ines',    'HAMDI',     '+216 76 789 012', TRUE, NOW()),
 (8,  2, 'nabil.sayari@tunishop.tn', @hash_argon,  'Nabil',   'SAYARI',    '+216 77 890 123', TRUE, NOW()),
 (9,  2, 'eya.benahmed@tunishop.tn', @hash_argon,  'Eya',     'BEN AHMED', '+216 78 901 234', TRUE, NOW()),
 (10, 2, 'sami.ghrib@tunishop.tn',   @hash_argon,  'Sami',    'GHRIB',     '+216 79 012 345', TRUE, NOW()),
 (11, 2, 'donia.ferjani@tunishop.tn',@hash_argon,  'Donia',   'FERJANI',   '+216 50 123 456', TRUE, NOW()),
 (12, 3, 'fatma.bensalah@tunishop.tn', @hash_argon, 'Fatma',  'BEN SALAH', '+216 71 111 222', TRUE, NOW()),
 (13, 3, 'mohamed.tabarka@tunishop.tn',@hash_argon, 'Mohamed','TABARKA',   '+216 72 222 333', TRUE, NOW()),
 (14, 3, 'syrine.ayari@tunishop.tn', @hash_argon,  'Syrine',  'AYARI',     '+216 73 333 444', TRUE, NOW()),
 (15, 3, 'leila.mokhtar@tunishop.tn', @hash_argon,  'Leila',   'MOKHTAR',   '+216 74 444 555', TRUE, NOW()),
 (16, 3, 'nouri.guellouz@tunishop.tn', @hash_argon, 'Nouri',   'GUELLOUZ',  '+216 75 555 666', TRUE, NOW()),
 (17, 3, 'rania.belhadj@tunishop.tn', @hash_argon,  'Rania',   'BELHADJ',   '+216 76 666 777', TRUE, NOW()),
 (18, 3, 'hedi.charfi@tunishop.tn',   @hash_argon,  'Hedi',    'CHARFI',    '+216 77 777 888', TRUE, NOW()),
 (19, 3, 'amina.khouja@tunishop.tn',  @hash_argon,  'Amina',   'KHOUJA',    '+216 78 888 999', TRUE, NOW());

-- ---------------------------------------------------------------------
-- 3. Adresses de livraison
-- ---------------------------------------------------------------------
INSERT INTO addresses (id, user_id, label, line1, line2, postal_code, city, governorate, is_default) VALUES
 (1,  2, 'Domicile', '12 rue de la République', NULL, '1000', 'Tunis',      'Tunis', 1),
 (2,  3, 'Domicile', '8 avenue Habib Bourguiba', NULL, '1001', 'Tunis',     'Tunis', 1),
 (3,  4, 'Domicile', '25 rue des Artisans', NULL, '2000', 'Ariana',         'Ariana', 1),
 (4,  5, 'Domicile', '3 impasse Sidi Bou', NULL, '2026', 'Sidi Bou Saïd',   'Tunis', 1),
 (5,  6, 'Domicile', '17 rue du Golfe', NULL, '1050', 'La Marsa',           'Tunis', 1),
 (6,  7, 'Domicile', '4 rue Farhat Hached', NULL, '1002', 'Tunis',          'Tunis', 1),
 (7,  8, 'Domicile', '9 avenue de la Liberté', NULL, '1003', 'Tunis',       'Tunis', 1),
 (8,  9, 'Domicile', '31 rue des Palmiers', NULL, '2035', 'Carthage',       'Tunis', 1),
 (9,  10,'Domicile', '6 rue de l’Artisanat', NULL, '2026', 'Sidi Bou Saïd', 'Tunis', 1),
 (10, 11,'Domicile', '14 rue El Menzah', NULL, '1004', 'Tunis',             'Tunis', 1);

-- ---------------------------------------------------------------------
-- 4. Catégories
-- ---------------------------------------------------------------------
INSERT INTO categories (id, parent_id, name, slug, description, icon, sort_order) VALUES
 (1, NULL, 'Céramique',     'ceramique',     'Céramique tournée et peinte à la main', '◍', 1),
 (2, NULL, 'Tapis & textile','tapis-textile','Tissages, tapis et broderies',          '▦', 2),
 (3, NULL, 'Parfumerie',    'parfumerie',    'Eaux florales et parfums artisanaux',   '◇', 3),
 (4, NULL, 'Bijoux',        'bijoux',        'Bijoux en argent et perles',            '✧', 4),
 (5, NULL, 'Décoration',    'decoration',    'Objets et carreaux décoratifs',         '◈', 5),
 (6, NULL, 'Calligraphie',  'calligraphie',  'Calligraphie et reliure',               '✎', 6);

-- ---------------------------------------------------------------------
-- 5. Boutiques (créées en attente par le trigger trg_shops_bi — RG-06)
-- ---------------------------------------------------------------------
INSERT INTO shops (id, owner_id, name, slug, description, address, neighborhood, phone, email, submitted_at) VALUES
 (1, 12, 'Atelier Bleu de Sidi Bou', 'atelier-bleu-sidi-bou',
  'Céramique tournée et peinte à la main depuis 2004 dans l’atelier familial.',
  '14 rue des Artisans', 'Sidi Bou Saïd', '+216 71 111 222', 'contact@atelierbleu.tn', '2026-11-02 09:10:00'),
 (2, 13, 'Dar Sidi Bou', 'dar-sidi-bou',
  'Tapis miniatures et broderies traditionnelles du village.',
  '8 place du Village', 'Sidi Bou Saïd', '+216 72 222 333', 'contact@darsidiabou.tn', '2026-11-03 10:20:00'),
 (3, 14, 'Maison El Bledi', 'maison-el-bledi',
  'Parfums, eaux florales et bijoux inspirés du patrimoine tunisien.',
  '22 rue de la Mosquée', 'Sidi Bou Saïd', '+216 73 333 444', 'contact@elbledi.tn', '2026-11-04 11:30:00'),
 (4, 15, 'Atelier Calligraphie', 'atelier-calligraphie',
  'Calligraphie arabe, cadres et carnets reliés à la main.',
  '5 impasse de la Fontaine', 'Sidi Bou Saïd', '+216 74 444 555', 'contact@calligraphie.tn', '2026-11-05 08:45:00'),
 (5, 16, 'Les Parfums du Village', 'les-parfums-du-village',
  'Bougies, coffrets et eaux parfumées fabriqués à Sidi Bou Saïd.',
  '17 rue bleue', 'Sidi Bou Saïd', '+216 75 555 666', 'contact@parfumsvillage.tn', '2026-11-06 14:00:00'),
 (6, 17, 'Atelier du Carreau', 'atelier-du-carreau',
  'Carreaux peints et céramique décorative.',
  '9 rue des Potiers', 'Sidi Bou Saïd', '+216 76 666 777', 'contact@carreau.tn', '2026-11-18 09:00:00'),
 (7, 18, 'Tapisserie Sidi Bou', 'tapisserie-sidi-bou',
  'Tapis de prière et tissages traditionnels.',
  '3 rue du Souk', 'Sidi Bou Saïd', '+216 77 777 888', 'contact@tapisserie.tn', '2026-11-19 10:00:00'),
 (8, 19, 'Bijoux el Bahr', 'bijoux-el-bahr',
  'Colliers et bijoux en argent et perles.',
  '11 quai des Pêcheurs', 'Sidi Bou Saïd', '+216 78 888 999', 'contact@bijouxel-bahr.tn', '2026-11-20 11:00:00');

-- ---------------------------------------------------------------------
-- 6. Produits (statut en_attente → publiés par sp_validate_shop)
-- ---------------------------------------------------------------------
INSERT INTO products (id, shop_id, category_id, name, slug, short_description, description, price_ttc, stock_qty, low_stock_threshold, status, is_featured) VALUES
 (1,  1, 1, 'Vase bleu et blanc', 'vase-bleu-et-blanc', 'Vase tourné et peint main',
  'Vase tourné et peint à la main. Bleu de cobalt et blanc chaux, émaillé et cuit à 980 °C.', 48.00, 12, 5, 'en_attente', TRUE),
 (2,  1, 1, 'Service à thé peint main', 'service-the-peint-main', 'Service à thé en céramique',
  'Théière et six tasses décorées au pinceau fin.', 85.00, 4, 5, 'en_attente', FALSE),
 (3,  1, 1, 'Vase miniature', 'vase-miniature', 'Miniature en céramique',
  'Petit vase décoratif, pièce unique.', 24.00, 3, 5, 'en_attente', FALSE),
 (4,  1, 5, 'Carreau décoratif peint', 'carreau-decoratif-peint', 'Carreau mural 20×20 cm',
  'Carreau en terre cuite peint et vernissé, motif bleu de Sidi Bou.', 38.00, 15, 5, 'en_attente', TRUE),
 (5,  1, 1, 'Pichet en céramique émaillée', 'pichet-ceramique-emaillee', 'Pichet artisanal 1 L',
  'Pichet émaillé, bleu et blanc, usage quotidien.', 56.00, 9, 5, 'en_attente', FALSE),
 (6,  2, 2, 'Tapis miniature fait main', 'tapis-miniature-fait-main', 'Tapis miniature 60×40 cm',
  'Tapis noué à la main, laine naturelle et motifs traditionnels.', 120.00, 7, 3, 'en_attente', TRUE),
 (7,  2, 2, 'Coussin brodé bleu', 'coussin-brode-bleu', 'Coussin 40×40 cm',
  'Coussin en lin brodé au point de croix.', 45.00, 2, 5, 'en_attente', FALSE),
 (8,  2, 2, 'Tissage traditionnel', 'tissage-traditionnel', 'Tissage mural 80×50 cm',
  'Tissage sur métier, motifs géométriques bleus.', 95.00, 6, 3, 'en_attente', FALSE),
 (9,  3, 3, 'Eau de fleur d’oranger', 'eau-de-fleur-oranger', 'Eau florale 100 ml',
  'Eau de fleur d’oranger distillée, idéale en cuisine et en soin.', 32.00, 20, 8, 'en_attente', TRUE),
 (10, 3, 3, 'Parfum iris et ambre', 'parfum-iris-ambre', 'Eau de parfum 50 ml',
  'Composition artisanale à base d’iris, ambre et musc blanc.', 58.00, 11, 5, 'en_attente', FALSE),
 (11, 3, 4, 'Bracelet argent gravé', 'bracelet-argent-grave', 'Bracelet argent 925',
  'Bracelet en argent gravé de motifs berbères.', 65.00, 8, 4, 'en_attente', TRUE),
 (12, 3, 4, 'Boucles d’oreilles bleues', 'boucles-oreilles-bleues', 'Boucles en argent et émail',
  'Boucles d’oreilles émaillées bleu cobalt.', 42.00, 1, 5, 'en_attente', FALSE),
 (13, 4, 6, 'Calligraphie encadrée « Sidi Bou »', 'calligraphie-sidi-bou', 'Cadre 30×40 cm',
  'Calligraphie arabe originale, encadrée sous verre.', 75.00, 5, 3, 'en_attente', FALSE),
 (14, 4, 6, 'Tableau calligraphie coranique', 'tableau-calligraphie', 'Cadre 50×70 cm',
  'Calligraphie coranique à l’encre sur papier artisanal.', 140.00, 3, 3, 'en_attente', FALSE),
 (15, 4, 6, 'Carnet relié à la main', 'carnet-relie-main', 'Carnet A5 120 pages',
  'Carnet relié à la main, couverture en tissu bleu.', 28.00, 18, 5, 'en_attente', FALSE),
 (16, 5, 3, 'Eau de rose de Sidi Bou', 'eau-de-rose-sidi-bou', 'Eau florale 100 ml',
  'Eau de rose distillée dans le village.', 26.00, 25, 8, 'en_attente', FALSE),
 (17, 5, 5, 'Bougie parfumée artisanale', 'bougie-parfumee-artisanale', 'Bougie 180 g',
  'Bougie en cire végétale, parfum fleur d’oranger.', 34.00, 14, 5, 'en_attente', FALSE),
 (18, 5, 5, 'Coffret cadeau artisan', 'coffret-cadeau-artisan', 'Coffret 3 objets',
  'Coffret réunissant un carreau, une bougie et une eau florale.', 110.00, 6, 3, 'en_attente', FALSE),
 (19, 6, 5, 'Carreau bleu peint main (brouillon)', 'carreau-bleu-brouillon', 'Carreau 15×15 cm',
  'Carreau en attente de validation de la boutique.', 36.00, 10, 5, 'brouillon', FALSE),
 (20, 7, 2, 'Tapis de prière brodé (brouillon)', 'tapis-priere-brouillon', 'Tapis de prière',
  'Tapis en attente de validation de la boutique.', 89.00, 4, 3, 'brouillon', FALSE),
 (21, 8, 4, 'Collier perles et argent (brouillon)', 'collier-perles-brouillon', 'Collier artisanal',
  'Collier en attente de validation de la boutique.', 72.00, 7, 3, 'brouillon', FALSE),
 (22, 1, 1, 'Assiette décorative peinte', 'assiette-decorative-peinte', 'Assiette 24 cm',
  'Assiette en céramique peinte, décor bleu et blanc.', 29.00, 0, 5, 'en_attente', FALSE),
 (23, 5, 5, 'Sculpture miniature en plâtre', 'sculpture-miniature-platre', 'Sculpture 12 cm',
  'Miniature sculptée et peinte à la main.', 64.00, 2, 5, 'en_attente', FALSE),
 (24, 2, 2, 'Pochette en tissu brodé', 'pochette-tissu-brode', 'Pochette 25×18 cm',
  'Pochette en lin brodé, fermeture artisanale.', 39.00, 17, 5, 'en_attente', FALSE);

-- Galerie photos (1 image principale par produit)
INSERT INTO product_images (product_id, image_path, alt_text, is_primary, sort_order)
SELECT id, CONCAT('uploads/products/p', LPAD(id, 2, '0'), '-1.jpg'),
       CONCAT('Photo principale — ', name), 1, 1
FROM products;

-- ---------------------------------------------------------------------
-- 7. Validation des 5 premières boutiques par l'admin (RG-06)
--    → publie automatiquement leurs produits en attente
-- ---------------------------------------------------------------------
SET @current_user_id = 1;
SET @client_ip = '127.0.0.1';
CALL sp_validate_shop(1, 1, 'validée', NULL);
CALL sp_validate_shop(2, 1, 'validée', NULL);
CALL sp_validate_shop(3, 1, 'validée', NULL);
CALL sp_validate_shop(4, 1, 'validée', NULL);
CALL sp_validate_shop(5, 1, 'validée', NULL);

-- ---------------------------------------------------------------------
-- 8. Codes promo
-- ---------------------------------------------------------------------
INSERT INTO promo_codes (code, type, value, min_amount_ttc, starts_at, ends_at, max_uses) VALUES
 ('BIENVENUE10', 'pourcentage', 10.00, 50.00, '2026-11-01 00:00:00', '2026-12-31 23:59:59', 100),
 ('ARTISAN5',    'montant',      5.00, 30.00, '2026-11-01 00:00:00', '2026-12-31 23:59:59', 50);

-- ---------------------------------------------------------------------
-- 9. Paniers de démonstration (US-035, RG-10)
-- ---------------------------------------------------------------------
INSERT INTO carts (id, user_id, guest_token, status, expires_at) VALUES
 (1, 2, NULL, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY)),
 (2, 3, NULL, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY)),
 (3, 7, NULL, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY)),
 (4, NULL, 'guest_demo_2026', 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));

INSERT INTO cart_items (cart_id, product_id, quantity) VALUES
 (1, 1, 1), (1, 4, 1),
 (2, 6, 1),
 (3, 9, 2),
 (4, 11, 1);

-- ---------------------------------------------------------------------
-- 10. Commandes : création via sp_place_order, paiement sandbox, statuts
-- ---------------------------------------------------------------------

-- CMD 1 — Aymen : 86 DT TTC + 8 DT livraison = 93 DT → livrée
INSERT INTO carts (id, user_id, status, expires_at) VALUES (5, 2, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(5, 1, 1);
CALL sp_add_to_cart(5, 4, 1);
SET @current_user_id = 2;
CALL sp_place_order(5, 2, NULL, 'domicile', 1, NULL, @oid1, @ono1);
CALL sp_simulate_payment(@oid1, @tx1);
CALL sp_update_order_status(@oid1, 'en_préparation', 'Prise en charge atelier');
CALL sp_update_order_status(@oid1, 'expédiée', 'Colis remis au transporteur');
CALL sp_update_order_status(@oid1, 'livrée', 'Livraison confirmée');

-- CMD 2 — Syrine : tapis 120 DT, livraison offerte → en préparation
INSERT INTO carts (id, user_id, status, expires_at) VALUES (6, 3, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(6, 6, 1);
SET @current_user_id = 3;
CALL sp_place_order(6, 3, NULL, 'domicile', 2, NULL, @oid2, @ono2);
CALL sp_simulate_payment(@oid2, @tx2);
CALL sp_update_order_status(@oid2, 'en_préparation', NULL);

-- CMD 3 — Mohamed : bracelet 65 + 8 = 73 DT → expédiée
INSERT INTO carts (id, user_id, status, expires_at) VALUES (7, 4, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(7, 11, 1);
SET @current_user_id = 4;
CALL sp_place_order(7, 4, NULL, 'domicile', 3, NULL, @oid3, @ono3);
CALL sp_simulate_payment(@oid3, @tx3);
CALL sp_update_order_status(@oid3, 'en_préparation', NULL);
CALL sp_update_order_status(@oid3, 'expédiée', NULL);

-- CMD 4 — Leila : vase 48 + 8 = 56 DT → livrée
INSERT INTO carts (id, user_id, status, expires_at) VALUES (8, 5, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(8, 1, 1);
SET @current_user_id = 5;
CALL sp_place_order(8, 5, NULL, 'domicile', 4, NULL, @oid4, @ono4);
CALL sp_simulate_payment(@oid4, @tx4);
CALL sp_update_order_status(@oid4, 'en_préparation', NULL);
CALL sp_update_order_status(@oid4, 'expédiée', NULL);
CALL sp_update_order_status(@oid4, 'livrée', NULL);

-- CMD 5 — Karim : 2 eaux florales 64 + 8 = 72 DT, code promo BIENVENUE10 → payée
INSERT INTO carts (id, user_id, status, expires_at) VALUES (9, 6, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(9, 9, 2);
SET @current_user_id = 6;
CALL sp_place_order(9, 6, NULL, 'domicile', 5, 'BIENVENUE10', @oid5, @ono5);
CALL sp_simulate_payment(@oid5, @tx5);

-- CMD 6 — Aymen : tapis 120 DT (retrait à l'atelier, 0 DT) → livrée
INSERT INTO carts (id, user_id, status, expires_at) VALUES (10, 2, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(10, 6, 1);
SET @current_user_id = 2;
CALL sp_place_order(10, 2, NULL, 'retrait', NULL, NULL, @oid6, @ono6);
CALL sp_simulate_payment(@oid6, @tx6);
CALL sp_update_order_status(@oid6, 'en_préparation', NULL);
CALL sp_update_order_status(@oid6, 'expédiée', NULL);
CALL sp_update_order_status(@oid6, 'livrée', NULL);

-- CMD 7 — Ines : 42 + 8 = 50 DT → EN ATTENTE de paiement
INSERT INTO carts (id, user_id, status, expires_at) VALUES (11, 7, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(11, 12, 1);
SET @current_user_id = 7;
CALL sp_place_order(11, 7, NULL, 'domicile', 6, NULL, @oid7, @ono7);

-- CMD 8 — Nabil : coussin 45 + 8 = 53 DT → livrée
INSERT INTO carts (id, user_id, status, expires_at) VALUES (12, 8, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(12, 7, 1);
SET @current_user_id = 8;
CALL sp_place_order(12, 8, NULL, 'domicile', 7, NULL, @oid8, @ono8);
CALL sp_simulate_payment(@oid8, @tx8);
CALL sp_update_order_status(@oid8, 'en_préparation', NULL);
CALL sp_update_order_status(@oid8, 'expédiée', NULL);
CALL sp_update_order_status(@oid8, 'livrée', NULL);

-- CMD 9 — Eya : coffret 110 DT, retrait gratuit → livrée
INSERT INTO carts (id, user_id, status, expires_at) VALUES (13, 9, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(13, 18, 1);
SET @current_user_id = 9;
CALL sp_place_order(13, 9, NULL, 'retrait', NULL, NULL, @oid9, @ono9);
CALL sp_simulate_payment(@oid9, @tx9);
CALL sp_update_order_status(@oid9, 'en_préparation', NULL);
CALL sp_update_order_status(@oid9, 'expédiée', NULL);
CALL sp_update_order_status(@oid9, 'livrée', NULL);

-- CMD 10 — Sami : vase miniature 24 + 8 = 32 DT → annulée (restock automatique)
INSERT INTO carts (id, user_id, status, expires_at) VALUES (14, 10, 'actif', DATE_ADD(NOW(), INTERVAL 30 DAY));
CALL sp_add_to_cart(14, 3, 1);
SET @current_user_id = 10;
CALL sp_place_order(14, 10, NULL, 'domicile', 9, NULL, @oid10, @ono10);
CALL sp_simulate_payment(@oid10, @tx10);
CALL sp_update_order_status(@oid10, 'annulée', 'Article indisponible — stock restauré');

-- ---------------------------------------------------------------------
-- 11. Avis (RG-05 : uniquement sur commandes livrées)
-- ---------------------------------------------------------------------
INSERT INTO reviews (user_id, product_id, order_id, rating, comment, status, moderated_by, moderated_at) VALUES
 (2, 1, @oid1, 5, 'Très belle pièce, fidèle à la photo. Emballage soigné.', 'publié', 1, NOW()),
 (2, 4, @oid1, 4, 'Carreau magnifique, un peu plus foncé que prévu.',      'publié', 1, NOW()),
 (5, 1, @oid4, 5, 'Savoir-faire exceptionnel, je recommande l’atelier.',   'publié', 1, NOW()),
 (2, 6, @oid6, 5, 'Tapis brodé superbe, retrait très pratique.',           'en_attente', NULL, NULL),
 (8, 7, @oid8, 4, 'Coussin de bonne qualité, livraison rapide.',           'publié', 1, NOW()),
 (9, 18,@oid9, 5, 'Coffret parfait pour offrir.',                          'publié', 1, NOW());

-- ---------------------------------------------------------------------
-- 12. Journalisation, newsletter, contact
-- ---------------------------------------------------------------------
SET @current_user_id = 2;
CALL sp_log_action('LOGIN_SUCCESS', 'users', '2', 'Connexion client — Aymen');
SET @current_user_id = 1;
CALL sp_log_action('LOGIN_SUCCESS', 'users', '1', 'Connexion administrateur');
CALL sp_log_action('DASHBOARD_VIEW', 'stats', NULL, 'Consultation tableau de bord');

INSERT INTO newsletter_subscribers (email) VALUES
 ('visiteur1@example.com'), ('visiteur2@example.com'),
 ('visiteur3@example.com'), ('visiteur4@example.com');

INSERT INTO contact_messages (name, email, subject, message, is_read) VALUES
 ('Touriste', 'touriste@example.com', 'Livraison en France ?',
  'Bonjour, livrez-vous hors de Tunisie ? Merci.', FALSE),
 ('Artisan', 'artisan@example.com', 'Rejoindre TuniShop',
  'Je souhaite ouvrir une boutique sur la marketplace.', TRUE),
 ('Client', 'client@example.com', 'Question facture',
  'Puis-je obtenir une facture avec mon numéro fiscal ?', FALSE);

-- Fin du jeu d'essai

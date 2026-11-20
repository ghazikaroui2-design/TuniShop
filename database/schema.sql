-- =====================================================================
-- TuniShop — Schéma de base de données (DDL)
-- MySQL 8.0 / MariaDB 10.11 · InnoDB · UTF-8mb4
-- US-017, US-050 · NFR-08 (intégrité), NFR-09 (reproductibilité)
-- Import : SOURCE database/schema.sql
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS tunishop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE tunishop;

-- Réinitialisation (script reproductible)
DROP VIEW IF EXISTS v_seller_sales;
DROP VIEW IF EXISTS v_reviews_published;
DROP VIEW IF EXISTS v_orders_detail;
DROP VIEW IF EXISTS v_cart_totals;
DROP VIEW IF EXISTS v_low_stock;
DROP VIEW IF EXISTS v_catalogue;

DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS promo_codes;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS shops;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS sequences;
DROP TABLE IF EXISTS roles;

-- ---------------------------------------------------------------------
-- 1. Référentiels et sécurité
-- ---------------------------------------------------------------------

CREATE TABLE roles (
  id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code        VARCHAR(20)  NOT NULL COMMENT 'visiteur|client|vendeur|admin',
  label       VARCHAR(50)  NOT NULL,
  description VARCHAR(255) NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rôles RBAC (FR-27)';

CREATE TABLE sequences (
  seq_name   VARCHAR(30)       NOT NULL COMMENT 'ORDER|INVOICE|PAYMENT',
  seq_year   SMALLINT UNSIGNED NOT NULL,
  next_value INT UNSIGNED      NOT NULL DEFAULT 1,
  PRIMARY KEY (seq_name, seq_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Numérotation atomique (RG-08)';

CREATE TABLE users (
  id                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id               TINYINT UNSIGNED NOT NULL,
  email                 VARCHAR(190) NOT NULL,
  password_hash         VARCHAR(255) NOT NULL COMMENT 'Argon2id ou bcrypt — RG-09',
  first_name            VARCHAR(80)  NOT NULL,
  last_name             VARCHAR(80)  NOT NULL,
  phone                 VARCHAR(30)  NULL,
  is_active             BOOLEAN      NOT NULL DEFAULT TRUE,
  email_verified_at     DATETIME     NULL,
  last_login_at         DATETIME     NULL,
  reset_token           VARCHAR(64)  NULL,
  reset_token_expires_at DATETIME    NULL,
  created_at            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_reset_token (reset_token),
  KEY idx_users_role (role_id),
  KEY idx_users_active (is_active),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comptes utilisateurs (US-040, RG-09)';

CREATE TABLE addresses (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      INT UNSIGNED NOT NULL,
  label        VARCHAR(50)  NOT NULL DEFAULT 'Domicile',
  line1        VARCHAR(180) NOT NULL,
  line2        VARCHAR(180) NULL,
  postal_code  VARCHAR(10)  NOT NULL,
  city         VARCHAR(80)  NOT NULL,
  governorate  VARCHAR(80)  NOT NULL DEFAULT 'Tunis',
  is_default   TINYINT UNSIGNED NULL DEFAULT NULL COMMENT '1 = adresse par défaut, NULL = non',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_addresses_default (user_id, is_default),
  KEY idx_addresses_user (user_id),
  CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Adresses de livraison';

-- ---------------------------------------------------------------------
-- 2. Catalogue
-- ---------------------------------------------------------------------

CREATE TABLE categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id   INT UNSIGNED NULL,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL,
  description TEXT NULL,
  icon        VARCHAR(50)  NULL,
  sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_active   BOOLEAN NOT NULL DEFAULT TRUE,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug),
  KEY idx_categories_parent (parent_id),
  CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catégories du catalogue (US-022)';

CREATE TABLE shops (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_id         INT UNSIGNED NOT NULL,
  name             VARCHAR(120) NOT NULL,
  slug             VARCHAR(140) NOT NULL,
  description      TEXT NULL,
  address          VARCHAR(255) NOT NULL,
  neighborhood     VARCHAR(100) NOT NULL DEFAULT 'Sidi Bou Saïd',
  phone            VARCHAR(30) NOT NULL,
  email            VARCHAR(190) NOT NULL,
  logo_path        VARCHAR(255) NULL,
  status           ENUM('en_attente','validée','refusée') NOT NULL DEFAULT 'en_attente',
  rejection_reason TEXT NULL,
  submitted_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  validated_at     DATETIME NULL,
  validated_by     INT UNSIGNED NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_shops_owner (owner_id),
  UNIQUE KEY uq_shops_slug (slug),
  KEY idx_shops_status (status),
  CONSTRAINT fk_shops_owner FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_shops_validated_by FOREIGN KEY (validated_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Boutiques artisans — RG-06 (US-030, US-041)';

CREATE TABLE products (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  shop_id            INT UNSIGNED NOT NULL,
  category_id        INT UNSIGNED NOT NULL,
  name               VARCHAR(150) NOT NULL,
  slug               VARCHAR(170) NOT NULL,
  short_description  VARCHAR(255) NULL,
  description        TEXT NULL,
  price_ttc          DECIMAL(10,2) NOT NULL COMMENT 'Prix TTC, TVA 19 % incluse (RG-02)',
  stock_qty          INT UNSIGNED NOT NULL DEFAULT 0,
  low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5,
  status             ENUM('brouillon','en_attente','publié','archivé') NOT NULL DEFAULT 'brouillon',
  is_featured        BOOLEAN NOT NULL DEFAULT FALSE,
  views_count        INT UNSIGNED NOT NULL DEFAULT 0,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  published_at       DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_slug (slug),
  KEY idx_products_shop (shop_id),
  KEY idx_products_category_status (category_id, status),
  KEY idx_products_price (price_ttc),
  KEY idx_products_status_featured (status, is_featured),
  FULLTEXT KEY ft_products_search (name, short_description, description),
  CONSTRAINT fk_products_shop FOREIGN KEY (shop_id) REFERENCES shops (id) ON DELETE CASCADE,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT,
  CONSTRAINT ck_products_price CHECK (price_ttc >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Produits — RG-01, RG-02, RG-06';

CREATE TABLE product_images (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id    INT UNSIGNED NOT NULL,
  image_path    VARCHAR(255) NOT NULL,
  alt_text      VARCHAR(180) NOT NULL COMMENT 'Accessibilité NFR-04 / SEO NFR-05',
  is_primary    TINYINT UNSIGNED NULL DEFAULT NULL COMMENT '1 = image principale',
  sort_order    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_product_images_primary (product_id, is_primary),
  KEY idx_product_images_product (product_id),
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Galerie photos produits';

CREATE TABLE promo_codes (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code           VARCHAR(30) NOT NULL,
  type           ENUM('pourcentage','montant') NOT NULL,
  value          DECIMAL(10,2) NOT NULL,
  min_amount_ttc DECIMAL(10,2) NOT NULL DEFAULT 0,
  starts_at      DATETIME NULL,
  ends_at        DATETIME NULL,
  max_uses       INT UNSIGNED NULL,
  uses_count     INT UNSIGNED NOT NULL DEFAULT 0,
  is_active      BOOLEAN NOT NULL DEFAULT TRUE,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_promo_codes_code (code),
  CONSTRAINT ck_promo_value CHECK (value > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Codes promo (US-054, Could)';

-- ---------------------------------------------------------------------
-- 3. Panier (RG-10)
-- ---------------------------------------------------------------------

CREATE TABLE carts (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NULL,
  guest_token VARCHAR(64) NULL,
  status      ENUM('actif','converti','abandonné') NOT NULL DEFAULT 'actif',
  expires_at  DATETIME NOT NULL COMMENT 'création + 30 jours (RG-10)',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_carts_guest (guest_token),
  KEY idx_carts_user (user_id),
  KEY idx_carts_status_expires (status, expires_at),
  CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT ck_carts_owner CHECK (user_id IS NOT NULL OR guest_token IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Paniers persistants — RG-10';

CREATE TABLE cart_items (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cart_id        INT UNSIGNED NOT NULL,
  product_id     INT UNSIGNED NOT NULL,
  shop_id        INT UNSIGNED NOT NULL COMMENT 'Snapshot vendeur (évite de lire products au checkout)',
  product_name   VARCHAR(150) NOT NULL COMMENT 'Snapshot produit',
  product_slug   VARCHAR(170) NOT NULL COMMENT 'Snapshot URL SEO',
  quantity       INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price_ttc DECIMAL(10,2) NOT NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cart_items (cart_id, product_id),
  KEY idx_cart_items_cart (cart_id),
  CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
  CONSTRAINT fk_cart_items_shop FOREIGN KEY (shop_id) REFERENCES shops (id) ON DELETE RESTRICT,
  CONSTRAINT ck_cart_items_qty CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lignes de panier — RG-01';

-- ---------------------------------------------------------------------
-- 4. Commande, paiement, facture
-- ---------------------------------------------------------------------

CREATE TABLE orders (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_number        VARCHAR(30) NOT NULL,
  user_id             INT UNSIGNED NULL,
  guest_email         VARCHAR(190) NULL,
  status              ENUM('en_attente','payée','en_préparation','expédiée','livrée','annulée','remboursée')
                      NOT NULL DEFAULT 'en_attente',
  delivery_mode       ENUM('domicile','retrait') NOT NULL DEFAULT 'domicile',
  subtotal_ht         DECIMAL(10,2) NOT NULL DEFAULT 0,
  vat_amount          DECIMAL(10,2) NOT NULL DEFAULT 0,
  subtotal_ttc        DECIMAL(10,2) NOT NULL DEFAULT 0,
  discount_amount     DECIMAL(10,2) NOT NULL DEFAULT 0,
  shipping_fee        DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_ttc           DECIMAL(10,2) NOT NULL DEFAULT 0,
  promo_code_id       INT UNSIGNED NULL,
  address_id          INT UNSIGNED NULL,
  shipping_line1      VARCHAR(180) NULL,
  shipping_postal_code VARCHAR(10) NULL,
  shipping_city       VARCHAR(80) NULL,
  transaction_ref     VARCHAR(60) NULL COMMENT 'SBX-YYYY-NNNNNN — RG-07',
  payment_method      ENUM('sandbox') NULL,
  paid_at             DATETIME NULL,
  placed_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_number (order_number),
  UNIQUE KEY uq_orders_transaction (transaction_ref),
  KEY idx_orders_user (user_id),
  KEY idx_orders_status (status),
  KEY idx_orders_placed (placed_at),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_orders_promo FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE SET NULL,
  CONSTRAINT fk_orders_address FOREIGN KEY (address_id) REFERENCES addresses (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Commandes — RG-02/03/04/07';

CREATE TABLE order_items (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id        INT UNSIGNED NOT NULL,
  product_id      INT UNSIGNED NOT NULL,
  shop_id         INT UNSIGNED NOT NULL COMMENT 'Snapshot vendeur pour facturation multi-boutiques',
  product_name    VARCHAR(150) NOT NULL,
  product_slug    VARCHAR(170) NOT NULL,
  unit_price_ttc  DECIMAL(10,2) NOT NULL,
  quantity        INT UNSIGNED NOT NULL,
  line_total_ttc  DECIMAL(10,2) NOT NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  KEY idx_order_items_product (product_id),
  KEY idx_order_items_shop (shop_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
  CONSTRAINT fk_order_items_shop FOREIGN KEY (shop_id) REFERENCES shops (id) ON DELETE RESTRICT,
  CONSTRAINT ck_order_items_qty CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lignes de commande — RG-01 (trigger de stock)';

CREATE TABLE order_status_history (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id     INT UNSIGNED NOT NULL,
  from_status  VARCHAR(30) NULL,
  to_status    VARCHAR(30) NOT NULL,
  changed_by   INT UNSIGNED NULL,
  note         VARCHAR(255) NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_status_history_order (order_id),
  CONSTRAINT fk_status_history_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_status_history_user FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historique RG-04 / RG-11';

CREATE TABLE payments (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id        INT UNSIGNED NOT NULL,
  transaction_ref VARCHAR(60) NOT NULL COMMENT 'Identifiant factice — aucune donnée bancaire (RG-07)',
  method          ENUM('sandbox') NOT NULL DEFAULT 'sandbox',
  amount          DECIMAL(10,2) NOT NULL,
  status          ENUM('en_attente','accepté','refusé') NOT NULL DEFAULT 'en_attente',
  processed_at    DATETIME NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payments_order (order_id),
  UNIQUE KEY uq_payments_ref (transaction_ref),
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Paiements simulés — RG-07';

CREATE TABLE invoices (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  invoice_number  VARCHAR(30) NOT NULL COMMENT 'F-YYYY-NNNN — RG-08',
  order_id        INT UNSIGNED NOT NULL,
  issued_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  subtotal_ht     DECIMAL(10,2) NOT NULL,
  vat_amount      DECIMAL(10,2) NOT NULL,
  shipping_fee    DECIMAL(10,2) NOT NULL,
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_ttc       DECIMAL(10,2) NOT NULL,
  pdf_path        VARCHAR(255) NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_invoices_number (invoice_number),
  UNIQUE KEY uq_invoices_order (order_id),
  CONSTRAINT fk_invoices_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Factures — RG-08';

-- ---------------------------------------------------------------------
-- 5. Avis, audit, marketing
-- ---------------------------------------------------------------------

CREATE TABLE reviews (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      INT UNSIGNED NOT NULL,
  product_id   INT UNSIGNED NOT NULL,
  order_id     INT UNSIGNED NOT NULL,
  rating       TINYINT UNSIGNED NOT NULL,
  comment      TEXT NOT NULL,
  status       ENUM('en_attente','publié','masqué','refusé') NOT NULL DEFAULT 'en_attente',
  moderated_by INT UNSIGNED NULL,
  moderated_at DATETIME NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reviews_once (user_id, product_id, order_id),
  KEY idx_reviews_product (product_id, status),
  KEY idx_reviews_status (status),
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_moderator FOREIGN KEY (moderated_by) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT ck_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Avis modérés — RG-05';

CREATE TABLE audit_logs (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NULL,
  action      VARCHAR(80) NOT NULL,
  entity_type VARCHAR(50) NOT NULL,
  entity_id   VARCHAR(50) NULL,
  ip_address  VARCHAR(45) NULL,
  user_agent  VARCHAR(255) NULL,
  details     TEXT NULL COMMENT 'JSON',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_created (created_at),
  KEY idx_audit_user (user_id),
  KEY idx_audit_action (action),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Journal des actions sensibles — RG-11';

CREATE TABLE newsletter_subscribers (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email          VARCHAR(190) NOT NULL,
  is_active      BOOLEAN NOT NULL DEFAULT TRUE,
  subscribed_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_newsletter_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Newsletter (marketing)';

CREATE TABLE contact_messages (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(190) NOT NULL,
  subject    VARCHAR(180) NOT NULL,
  message    TEXT NOT NULL,
  is_read    BOOLEAN NOT NULL DEFAULT FALSE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Formulaire contact';

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 6. Vues de lecture
-- ---------------------------------------------------------------------

CREATE VIEW v_catalogue AS
SELECT
  p.id, p.name, p.slug, p.short_description, p.price_ttc, p.stock_qty,
  p.is_featured, p.views_count,
  c.name AS category_name, c.slug AS category_slug,
  s.id AS shop_id, s.name AS shop_name, s.slug AS shop_slug,
  s.neighborhood,
  COALESCE(r.avg_rating, 0) AS avg_rating,
  COALESCE(r.review_count, 0) AS review_count,
  pi.image_path AS primary_image, pi.alt_text AS primary_alt
FROM products p
JOIN shops s      ON s.id = p.shop_id AND s.status = 'validée'
JOIN categories c ON c.id = p.category_id AND c.is_active = TRUE
LEFT JOIN (
  SELECT product_id, ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS review_count
  FROM reviews WHERE status = 'publié' GROUP BY product_id
) r ON r.product_id = p.id
LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.status = 'publié';

CREATE VIEW v_low_stock AS
SELECT
  p.id AS product_id, p.name AS product_name, p.stock_qty,
  p.low_stock_threshold, p.status, s.id AS shop_id, s.name AS shop_name
FROM products p
JOIN shops s ON s.id = p.shop_id
WHERE p.status = 'publié'
  AND p.stock_qty <= p.low_stock_threshold
ORDER BY p.stock_qty ASC;

CREATE VIEW v_cart_totals AS
SELECT
  c.id AS cart_id,
  COALESCE(SUM(ci.quantity * ci.unit_price_ttc), 0) AS subtotal_ttc,
  ROUND(COALESCE(SUM(ci.quantity * ci.unit_price_ttc), 0) / 1.19, 2) AS subtotal_ht,
  ROUND(COALESCE(SUM(ci.quantity * ci.unit_price_ttc), 0)
        - COALESCE(SUM(ci.quantity * ci.unit_price_ttc), 0) / 1.19, 2) AS vat_amount,
  CASE WHEN COALESCE(SUM(ci.quantity * ci.unit_price_ttc), 0) >= 100 THEN 0 ELSE 8 END AS shipping_fee,
  GREATEST(100 - COALESCE(SUM(ci.quantity * ci.unit_price_ttc), 0), 0) AS remaining_for_free_shipping
FROM carts c
LEFT JOIN cart_items ci ON ci.cart_id = c.id
GROUP BY c.id;

CREATE VIEW v_orders_detail AS
SELECT
  o.id, o.order_number, o.placed_at, o.status, o.total_ttc, o.delivery_mode,
  u.first_name, u.last_name, u.email,
  (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
  i.invoice_number
FROM orders o
LEFT JOIN users u ON u.id = o.user_id
LEFT JOIN invoices i ON i.order_id = o.id;

CREATE VIEW v_reviews_published AS
SELECT
  r.id, r.rating, r.comment, r.created_at,
  p.name AS product_name, p.slug AS product_slug,
  s.name AS shop_name,
  CONCAT(u.first_name, ' ', LEFT(u.last_name, 1), '.') AS customer
FROM reviews r
JOIN products p ON p.id = r.product_id
JOIN shops s ON s.id = p.shop_id
JOIN users u ON u.id = r.user_id
WHERE r.status = 'publié';

CREATE VIEW v_seller_sales AS
SELECT
  s.id AS shop_id, s.name AS shop_name,
  p.id AS product_id, p.name AS product_name,
  COUNT(oi.id) AS units_sold,
  COALESCE(SUM(oi.line_total_ttc), 0) AS revenue_ttc
FROM shops s
JOIN products p ON p.shop_id = s.id
LEFT JOIN order_items oi ON oi.product_id = p.id
LEFT JOIN orders o ON o.id = oi.order_id AND o.status IN ('payée','en_préparation','expédiée','livrée')
GROUP BY s.id, p.id;

-- Fin du schéma

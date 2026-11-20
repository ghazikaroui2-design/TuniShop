# Dictionnaire des données — TuniShop

> Version 1.0 · 20/11/2026 · MySQL 8 / InnoDB · 20 tables · 6 vues  
> Convention : `PK` clé primaire, `FK` clé étrangère, `UK` unicité, `NN` NOT NULL, `D` défaut.

---

## 1. Vue d'ensemble des tables

| # | Table | Rôle | Volume jeu d'essai |
|---|---|---|---|
| 1 | `roles` | Rôles RBAC (visiteur, client, vendeur, admin) | 4 |
| 2 | `sequences` | Numérotation atomique CMD / factures / paiements | 3 |
| 3 | `users` | Comptes (client, vendeur, admin) + hachage | 19 |
| 4 | `addresses` | Adresses de livraison des clients | 10 |
| 5 | `categories` | Catégories du catalogue (arborescence) | 6 |
| 6 | `shops` | Boutiques artisans + statut de validation | 8 |
| 7 | `products` | Produits, prix TTC, stock, seuil d'alerte | 24 |
| 8 | `product_images` | Galerie photos (1 principale) | 24 |
| 9 | `promo_codes` | Codes promo (US-054, Could) | 2 |
| 10 | `carts` | Paniers persistants (30 jours) | 4 |
| 11 | `cart_items` | Lignes de panier | 7 |
| 12 | `orders` | Commandes + totaux + snapshot livraison | 10 |
| 13 | `order_items` | Lignes de commande (snapshot produit) | 18 |
| 14 | `order_status_history` | Traçabilité des changements de statut | ~25 |
| 15 | `payments` | Transactions **sandbox** (aucune donnée bancaire) | 8 |
| 16 | `invoices` | Factures `F-YYYY-NNNN` | 8 |
| 17 | `reviews` | Avis modérés (1 max / produit / commande) | 6 |
| 18 | `audit_logs` | Journal des actions sensibles (RG-11) | ~20 |
| 19 | `newsletter_subscribers` | Inscriptions newsletter (marketing) | 4 |
| 20 | `contact_messages` | Messages du formulaire contact | 3 |

---

## 2. Sécurité et référentiels

### 2.1 `roles`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `TINYINT UNSIGNED` | PK, AI | Identifiant rôle |
| `code` | `VARCHAR(20)` | NN, UK | `visiteur`, `client`, `vendeur`, `admin` |
| `label` | `VARCHAR(50)` | NN | Libellé affiché |
| `description` | `VARCHAR(255)` | — | Description des permissions |
| `created_at` | `TIMESTAMP` | D `CURRENT_TIMESTAMP` | Date de création |

### 2.2 `sequences`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `seq_name` | `VARCHAR(30)` | PK (composée) | `ORDER`, `INVOICE`, `PAYMENT` |
| `seq_year` | `SMALLINT UNSIGNED` | PK (composée) | Année de la séquence |
| `next_value` | `INT UNSIGNED` | D `1` | Prochain numéro (RG-08) |

### 2.3 `users`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `INT UNSIGNED` | PK, AI | Identifiant utilisateur |
| `role_id` | `TINYINT UNSIGNED` | NN, FK → `roles.id` | Rôle RBAC |
| `email` | `VARCHAR(190)` | NN, UK | Email de connexion |
| `password_hash` | `VARCHAR(255)` | NN | **Argon2id** (repli bcrypt) — RG-09, jamais en clair |
| `first_name` | `VARCHAR(80)` | NN | Prénom |
| `last_name` | `VARCHAR(80)` | NN | Nom |
| `phone` | `VARCHAR(30)` | — | Téléphone (format tunisien) |
| `is_active` | `BOOLEAN` | D `TRUE` | Suspension de compte par l'admin |
| `email_verified_at` | `DATETIME` | — | Validation d'email (FR-01) |
| `last_login_at` | `DATETIME` | — | Dernière connexion (RG-11) |
| `reset_token` | `VARCHAR(64)` | UK | Token réinitialisation (US-053) |
| `reset_token_expires_at` | `DATETIME` | — | Expiration du token |
| `created_at` / `updated_at` | `DATETIME` | D `NOW()` / `ON UPDATE NOW()` | Horodatage |

### 2.4 `addresses`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `INT UNSIGNED` | PK, AI | Identifiant adresse |
| `user_id` | `INT UNSIGNED` | NN, FK → `users.id` ON DELETE CASCADE | Propriétaire |
| `label` | `VARCHAR(50)` | D `'Domicile'` | Libellé (« Atelier », « Domicile ») |
| `line1` / `line2` | `VARCHAR(180)` | NN / — | Adresse |
| `postal_code` | `VARCHAR(10)` | NN | Code postal tunisien |
| `city` | `VARCHAR(80)` | NN | Ville |
| `governorate` | `VARCHAR(80)` | D `'Tunis'` | Gouvernorat |
| `is_default` | `TINYINT UNSIGNED` | `NULL` ou `1`, UK `(user_id, is_default)` | `NULL` = non défaut (permet plusieurs adresses) |

---

## 3. Catalogue

### 3.1 `categories`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `INT UNSIGNED` | PK, AI | Identifiant catégorie |
| `parent_id` | `INT UNSIGNED` | FK → `categories.id` ON DELETE SET NULL | Sous-catégorie |
| `name` | `VARCHAR(100)` | NN | Céramique, Tapis & textile… |
| `slug` | `VARCHAR(120)` | NN, UK | URL SEO |
| `description` / `icon` | `TEXT` / `VARCHAR(50)` | — | Présentation / pictogramme |
| `sort_order` | `SMALLINT UNSIGNED` | D `0` | Ordre d'affichage |
| `is_active` | `BOOLEAN` | D `TRUE` | Masquage catégorie |

### 3.2 `shops`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `INT UNSIGNED` | PK, AI | Identifiant boutique |
| `owner_id` | `INT UNSIGNED` | NN, UK, FK → `users.id` | 1 vendeur = 1 boutique |
| `name` / `slug` | `VARCHAR(120)` / `VARCHAR(140)` | NN / NN, UK | Nom et URL SEO |
| `description` | `TEXT` | — | Savoir-faire de l'artisan |
| `address` / `neighborhood` | `VARCHAR(255)` / `VARCHAR(100)` | NN / D `'Sidi Bou Saïd'` | Adresse atelier |
| `phone` / `email` | `VARCHAR(30)` / `VARCHAR(190)` | NN / NN | Contact public |
| `logo_path` | `VARCHAR(255)` | — | Chemin fichier upload |
| `status` | `ENUM('en_attente','validée','refusée')` | D `'en_attente'` | **RG-06** : visible seulement si `validée` |
| `rejection_reason` | `TEXT` | — | Motif du refus (US-041) |
| `submitted_at` / `validated_at` | `DATETIME` | D `NOW()` / — | Dates demande / décision |
| `validated_by` | `INT UNSIGNED` | FK → `users.id` | Admin décisionnaire |

### 3.3 `products`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `INT UNSIGNED` | PK, AI | Identifiant produit |
| `shop_id` | `INT UNSIGNED` | NN, FK → `shops.id` ON DELETE CASCADE | Boutique propriétaire |
| `category_id` | `INT UNSIGNED` | NN, FK → `categories.id` | Catégorie obligatoire (US-031) |
| `name` / `slug` | `VARCHAR(150)` / `VARCHAR(170)` | NN / NN, UK | Libellé / URL SEO |
| `short_description` / `description` | `VARCHAR(255)` / `TEXT` | — | Fiche produit |
| `price_ttc` | `DECIMAL(10,2)` | NN, `CHECK (>= 0)` | **Prix TTC**, TVA 19 % incluse (RG-02) |
| `stock_qty` | `INT UNSIGNED` | D `0` | Stock disponible (RG-01) |
| `low_stock_threshold` | `INT UNSIGNED` | D `5` | Seuil « stock faible » (US-039) |
| `status` | `ENUM('brouillon','en_attente','publié','archivé')` | D `'brouillon'` | Publication contrôlée par trigger |
| `is_featured` | `BOOLEAN` | D `FALSE` | Coup de cœur accueil |
| `views_count` | `INT UNSIGNED` | D `0` | Statistique SEO |
| `published_at` | `DATETIME` | — | Date de publication |

**Index** : `idx_products_shop`, `idx_products_category_status`, `idx_products_price`, `idx_products_status_featured`, `FULLTEXT ft_products_search(name, short_description, description)`.

### 3.4 `product_images`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `INT UNSIGNED` | PK, AI | Identifiant image |
| `product_id` | `INT UNSIGNED` | NN, FK → `products.id` ON DELETE CASCADE | Produit |
| `image_path` | `VARCHAR(255)` | NN | Chemin `public/uploads/products/…` |
| `alt_text` | `VARCHAR(180)` | NN | Alternatives textuelles (NFR-04/05) |
| `is_primary` | `TINYINT UNSIGNED` | `NULL` ou `1`, UK `(product_id,is_primary)` | Image principale |
| `sort_order` | `SMALLINT UNSIGNED` | D `0` | Ordre galerie |

### 3.5 `promo_codes` (US-054, Could)

`id`, `code UK`, `type ENUM('pourcentage','montant')`, `value DECIMAL(10,2) CHECK (>0)`, `min_amount_ttc`, `starts_at`, `ends_at`, `max_uses`, `uses_count D 0`, `is_active`, `created_at`, `updated_at`.

---

## 4. Panier et commande

### 4.1 `carts` (RG-10)

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `INT UNSIGNED` | PK, AI | Identifiant panier |
| `user_id` | `INT UNSIGNED` | FK → `users.id` ON DELETE CASCADE | `NULL` pour invité |
| `guest_token` | `VARCHAR(64)` | UK | Jeton panier anonyme |
| `status` | `ENUM('actif','converti','abandonné')` | D `'actif'` | Cycle de vie |
| `expires_at` | `DATETIME` | NN | `created_at + 30 jours` |
| `created_at` / `updated_at` | `DATETIME` | Horodatage | Purge via `sp_purge_abandoned_carts()` |

> Contrainte : `user_id IS NOT NULL OR guest_token IS NOT NULL`.

### 4.2 `cart_items`

`id`, `cart_id FK ON DELETE CASCADE`, `product_id FK`, `quantity INT UNSIGNED CHECK (>0)`, `unit_price_ttc DECIMAL(10,2)`, `created_at`, `updated_at`, **UK `(cart_id, product_id)`**.  
Le trigger `trg_cart_items_bi` refuse une quantité > stock et un produit non publié (RG-01).

### 4.3 `orders`

| Champ | Type | Contraintes | Commentaire |
|---|---|---|---|
| `id` | `INT UNSIGNED` | PK, AI | Identifiant commande |
| `order_number` | `VARCHAR(30)` | NN, UK | `CMD-YYYY-NNNN` |
| `user_id` | `INT UNSIGNED` | FK → `users.id` ON DELETE SET NULL | Client (ou invité) |
| `guest_email` | `VARCHAR(190)` | — | Email invité |
| `status` | `ENUM('en_attente','payée','en_préparation','expédiée','livrée','annulée','remboursée')` | D `'en_attente'` | **RG-04** |
| `delivery_mode` | `ENUM('domicile','retrait')` | D `'domicile'` | Retrait gratuit (US-055) |
| `subtotal_ht` / `vat_amount` / `subtotal_ttc` | `DECIMAL(10,2)` | D `0` | **RG-02** |
| `discount_amount` / `shipping_fee` / `total_ttc` | `DECIMAL(10,2)` | D `0` | **RG-03**, remise |
| `promo_code_id` / `address_id` | `INT UNSIGNED` | FK `SET NULL` | Code promo / adresse |
| `shipping_line1` / `shipping_postal_code` / `shipping_city` | `VARCHAR` | — | **Snapshot** d'adresse |
| `transaction_ref` | `VARCHAR(60)` | UK | Référence **sandbox** (RG-07) |
| `payment_method` | `ENUM('sandbox')` | — | Aucun autre moyen en V1 |
| `paid_at` / `placed_at` | `DATETIME` | — / D `NOW()` | Horodatage |

### 4.4 `order_items`

`id`, `order_id FK ON DELETE CASCADE`, `product_id FK`, `shop_id FK` (snapshot vendeur), `product_name`, `product_slug`, `unit_price_ttc`, `quantity`, `line_total_ttc`, `created_at`.  
**Le trigger `trg_order_items_ai` décrémente le stock (RG-01).**

### 4.5 `order_status_history`

`id`, `order_id FK ON DELETE CASCADE`, `from_status`, `to_status`, `changed_by FK users`, `note`, `created_at`.  
Alimenté automatiquement par `trg_orders_au` (RG-04, RG-11).

### 4.6 `payments` (RG-07)

`id`, `order_id UK FK`, `transaction_ref UK` (`SBX-YYYY-NNNNNN`), `method ENUM('sandbox')`, `amount DECIMAL(10,2)`, `status ENUM('en_attente','accepté','refusé')`, `processed_at`, `created_at`.  
> ⚠️ **Aucun champ numéro de carte, date d'expiration ni CVC** : la carte de test n'est qu'un libellé dans l'interface.

### 4.7 `invoices` (RG-08)

`id`, `invoice_number UK` (`F-YYYY-NNNN`), `order_id UK FK`, `issued_at`, `subtotal_ht`, `vat_amount`, `shipping_fee`, `discount_amount`, `total_ttc`, `pdf_path`.  
Créée automatiquement à la transition vers `payée`.

---

## 5. Avis, audit et marketing

### 5.1 `reviews` (RG-05)

`id`, `user_id FK`, `product_id FK`, `order_id FK`, `rating TINYINT UNSIGNED CHECK (1..5)`, `comment TEXT`, `status ENUM('en_attente','publié','masqué','refusé') D 'en_attente'`, `moderated_by FK`, `moderated_at`, `created_at`, `updated_at`.  
**UK `(user_id, product_id, order_id)`** + trigger `trg_reviews_bi` : achat livré obligatoire.

### 5.2 `audit_logs` (RG-11)

`id BIGINT`, `user_id FK SET NULL`, `action VARCHAR(80)`, `entity_type VARCHAR(50)`, `entity_id VARCHAR(50)`, `ip_address VARCHAR(45)`, `user_agent VARCHAR(255)`, `details TEXT` (JSON), `created_at`.  
Index : `idx_audit_created`, `idx_audit_user`, `idx_audit_action`.

### 5.3 `newsletter_subscribers`

`id`, `email UK`, `is_active BOOLEAN`, `subscribed_at`.

### 5.4 `contact_messages`

`id`, `name`, `email`, `subject`, `message TEXT`, `is_read BOOLEAN`, `created_at`.

---

## 6. Vues

| Vue | Contenu |
|---|---|
| `v_catalogue` | Produits **publiés** + boutique **validée** + catégorie active + note moyenne + image principale |
| `v_low_stock` | Produits publiés en stock faible ou rupture, avec nom de boutique |
| `v_cart_totals` | Totaux HT/TVA/livraison/TTC par panier (RG-02, RG-03) |
| `v_orders_detail` | Commandes + client + nombre d'articles + facture |
| `v_reviews_published` | Avis publiés avec nom produit, boutique et client |
| `v_seller_sales` | Chiffre d'affaires par boutique et par produit |

---

## 7. Domaines et formats

| Domaine | Format | Exemple |
|---|---|---|
| `EMAIL` | `VARCHAR(190)`, regex applicative | `aymen.benali@tunishop.tn` |
| `TND` | `DECIMAL(10,2)`, 2 décimales, séparateur `,` à l'affichage | `48.00` → `48,00 DT` |
| `SLUG` | minuscules, tirets, sans accent | `vase-bleu-et-blanc` |
| `TEL_TN` | `+216 XX XXX XXX` | `+216 71 234 567` |
| `DATE` | `DATETIME` (UTC local Europe/Tunis) | `2026-11-20 14:30:00` |
| `STATUT_CMDE` | `en_attente → payée → en_préparation → expédiée → livrée` (+ `annulée`, `remboursée`) | — |
| `NOTE` | entier 1–5 | `5` |
| `IP` | `VARCHAR(45)` (IPv4/IPv6) | `127.0.0.1` |

---

## 8. Contraintes d'intégrité synthétiques

- **20 clés primaires**, **27 clés étrangères**, **47 index déclarés** (dont 20 uniques et 1 FULLTEXT) ;
- `ON DELETE CASCADE` pour les dépendances fortes (images, lignes, paniers) ;
- `ON DELETE SET NULL` pour les références historiques (client, admin, adresse) ;
- `ON DELETE RESTRICT` pour les références catalogue (catégorie, produit commandé) ;
- Toutes les tables en **InnoDB** (transactions, verrouillage `FOR UPDATE`, FK).

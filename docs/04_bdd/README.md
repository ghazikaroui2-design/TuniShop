# TuniShop — Livrables Base de données (matière Bases de données)

| Champ | Valeur |
|---|---|
| **Matière** | Bases de données (MCD/MLD, SQL avancé, intégrité, vues, triggers, procédures) |
| **Période** | Sprint 4 → Sprint 5 ( Validation 2 : 20/12/2026 ) |
| **SGBD cible** | MySQL 8.0 (InnoDB) — scripts également validés sur **MariaDB 10.11** (XAMPP local) |
| **Jeu d'essai** | 8 vendeurs (5 validés, 3 à valider), 10 clients, 24 produits, 6 catégories, 10 commandes |
| **Références** | [Cahier des charges](../01_gestion_projet/cahier_des_charges.md) · [Backlog](../01_gestion_projet/backlog_produit.md) · [Prototype UX](../02_ux_ui/prototype_tunishop.html) |

---

## 1. Fichiers livrés

| Fichier | Rôle | US couvertes |
|---|---|---|
| [dictionnaire_donnees.md](dictionnaire_donnees.md) | Dictionnaire des données : tables, champs, types, contraintes, domaines, index | US-016, US-017 |
| [mcd_mld.md](mcd_mld.md) | Modèle Conceptuel (Merise) + Modèle Logique, cardinalités, règles de gestion traduites | US-016, US-017 |
| [../../database/schema.sql](../../database/schema.sql) | DDL complet : 20 tables, clés, contraintes, index, 6 vues | US-017, US-050 |
| [../../database/procedures.sql](../../database/procedures.sql) | 7 fonctions + 13 procédures stockées (checkout, paiement, stats, RG) | US-023, US-024, US-037, US-038, US-050 |
| [../../database/triggers.sql](../../database/triggers.sql) | 11 triggers : stock, facturation, historique, audit, avis, RG-06 | US-023, US-044, US-048 |
| [../../database/seed.sql](../../database/seed.sql) | Jeu d'essai réaliste rechargeable | US-018, NFR-07 |
| [../../database/tests.sql](../../database/tests.sql) | Scénarios de vérification SQL (triggers, TVA, panier, avis) | US-023, US-049 |
| [../../database/README.md](../../database/README.md) | Procédure d'import, sauvegarde/restauration | US-056, NFR-09 |

---

## 2. Couverture des règles métier (RG-01 → RG-11)

| Règle | Traduction technique |
|---|---|
| **RG-01** Stock décrémenté | Trigger `trg_order_items_ai` + verrou `SELECT … FOR UPDATE` dans `sp_place_order` + garde-fou `trg_cart_items_bi` |
| **RG-02** TVA 19 % incluse | Prix stockés **TTC** ; `fn_vat_from_ttc()` / `fn_vat_amount_from_ttc()` ; facture détaillée HT/TVA/TTC |
| **RG-03** Livraison 8 DT, offerte ≥ 100 DT | `sp_calculate_cart_totals()` ; `delivery_mode = 'retrait'` → 0 DT (US-055) |
| **RG-04** Cycle de statuts | `CHECK`/`ENUM` + `sp_update_order_status()` (transitions autorisées) + `order_status_history` |
| **RG-05** Avis après achat, modéré | Trigger `trg_reviews_bi` + `fn_can_review()` + `UNIQUE(user_id, product_id, order_id)` + `sp_moderate_review()` |
| **RG-06** Vendeur validé avant publication | Triggers `trg_shops_bi` (statut forcé à `en_attente`) et `trg_products_bi/bu` |
| **RG-07** Paiement simulé | Table `payments` : **aucune donnée bancaire**, uniquement `transaction_ref` (`SBX-…`) + `method='sandbox'` |
| **RG-08** Facture `F-YYYY-0001` | Table `sequences` + `fn_next_sequence()` + trigger `trg_orders_au` à la transition vers `payée` |
| **RG-09** Mots de passe hachés | `password_hash VARCHAR(255)` — jeu d'essai en **Argon2id** (+ 1 compte bcrypt pour démontrer le repli) |
| **RG-10** Panier 30 jours | `carts.expires_at` + `sp_purge_abandoned_carts()` |
| **RG-11** Journalisation | `audit_logs` + triggers sur `users`, `shops`, `orders`, `reviews` + `sp_log_action()` |

---

## 3. Correspondance avec les écrans du prototype

| Écran prototype | Tables principales |
|---|---|
| 01 Accueil / 02 Catalogue / 03 Fiche produit | `v_catalogue`, `products`, `product_images`, `categories`, `shops`, `reviews` |
| 04 Panier | `carts`, `cart_items`, `sp_calculate_cart_totals()` |
| 05 Checkout / 06 Confirmation | `sp_place_order()`, `sp_simulate_payment()`, `orders`, `payments`, `invoices` |
| 07 Espace client | `orders`, `order_status_history`, `invoices`, `reviews`, `fn_can_review()` |
| 08 Ma boutique / 09 Produits & stocks | `shops`, `products`, `product_images`, `v_low_stock` |
| 10 Commandes | `v_orders_detail`, `sp_update_order_status()` |
| 11 Tableau de bord | `sp_get_dashboard_stats()`, `sp_top_products()`, `v_seller_sales` |
| 12 Validation vendeurs | `sp_validate_shop()`, `audit_logs` |

---

## 4. Correction apportée au prototype

Le panier du prototype affichait `Sous-total HT 71,43 DT`, `TVA 13,57 DT` et `TOTAL TTC 93,00 DT` pour 86,00 DT TTC d'articles (48 + 38).  
Le calcul exact avec une TVA de 19 % **incluse** est :

- HT = 86,00 / 1,19 = **72,27 DT**
- TVA = 86,00 − 72,27 = **13,73 DT**
- Livraison = **8,00 DT** (seuil 100 DT non atteint)
- **Total TTC = 94,00 DT** (et non 93,00 DT : 86 + 8 = 94)

La base de données calcule ces valeurs exactement via `fn_vat_from_ttc()` et `sp_calculate_cart_totals()` ; le prototype HTML a été corrigé en conséquence (montants cohérents sur les 12 écrans).

---

## 5. Convention de nommage

| Objet | Convention | Exemple |
|---|---|---|
| Table | `snake_case` pluriel | `order_items` |
| Clé primaire | `id` | `products.id` |
| Clé étrangère | `<table_singulier>_id` | `shop_id` |
| Index | `idx_<table>_<champs>` | `idx_products_category_status` |
| Contrainte | `fk_/uq_/ck_` | `fk_products_shop` |
| Vue | `v_<nom>` | `v_catalogue` |
| Procédure | `sp_<verbe>_<objet>` | `sp_place_order` |
| Fonction | `fn_<verbe>_<objet>` | `fn_next_sequence` |
| Trigger | `trg_<table>_<avant/après><action>` | `trg_order_items_ai` |

 jeu d'essai : mot de passe commun **`Test1234!`**, e-mails `prenom.nom@tunishop.tn`.

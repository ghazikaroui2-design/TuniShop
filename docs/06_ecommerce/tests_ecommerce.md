# TuniShop — Recette du module E-commerce (15 scénarios)

> Fichier exécutable : [`tests/ecommerce.php`](../../tests/ecommerce.php).
> Ce document décrit chaque scénario, la commande pour le lancer et le résultat observé.

---

## 1. Commandes de validation

```powershell
# Recette e-commerce seule
& 'C:\xampp\php\php.exe' tests/ecommerce.php

# Ou via Composer
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' test:ecommerce

# Toutes les recettes (socle MVC + e-commerce)
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' test:all

# Vérification SQL (jeu d'essai inchangé)
& 'C:\xampp\mysql\bin\mysql.exe' --default-character-set=utf8mb4 -u root -e "SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/database/tests.sql"
```

**Prérequis** : base `tunishop` importée, `composer install` exécuté, PHP 8.3 avec extensions `pdo_mysql`, `dom`, `mbstring`.

**Résultat final : `15 PASS / 0 FAIL`** ✅

> Les scénarios E06 à E09 créent des paniers et commandes temporaires, puis les suppriment dans un bloc `finally`. Le jeu d'essai de démonstration reste strictement inchangé, comme le confirment les 25 tests SQL relancés après la recette.

---

## 2. Tableau des scénarios

| ID | Scénario | Ce qui est vérifié | Résultat |
|---|---|---|---|
| **E01** | DomPDF installé | `Dompdf\Dompdf` et `Dompdf\Options` chargés via l'autoload Composer | PASS |
| **E02** | Route `order.invoice` | `GET /commande/{id}/facture.pdf` déclarée avec le middleware `auth` | PASS |
| **E03** | Étape 1 — livraison | `POST /commande` (`etape=1`, `delivery_mode=domicile`, `address_id=1`) → 302 vers `/commande?etape=2` et choix stocké en session | PASS |
| **E04** | Étape 2 — formulaire sandbox | La page contient `4242 4242 4242 4242`, `ARTISAN5`, `confirm_sandbox`, mais **aucun champ `name="card_number"` / `name="card_cvc"`** | PASS |
| **E05** | RG-07 — carte de test | `4242 4242 4242 4242` acceptée, `4111 1111 1111 1111` refusée | PASS |
| **E06** | FR-08 — quantité | Passer de 1 à 3 exemplaires du produit 1 → quantité 3, sous-total 144,00 DT, TVA 22,99 DT (TVA 19 % incluse) | PASS |
| **E07** | RG-01 — stock | Une quantité de 9 999 déclenche une `PDOException` depuis `sp_add_to_cart` | PASS |
| **E08** | FR-10 — code promo | `ARTISAN5` donne −5,00 DT, total 51,00 DT (48 + 8 − 5), numéro `CMD-2026-…` | PASS |
| **E09** | RG-07 / RG-08 — paiement | Référence `SBX-2026-…`, statut `payée`, facture `F-2026-NNNN`, total 56,00 DT | PASS |
| **E10** | RG-07 — schéma BDD | Aucune colonne `card_number`, `cvc` ou `expiry` dans `payments` | PASS |
| **E11** | FR-26 — HTML facture | Le gabarit contient `F-2026-0001`, `72,27 DT`, `13,73 DT`, `94,00 DT` et `SBX-` | PASS |
| **E12** | FR-26 — PDF | `renderPdf(1)` produit un flux commençant par `%PDF-` et long de plus de 2 Ko | PASS |
| **E13** | FR-26 — téléchargement | `GET /commande/1/facture.pdf` → 200, `application/pdf`, `F-2026-0001.pdf` en pièce jointe | PASS |
| **E14** | RG-08 — `pdf_path` | `invoices.pdf_path` vaut `factures/F-2026-0001.pdf` après génération | PASS |
| **E15** | RBAC facture | Un autre client reçoit une 404 neutre (pas de fuite d'information) | PASS |

---

## 3. Détail des scénarios

### E01 — Dépendance PDF

```php
class_exists(\Dompdf\Dompdf::class) && class_exists(\Dompdf\Options::class)
```

Vérifie que `composer.json`, `composer.lock` et `vendor/autoload.php` sont cohérents. C'est le garde-fou contre un oubli de `composer install` sur un clone.

### E02 — Route de facture

Recherche de la route nommée `order.invoice` dans `config/routes.php` : méthode `GET`, chemin `/commande/{id}/facture.pdf`, middleware `auth`. Le passage de 22 à **23 routes** est ainsi figé par un test.

### E03 — Étape 1 du tunnel

Le test construit une requête POST avec un jeton CSRF valide (session CLI), appelle `$app->handle(...)` et vérifie :

- code HTTP 302 ;
- en-tête `Location: /commande?etape=2` ;
- clé de session `checkout` = `['delivery_mode' => 'domicile', 'address_id' => 1]`.

### E04 — Étape 2 du tunnel

La vue `checkout/index` est rendue via le routeur. Le test vérifie la présence des éléments pédagogiques (carte de test, code promo, case de confirmation) **et l'absence de champs bancaires nommés** par expression régulière : c'est la traduction automatique de la RG-07 côté interface.

### E05 — Validation de la carte de test

`PaymentService::isValidTestCard()` est volontairement cosmétique : il reconnaît `4242 4242 4242 4242` et rejette une carte arbitraire. Aucune autre carte n'est acceptée.

### E06 — Modification de quantité

Création d'un panier invité temporaire, deux appels à `CartService::updateQuantity()` (1 puis 3), puis lecture de `cart_items.quantity` et de `v_cart_totals` :

```text
3 × 48,00 DT = 144,00 DT TTC
HT  = ROUND(144 / 1,19 ; 2) = 121,01 DT
TVA = 144 − 121,01 = 22,99 DT
```

Le panier est supprimé dans le `finally`, même si une assertion échoue.

### E07 — Refus de quantité hors stock

Le stock du produit 1 est de 10 unités. Une demande de 9 999 exemplaires doit faire échouer `sp_add_to_cart` avec `SQLSTATE 45000` (« Stock insuffisant »), traduit en `PDOException` côté PHP.

### E08 — Code promo

Panier à 48,00 DT + livraison 8,00 DT + remise `ARTISAN5` 5,00 DT = **51,00 DT**. Le test vérifie aussi le préfixe du numéro de commande `CMD-2026-` et restaure `promo_codes.uses_count` après suppression.

### E09 — Paiement et facture

Chaîne complète :

1. `sp_place_order()` → commande `en_attente` ;
2. `PaymentService::simulate()` → référence `SBX-2026-NNNNNN` ;
3. `sp_simulate_payment()` → statut `payée` ;
4. `trg_orders_au` → facture `F-2026-NNNN` ;
5. contrôle du total `56,00 DT` (48 + 8).

### E10 — Absence de colonnes bancaires

Interrogation de `information_schema.columns` : `payments` ne contient ni `card_number`, ni `cvc`, ni `expiry`. Ce test complète le test SQL **T23** qui vérifie l'absence de données bancaires dans l'audit.

### E11 — Contenu du gabarit

`InvoiceService::renderHtml(1)` est appelé sur la commande 1 du jeu d'essai. Le test contrôle les trois montants arrondis à la française, le numéro de facture et la référence sandbox.

### E12 — Génération binaire

`Dompdf` produit un flux `%PDF-1.x`. Le seuil de 2 Ko évite qu'un PDF vide (erreur de rendu silencieuse) soit considéré comme valide.

### E13 — Route HTTP complète

Le routeur est appelé avec la session du client de démonstration. La réponse doit avoir :

- `Content-Type: application/pdf` ;
- `Content-Disposition: attachment; filename="F-2026-0001.pdf"` ;
- `Content-Length` cohérent ;
- `X-Content-Type-Options: nosniff`.

### E14 — Persistance du chemin

`UPDATE invoices SET pdf_path = 'factures/…'` est bien exécuté par `InvoiceService::save()`. Le fichier lui-même est ignoré par Git (`public/factures/*.pdf`).

### E15 — Autorisation

Le test change l'utilisateur de session pour un autre client, appelle la route de facture de la commande 1, puis s'attend à une `NotFoundException` avec statut 404. La session d'origine est restaurée dans le `finally`.

---

## 4. Sortie console attendue

```text
PASS  E01 — DomPDF installé (dépendance Composer)
PASS  E02 — Route nommée order.invoice déclarée
PASS  E03 — Étape 1 : la livraison est validée et stockée en session
PASS  E04 — Étape 2 : le formulaire sandbox est rendu sans données bancaires
PASS  E05 — RG-07 : carte de test reconnue, carte arbitraire refusée
PASS  E06 — FR-08 : modifier une quantité recalcule les totaux
PASS  E07 — RG-01 : quantité supérieure au stock refusée
PASS  E08 — FR-10 : code promo ARTISAN5 (−5 DT) appliqué au checkout
PASS  E09 — RG-07/RG-08 : paiement sandbox génère référence SBX et facture F-YYYY-NNNN
PASS  E10 — RG-07 : aucune colonne bancaire dans la table payments
PASS  E11 — FR-26 : le HTML de facture contient HT, TVA, TTC et la référence
PASS  E12 — FR-26 : DomPDF produit un fichier %PDF valide
PASS  E13 — FR-26 : GET /commande/1/facture.pdf télécharge un PDF
PASS  E14 — RG-08 : invoices.pdf_path renseigné après génération
PASS  E15 — Accès refusé à la facture d’un autre client

=== 15 PASS / 0 FAIL ===
```

---

## 5. Couverture globale

| Suite | Commande | Résultat |
|---|---|---|
| Socle MVC | `composer test` | 23 PASS / 0 FAIL |
| E-commerce | `composer test:ecommerce` | 15 PASS / 0 FAIL |
| Base de données | `database/tests.sql` | 25 PASS / 0 FAIL |
| **Total** | `composer test:all` + SQL | **63 PASS / 0 FAIL** |

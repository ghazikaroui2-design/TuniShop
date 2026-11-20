# TuniShop — Livrables POO / MVC / UML (matière Programmation orientée objet avancée)

| Champ | Valeur |
|---|---|
| **Matière** | POO avancée / génie logiciel — UML, architecture MVC, modélisation objet |
| **Période** | Sprint 5 (socle) → Sprint 7 (intégration) — Validation 2 : 20/12/2026 |
| **Langage** | PHP **8.3** (typage strict, propriétés `readonly`, named arguments, `match`, enums) |
| **Architecture** | MVC maison + routeur frontal + middlewares + couche Services |
| **Persistance** | PDO MySQL, requêtes préparées, procédures stockées existantes (`database/`) |
| **Références** | [Cahier des charges](../01_gestion_projet/cahier_des_charges.md) · [Backlog](../01_gestion_projet/backlog_produit.md) · [Base de données](../04_bdd/README.md) · [Prototype UX](../02_ux_ui/prototype_tunishop.html) |

---

## 1. Fichiers livrés

| Document | Contenu | US couvertes |
|---|---|---|
| [architecture_mvc.md](architecture_mvc.md) | Architecture MVC, composants Core, pipeline HTTP, table de routage, middlewares, injection de dépendances | US-025, US-026, US-027 |
| [diagrammes_uml.md](diagrammes_uml.md) | UML : cas d'utilisation, classes, séquences, composants, états (Mermaid) | US-025, US-028 |
| [poo_concepts.md](poo_concepts.md) | Concepts POO et principes SOLID appliqués, patterns de conception, choix techniques justifiés | US-025, US-026 |

### Code applicatif (socle livré et testé)

| Dossier / fichier | Rôle |
|---|---|
| `composer.json` | Autoload PSR-4 `App\` → `app/` ; script `composer test` |
| `.env.example` | Configuration locale (jamais de secret versionné) |
| `config/app.php` | Paramètres applicatifs, session, RG-02/RG-03, RG-10 |
| `config/database.php` | Connexion PDO et options de sécurité |
| `config/routes.php` | **22 routes** nommées, avec middlewares |
| `public/index.php` | Routeur frontal, unique point d'entrée HTTP |
| `app/Core/` | `Application`, `Router`, `Request`, `Response`, `Session`, `View`, `Database`, `Controller` |
| `app/Middlewares/` | `AuthMiddleware`, `RoleMiddleware`, `CsrfMiddleware`, `MiddlewareInterface` |
| `app/Models/` | `Model`, `Product`, `Category`, `Shop`, `Cart`, `Order`, `User` |
| `app/Services/` | `CartService`, `OrderService`, `PaymentService`, `ShopService`, `AuditService` |
| `app/Controllers/` | `Home`, `Catalog`, `Cart`, `Auth`, `Order`, `Seller`, `Admin`, `Api`, `System` |
| `app/Views/` | Gabarits PHP : layout, accueil, catalogue, panier, checkout, auth, confirmation, dashboard |
| `app/Exceptions/` | `HttpException`, `NotFoundException`, `ForbiddenException`, `ValidationException` |
| `public/assets/css/app.css` | Feuille de style du socle (responsive) |
| `tests/run.php` | **23 tests de recette** CLI |

---

## 2. Validation

```powershell
# 1. Autoload (après ajout d'une classe)
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' dump-autoload

# 2. Lint de tous les fichiers PHP
$php='C:\xampp\php\php.exe'
Get-ChildItem -Path app,config,public,tests -Recurse -Filter *.php | % { & $php -l $_.FullName }

# 3. Tests de recette (base `tunishop` importée)
& 'C:\xampp\php\php.exe' tests/run.php
# ou : & 'C:\Users\USER\AppData\Local\Composer\composer.bat' test
```

**Résultat final : `23 PASS / 0 FAIL`** ✅

| Groupe | Tests | Vérifications |
|---|---|---|
| Structure & autoload | T01–T03 | PSR-4, 22 routes, normalisation des URL |
| Composants Core | T04–T06 | JSON, échappement HTML, jeton CSRF |
| Middlewares | T07–T09 | blocage CSRF (419), RBAC (403) |
| Routage / rendu | T10–T16 | accueil, catalogue, `{slug}`, API, santé, 302, 404 |
| Modèles & règles métier | T17–T23 | 20 tables/6 vues, catalogue, RG-02/03, RG-07, RG-08, RG-11 |

> Le serveur local démarre avec `php -S localhost:8000 -t public` depuis la racine du projet (ou via le module Apache XAMPP pointant sur `public/`).

---

## 3. Couverture des règles métier côté application

| Règle | Traduction dans le code PHP |
|---|---|
| **RG-01** | `CartService::add()` appelle `sp_add_to_cart()` ; le trigger SQL bloque un stock insuffisant, `CartController` transforme l'erreur en message utilisateur |
| **RG-02 / RG-03** | `CartService::totals()` lit `v_cart_totals` (HT, TVA 19 % incluse, livraison 8 DT / offerte ≥ 100 DT) |
| **RG-04** | `OrderController` + `sp_place_order()` ; statuts et transitions gérés côté base |
| **RG-05** | Avis modérés via `sp_moderate_review()` (?? interface admin à brancher au Sprint 7) |
| **RG-06** | `ShopService::validate()` → `sp_validate_shop()` ; route admin protégée par `role:admin` |
| **RG-07** | `PaymentService::isValidTestCard()` validation cosmétique uniquement ; `simulate()` n'envoie que l'ID de commande, aucune donnée bancaire |
| **RG-08** | `OrderController::confirmation()` affiche la facture générée par le trigger `trg_orders_au` |
| **RG-09** | `User::verify()` avec `password_verify()` ; création en **Argon2id** |
| **RG-10** | `CartService::currentCartId()` crée un panier `expires_at = NOW() + 30 DAY` |
| **RG-11** | `Application::handle()` pose le contexte d'audit et configure `AuditService` avant chaque requête |

---

## 4. Choix techniques majeurs

| Choix | Justification |
|---|---|
| Routeur frontal | URL propres, point de contrôle unique pour l'audit, la sécurité et les erreurs |
| PSR-4 Composer | Chargement automatique, pas de `require` manuel, conventions de l'écosystème PHP |
| `Request` / `Response` immuables | État prévisible, testabilité, économie de copie |
| Middlewares (Chaîne de responsabilité) | Auth, CSRF et RBAC déclarés dans la table de routage, réutilisables |
| Services métier | Logique applicative découplée des contrôleurs et des modèles |
| Active Record léger | CRUD générique sûr (whitelist des identifiants SQL) tout en restant simple à enseigner |
| Vues PHP natives | Pas de moteur externe ; échappement centralisé `View::e()` |
| SQL comme source de vérité | TVA, stock, facturation et audit restent dans la BDD (cohérence transactionalité) |

---

## 5. État d'avancement

- ✅ Socle MVC complet, linté et testé (**23 PASS**).
- ✅ 12 écrans du prototype couverts par les vues et les routes (accueil, catalogue, fiche, panier, checkout, confirmation, connexion, inscription, tableau de bord vendeur/admin, validation vendeur, API, santé).
- ⏳ Génération des factures PDF prévue dans le module **E-commerce** (Sprint 8).
- ⏳ Formulaire de modération des avis prévu dans l'administration (Sprint 7).

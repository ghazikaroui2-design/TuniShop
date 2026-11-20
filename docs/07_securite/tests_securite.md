# TuniShop — Recette de sécurité (18 scénarios)

> Fichier exécutable : [`tests/security.php`](../../tests/security.php).
> Ces tests couvrent NFR-01, RG-09, RG-11 et les catégories OWASP A01 à A10.

---

## 1. Commandes

```powershell
# Sécurité seule
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' test:security

# Toutes les suites PHP
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' test:all
```

**Résultat final : `18 PASS / 0 FAIL`** ✅

> Chaque scénario utilise une application et une session CLI neuves via la fabrique `$makeApp()`. Le seul utilisateur créé (S09) est supprimé dans un `finally` : le jeu d'essai reste inchangé.

---

## 2. Tableau des scénarios

| ID | Scénario | Catégorie OWASP | Résultat |
|---|---|---|---|
| **S01** | En-têtes de sécurité sur une réponse HTML | A02, A05 | PASS |
| **S02** | En-têtes de sécurité sur une erreur 404 | A05 | PASS |
| **S03** | CSP : pas de script inline, pas d'encastrement | A03, A05 | PASS |
| **S04** | Échappement HTML des charges XSS | A03 | PASS |
| **S05** | CSRF : POST sans jeton (419) vs jeton valide (302) | A01, A08 | PASS |
| **S06** | Jeton CSRF renouvelé après `regenerate()` | A01, A07 | PASS |
| **S07** | RBAC : client 403 sur `/admin`, admin 200 | A01 | PASS |
| **S08** | Session requise : 302 en HTML, 401 en API | A01 | PASS |
| **S09** | Inscription hachée en Argon2id | A02, A07 | PASS |
| **S10** | Politique : mots de passe forts acceptés | A07 | PASS |
| **S11** | Politique : mots de passe faibles refusés | A07 | PASS |
| **S12** | Verrou après 5 échecs de connexion | A07 | PASS |
| **S13** | Verrou : bons identifiants également refusés | A07 | PASS |
| **S14** | Message d'erreur identique (anti-énumération) | A07 | PASS |
| **S15** | Whitelist des identifiants SQL | A03 | PASS |
| **S16** | Entrées typées et bornées (tableaux rejetés) | A03 | PASS |
| **S17** | Aucun mot de passe dans `audit_logs` | A09 | PASS |
| **S18** | Cookies de session durcis (`HttpOnly`, `SameSite`, durée) | A07 | PASS |

---

## 3. Détail des scénarios

### S01 — En-têtes sur une réponse HTML

`Response::html(...)->headers` doit contenir `nosniff`, `DENY`, `strict-origin-when-cross-origin` et une `Permissions-Policy` interdisant `payment=()`.

### S02 — En-têtes sur une erreur

`NotFoundException::toResponse()` produit une page neutre avec le statut 404 **et** les mêmes en-têtes. C'est la conséquence directe de leur ajout dans le constructeur de `Response`.

### S03 — CSP

La `Content-Security-Policy` impose `script-src 'self'` et ne contient aucun `'unsafe-inline'` sur la directive `script-src`. Elle interdit `frame-ancestors` et `object-src`. Les styles inline restent autorisés car le CSS du prototype les utilise.

### S04 — XSS

`View::e('<script>alert(1)</script>')` et `View::e('<img src=x onerror="alert(1)">')` doivent renvoyer des entités HTML inoffensives.

### S05 — CSRF end-to-end

Premier appel : `POST /panier/ajouter` sans `_token` → **419**. Second appel avec le jeton de session → **302** vers `/panier`.

### S06 — Rotation du jeton

`csrfToken()` avant `regenerate()`, puis après : les deux jetons doivent différer, faire 64 caractères hexadécimaux, et seul le nouveau doit être accepté par `validateCsrf()`.

### S07 — RBAC

Session `client` sur `GET /admin` → `ForbiddenException` 403. Session `admin` sur la même route → 200. Le refus est journalisé `ACCESS_DENIED`.

### S08 — Authentification requise

`GET /admin` sans session → 302 avec `Location: /connexion`. Le middleware appelé avec `Content-Type: application/json` sur `/api/commandes` → 401.

### S09 — Argon2id

Création d'un utilisateur temporaire via `User::createClient('Test1234!')` ; le hash commence par `$argon2id$`, `password_verify()` réussit, et le clair n'apparaît pas dans le hash. Suppression en `finally`.

### S10 / S11 — Politique de mot de passe

Acceptés : `Test1234!`, `SidiBou2026!artisanat` (score ≥ 3). Refusés : `password`, `Ab1!`, `MAJUSCULES123!`, `motdepasselongsanschiffre`.

### S12 / S13 — Force brute

Cinq POST `/connexion` avec des identifiants erronés : `attempts() === 5`, `isLocked() === true`, `retryAfter() > 0`. Un sixième POST avec les **bons** identifiants ne connecte pas l'utilisateur et affiche le message de verrou.

### S14 — Anti-énumération

Un email inexistant et un email existant avec un mauvais mot de passe doivent produire exactement le même message flash, sans mention « existe » ni « introuvable ».

### S15 — Identifiants SQL

`identifier('products')` → `` `products` ``. `identifier('products; DROP TABLE users')` lève une `RuntimeException`.

### S16 — Validation des entrées

- `email[]` (tableau) → chaîne vide ;
- `'  AYMEN@TUNISHOP.TN  '` → `aymen@tunishop.tn` ;
- `product_id` démesuré → ramené à 1 000 000 ;
- `quantity = -5` → ramené à 1.

### S17 — Journalisation sans secret

Un échec de connexion avec un mot de passe marqué `S3cretAuditTest!…` est journalisé, puis une requête `LIKE '%secret%'` sur `audit_logs` doit renvoyer 0 ligne.

### S18 — Cookies

`config/app.php` : `httponly=true`, `samesite=Lax`, `lifetime ≤ 7200`.

---

## 4. Sortie console attendue

```text
PASS  S01 — En-têtes de sécurité présents sur une réponse HTML
PASS  S02 — En-têtes de sécurité présents sur une erreur 404
PASS  S03 — CSP interdit les scripts inline et l’encastrement
PASS  S04 — Échappement HTML centralisé contre les XSS
PASS  S05 — CSRF : POST sans jeton bloqué (419), jeton valide accepté
PASS  S06 — Jeton CSRF renouvelé après régénération de session
PASS  S07 — RBAC : client refusé sur /admin, admin accepté
PASS  S08 — Session requise : 302 (HTML) et 401 (API)
PASS  S09 — RG-09 : inscription hache en Argon2id
PASS  S10 — Politique de mot de passe : un mot fort est accepté
PASS  S11 — Politique de mot de passe : un mot faible est refusé
PASS  S12 — Verrou après 5 échecs de connexion
PASS  S13 — Verrou : les bons identifiants sont aussi refusés
PASS  S14 — Anti-énumération : message identique pour email inconnu ou mot de passe faux
PASS  S15 — Whitelist des identifiants SQL
PASS  S16 — Validation des entrées : tableaux rejetés, bornes respectées
PASS  S17 — Aucun mot de passe dans les journaux d’audit
PASS  S18 — Configuration des cookies de session durcie

=== 18 PASS / 0 FAIL ===
```

---

## 5. Couverture globale

| Suite | Commande | Résultat |
|---|---|---|
| Socle MVC | `composer test` | 23 PASS / 0 FAIL |
| E-commerce | `composer test:ecommerce` | 15 PASS / 0 FAIL |
| Sécurité | `composer test:security` | 18 PASS / 0 FAIL |
| Base de données | `database/tests.sql` | 25 PASS / 0 FAIL |
| **Total** | `composer test:all` + SQL | **81 PASS / 0 FAIL** |

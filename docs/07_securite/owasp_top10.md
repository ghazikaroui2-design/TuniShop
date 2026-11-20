# TuniShop face à l'OWASP Top 10 (2021)

> Tableau de conformité destiné à la soutenance : pour chaque catégorie, le contrôle réellement implémenté et la preuve de test.

---

## Synthèse

| # | Catégorie OWASP | Contrôle TuniShop | Preuve |
|---|---|---|---|
| **A01** | Broken Access Control | Middlewares `auth` / `role:*`, contrôle d'appartenance des commandes, 404 neutre, `X-Frame-Options: DENY` | S07, S08, E15, T09 |
| **A02** | Cryptographic Failures | Argon2id, PDO sans émulation de requêtes préparées, aucun secret versionné, CSP et `Referrer-Policy` | S01, S03, S09, T23 |
| **A03** | Injection | Requêtes préparées partout, `Database::identifier()` en whitelist, `InputSanitizer`, `View::e()` | S04, S15, S16, T17 |
| **A04** | Insecure Design | Règles métier dans la BDD (stock, TVA, transitions), RBAC dès la conception, ring-fencing du paiement simulé | E07, E10, T24, T25 |
| **A05** | Security Misconfiguration | En-têtes de sécurité globaux, `APP_DEBUG=false` en production, `.env` ignoré, `composer.lock` épinglé | S01, S02, S18 |
| **A06** | Vulnerable Components | DomPDF **3.1.6** (versions 3.1.0–3.1.5 frappées d'avis), `composer audit` | E01, `composer.lock` |
| **A07** | Identification & Authentication Failures | Argon2id, politique de mot de passe, 5 tentatives max, verrou 15 min, anti-énumération, rotation de session | S06, S10–S14, S18 |
| **A08** | Software & Data Integrity | `composer.lock`, triggers de facturation, journalisation `audit_logs`, CSRF sur tout POST | S05, E14, E15, T22 |
| **A09** | Security Logging & Monitoring Failures | `sp_log_action()`, contexte `fn_audit_user()` / `fn_audit_ip()`, `LOGIN_*`, `ACCESS_DENIED`, aucun secret journalisé | S17, T23 |
| **A10** | Server-Side Request Forgery | Aucune récupération de ressource externe : `isRemoteEnabled=false` dans DomPDF, pas de `fetch` serveur | E12, doc [facturation PDF](../06_ecommerce/facturation_pdf.md) |

---

## 1. A01 — Broken Access Control

**Risque.** Un utilisateur modifie l'identifiant dans l'URL (`/commande/2/facture.pdf`) pour lire la facture d'un autre client, ou accède directement à `/admin`.

**Contrôles.**
- `AuthMiddleware` : 302 vers `/connexion` pour une page HTML, 401 pour une API.
- `RoleMiddleware` : 403 si le rôle session n'est pas dans la liste autorisée ; journalise `ACCESS_DENIED`.
- `OrderController::loadOrderForCurrentUser()` : propriétaire ou admin uniquement, **404 neutre** sinon.
- `Address::belongsToUser()` : une adresse ne peut pas être choisie par un autre client.
- `X-Frame-Options: DENY` et CSP `frame-ancestors 'none'` : interdiction du clickjacking.

**Preuves :** S07, S08, E15, T09.

---

## 2. A02 — Cryptographic Failures

**Risque.** Mots de passe en clair, données bancaires stockées, fuite d'erreurs techniques.

**Contrôles.**
- `password_hash(..., PASSWORD_ARGON2ID)` et `password_verify()`.
- Aucun champ de carte transmis (`name` absent) ni colonne bancaire en base (E10, T23).
- `ATTR_EMULATE_PREPARES = false` : vraies requêtes préparées côté serveur.
- `Referrer-Policy: strict-origin-when-cross-origin` : pas de fuite d'URL sensible.
- Erreurs techniques masquées quand `APP_DEBUG=false` (`public/index.php`).
- `.env` et `vendor/` ignorés par Git ; `composer.lock` versionné.

**Preuves :** S01, S03, S09, T23.

---

## 3. A03 — Injection

**Risque.** Injection SQL via un champ de recherche ou un identifiant, XSS via un commentaire.

**Contrôles.**
- Toutes les valeurs passent par PDO et des marqueurs nommés ou positionnels.
- `Database::identifier()` n'autorise que `[A-Za-z_][A-Za-z0-9_]*` pour les noms de tables/colonnes (S15).
- `InputSanitizer` refuse les tableaux et borne les longueurs (S16).
- `View::e()` applique `htmlspecialchars(ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` sur toutes les données affichées (S04).
- CSP `default-src 'self'; script-src 'self'` : même si un script parvenait à être injecté, il ne s'exécuterait pas.

**Preuves :** S04, S15, S16, T17.

---

## 4. A04 — Insecure Design

**Risque.** Contourner le stock, la TVA ou la facturation en encourageant directement la base.

**Contrôles.**
- `sp_place_order()` : transaction + `SELECT ... FOR UPDATE`, panier `actif` obligatoire.
- `trg_order_items_ai` : décrémentation atomique du stock ; rejet des quantités excessives.
- `trg_orders_au` : facture créée uniquement au passage en `payée`, séquence monotone.
- `fn_is_valid_transition()` : transitions de statut interdites (T25).
- `sp_simulate_payment()` : un seul paiement par commande, référence unique.
- Avis conditionnés à une commande livrée (T24) et modérés (RG-05).

**Preuves :** E07, E09, E10, T24, T25.

---

## 5. A05 — Security Misconfiguration

**Contrôles appliqués à chaque réponse** (`Response::securityHeaders()`) :

```text
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), camera=(), microphone=(), payment=()
Content-Security-Policy: default-src 'self'; base-uri 'self';
  form-action 'self'; frame-ancestors 'none'; object-src 'none';
  img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'
```

Ces en-têtes sont ajoutés dans le **constructeur** de `Response` : ils sont donc présents sur les pages HTML, les JSON, les redirections, le PDF et les erreurs (S01, S02).

**Autres mesures :** `APP_DEBUG=false` en production, `.env.example` seul versionné, `composer.lock` figé, serveur web configuré pour pointer sur `public/` uniquement.

---

## 6. A06 — Vulnerable and Outdated Components

| Composant | Version | Action |
|---|---|---|
| PHP | 8.3.36 | Version supportée |
| Composer | 2.11.2 | À jour |
| `dompdf/dompdf` | **3.1.6** | Les versions 3.1.0 → 3.1.5 sont bloquées par 6 avis de sécurité |
| Dépendances DomPDF | `php-font-lib` 1.0.2, `php-svg-lib` 1.0.2, `masterminds/html5` 2.12.0, `php-css-parser` v9.5.0 | Verrouillées dans `composer.lock` |

Commande de vérification à passer avant chaque livraison :

```powershell
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' audit
```

---

## 7. A07 — Identification and Authentication Failures

Voir [authentification.md](authentification.md) : Argon2id, politique de mot de passe (S10, S11), verrou après 5 échecs (S12, S13), message unique (S14), rotation de session et de CSRF (S06), cookies `HttpOnly` / `SameSite=Lax` (S18).

---

## 8. A08 — Software and Data Integrity Failures

- `composer.lock` garantit des dépendances reproductibles ; jamais de mise à jour automatique en production.
- Le checkout s'appuie sur des procédures stockées signées par le schéma SQL livré, pas sur une logique JavaScript modifiable côté client.
- `CSRF` sur **tous** les POST (S05) empêche l'envoi d'une requête forgée depuis un site tiers.
- Les montants de la facture sont recopiés depuis `orders` par un trigger, pas recalculés dans le navigateur (E11, E14).

---

## 9. A09 — Security Logging and Monitoring Failures

Le contexte d'audit est posé **avant chaque requête** dans `Application::handle()` :

```php
$this->database->setAuditContext($userId, $ip, $userAgent);
AuditService::configure($this->database, $userId, $ip, $userAgent);
```

Les triggers lisent `@current_user_id`, `@client_ip`, `@client_ua` via `fn_audit_user()` et `fn_audit_ip()`. Événements journalisés : `LOGIN_SUCCESS`, `LOGIN_FAILED`, `LOGIN_THROTTLED`, `LOGOUT`, `USER_REGISTERED`, `ACCESS_DENIED`, `ORDER_PLACED`, `PAYMENT_SIMULATED`, `SHOP_STATUS_CHANGE`, `REVIEW_MODERATED`.

Le test **S17** garantit qu'aucun mot de passe n'entre dans `audit_logs`.

---

## 10. A10 — Server-Side Request Forgery

TuniShop ne récupère aucune URL fournie par l'utilisateur côté serveur. Les seules ressources externes potentielles seraient celles de DomPDF, désactivées explicitement :

```php
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
```

Le gabarit de facture n'intègre que des données locales et du CSS inline. Test E12 : le PDF est produit sans accès réseau.

---

## 11. Synthèse pour la soutenance (2 minutes)

1. Montrer `tests/security.php` : **18 PASS / 0 FAIL** — la sécurité est exécutable, pas seulement documentée.
2. Ouvrir `app/Core/Response.php` : les en-têtes sont globaux, donc impossibles à oublier sur une nouvelle route.
3. Ouvrir `app/Controllers/AuthController.php` : Argon2id + throttle + anti-énumération + audit.
4. Ouvrir `config/routes.php` : le RBAC est déclaratif, ligne par ligne.
5. Ouvrir `docs/06_ecommerce/paiement_sandbox.md` : RG-07 — aucune donnée bancaire n'est collectée.

# Checklist de sécurité TuniShop (NFR-01)

> Cases cochées = contrôle réellement implémenté et couvert par un test. 
> Les cases « production » sont des recommandations hors périmètre BTS.

---

## 1. Authentification et mots de passe

| # | Contrôle | État | Preuve |
|---|---|---|---|
| 1.1 | Hachage Argon2id via `password_hash()` | ✅ | S09 |
| 1.2 | Vérification avec `password_verify()` | ✅ | `User::verify()` |
| 1.3 | Aucun mot de passe en clair en base | ✅ | S09, T23 |
| 1.4 | Aucun mot de passe dans les logs | ✅ | S17 |
| 1.5 | Politique de mot de passe (8 caractères + 4 classes) | ✅ | S10, S11 |
| 1.6 | Limite de 5 tentatives avec verrou de 15 min | ✅ | S12, S13 |
| 1.7 | Message d'erreur générique anti-énumération | ✅ | S14 |
| 1.8 | Égalisation du temps de réponse (hash fictif) | ✅ | `AuthController::DUMMY_HASH` |
| 1.9 | Double authentification (2FA) | ⏳ | Production |

## 2. Sessions

| # | Contrôle | État | Preuve |
|---|---|---|---|
| 2.1 | `session_regenerate_id(true)` à la connexion | ✅ | `Session::regenerate()` |
| 2.2 | Jeton CSRF renouvelé avec la session | ✅ | S06 |
| 2.3 | Cookie `HttpOnly` | ✅ | S18 |
| 2.4 | Cookie `SameSite=Lax` | ✅ | S18 |
| 2.5 | Durée de vie ≤ 2 h | ✅ | S18 |
| 2.6 | `Secure=true` en HTTPS | ⚙️ | `SESSION_SECURE=true` en production |
| 2.7 | `destroy()` à la déconnexion | ✅ | `AuthController::logout()` |

## 3. CSRF et RBAC

| # | Contrôle | État | Preuve |
|---|---|---|---|
| 3.1 | Jeton CSRF sur tous les POST | ✅ | S05, routes `csrf` |
| 3.2 | Comparaison `hash_equals()` à temps constant | ✅ | `Session::validateCsrf()` |
| 3.3 | En-tête `X-CSRF-Token` accepté pour les API | ✅ | `CsrfMiddleware` |
| 3.4 | `auth` sur les routes privées | ✅ | S08 |
| 3.5 | `role:vendeur` / `role:admin` côté serveur | ✅ | S07 |
| 3.6 | Contrôle d'appartenance des commandes/factures | ✅ | E15 |
| 3.7 | 404 neutre au lieu de 403 sur ressource d'autrui | ✅ | `loadOrderForCurrentUser()` |
| 3.8 | Adresse de livraison vérifiée | ✅ | `Address::belongsToUser()` |

## 4. Données et injection

| # | Contrôle | État | Preuve |
|---|---|---|---|
| 4.1 | Requêtes préparées PDO (aucune concaténation de valeurs) | ✅ | `Database::fetch/fetchAll/execute` |
| 4.2 | `ATTR_EMULATE_PREPARES = false` | ✅ | `config/database.php` |
| 4.3 | Whitelist des identifiants SQL | ✅ | S15 |
| 4.4 | Validation et bornage des entrées | ✅ | S16 |
| 4.5 | Échappement HTML centralisé | ✅ | S04 |
| 4.6 | Transactions et verrous sur le checkout | ✅ | E07, E09 |
| 4.7 | Contraintes `CHECK`, FK et triggers InnoDB | ✅ | 25 tests SQL |

## 5. Réponse et navigateur

| # | Contrôle | État | Preuve |
|---|---|---|---|
| 5.1 | `X-Content-Type-Options: nosniff` | ✅ | S01 |
| 5.2 | `X-Frame-Options: DENY` | ✅ | S01 |
| 5.3 | CSP sans script inline | ✅ | S03 |
| 5.4 | `Referrer-Policy` | ✅ | S01 |
| 5.5 | `Permissions-Policy` | ✅ | S01 |
| 5.6 | En-têtes présents sur les erreurs | ✅ | S02 |
| 5.7 | Aucun détail technique en production | ✅ | `public/index.php` + `APP_DEBUG` |

## 6. Dépendances et configuration

| # | Contrôle | État | Preuve |
|---|---|---|---|
| 6.1 | `composer.lock` versionné | ✅ | Fichier à la racine |
| 6.2 | DomPDF 3.1.6 sans avis de sécurité | ✅ | E01 + `composer audit` |
| 6.3 | `vendor/` ignoré par Git | ✅ | `.gitignore` |
| 6.4 | `.env` ignoré, `.env.example` seul versionné | ✅ | `.gitignore` |
| 6.5 | Document root = `public/` | ✅ | `public/index.php` |
| 6.6 | Aucun secret (clé API, mot de passe BDD réel) dans le dépôt | ✅ | Revue `config/*.php` |

## 7. Journalisation et paiement

| # | Contrôle | État | Preuve |
|---|---|---|---|
| 7.1 | Contexte d'audit posé avant chaque requête | ✅ | `Application::handle()` |
| 7.2 | Événements de sécurité journalisés | ✅ | S17, T23 |
| 7.3 | Journal en base non modifiable par le client | ✅ | `audit_logs`, procédures |
| 7.4 | Paiement simulé, aucune donnée bancaire | ✅ | E04, E05, E10, T23 |
| 7.5 | PDF sans ressource distante (`isRemoteEnabled=false`) | ✅ | E12 |

---

## 8. Règles d'or pour le dépôt

**Ne jamais versionner :**

- `.env`, mot de passe MySQL, clés API ou secrets PSP ;
- `vendor/` (mais **toujours** versionner `composer.lock`) ;
- `public/factures/*.pdf` (données clients) ;
- captures d'écran contenant des jetons, cookies ou emails réels ;
- exports de production de `audit_logs`.

**Toujours vérifier avant un commit :**

```powershell
& 'C:\Program Files\Git\cmd\git.exe' status --short
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' audit
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' test:all
```

---

## 9. Plan de remédiation production (hors périmètre)

| Priorité | Action |
|---|---|
| P1 | Forcer `SESSION_SECURE=true` et HTTPS partout (HSTS inclus) |
| P1 | Limiteur de débit par IP dans un cache partagé |
| P1 | 2FA TOTP pour les rôles `vendeur` et `admin` |
| P2 | Renouvellement périodique des secrets et rotation des certificats |
| P2 | Sauvegardes chiffrées et testées de `audit_logs` |
| P3 | Scan DAST (OWASP ZAP) et revue de code pair-à-pair avant mise en ligne |

# Liste des anomalies — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 21/11/2026 |
| Période de recette | S1 → S5 (état des lieux au 21/11/2026) |
| Jalon associé | **Préjury 10/01/2027** — « liste des anomalies détectées avec priorité » |
| Sources | Recette PHP (69 tests), recette SQL (25 tests), revues de sprint, audit sécurité, revue de code |
| Lien | [Suivi des sprints](suivi_sprints.md) · [Recette frontend](../03_frontend/recette_frontend.md) |

---

## 1. Gravité et conventions

| Gravité | Définition | Délai de traitement |
|---|---|---|
| 🔴 **Bloquant** | Empêche la démonstration ou une validation de jalon | Avant le jalon concerné |
| 🟠 **Majeur** | Fonctionnalité attendue absente ou contournement nécessaire | Sprint en cours |
| 🟡 **Mineur** | Confort, esthétique, dette technique sans impact fonctionnel | V1.1 / réserve |
| ⚪ **Information** | Point de vigilance, non une anomalie | Suivi libre |

**États :** 🔵 Ouvert · 🟡 En cours · ✅ Corrigé · ⏭️ Reporté (V1.1).

## 2. Synthèse

| Gravité | Ouvert / en cours | Corrigé | Reporté | Total |
|---|---:|---:|---:|---:|
| 🔴 Bloquant | 0 | 2 | 0 | 2 |
| 🟠 Majeur | 2 | 2 | 0 | 4 |
| 🟡 Mineur | 1 | 0 | 5 | 6 |
| **Total** | **3** | **4** | **5** | **12** |

> Aucune anomalie **bloquante** n'est ouverte au 21/11/2026. Les deux anomalies bloquantes historiques (A-09, A-10) ont été corrigées dans le commit `7d74622`. Le lot P0 SEO (A-05) a été livré ensuite : **106 tests PHP PASS / 0 FAIL + 25 tests SQL PASS / 0 FAIL**, `composer audit` propre.

---

## 3. Registre des anomalies

### A-01 — Noms des 4 membres non renseignés

| | |
|---|---|
| Gravité | 🟠 Majeur (bloque la case Validation 1) |
| État | 🔵 Ouvert — en attente du chef de projet |
| Découvert le | 20/11/2026 (US-005) |
| Description | Le tableau « Équipe et rôles » de `suivi_sprints.md` contient `*à compléter*` pour les 4 rôles. DEC-007 fixe l'identité Git à Ghazi KAROUI, mais les 3 autres membres et la répartition définitive ne sont pas validés. |
| Impact | Case « Noms des 4 membres » non cochée au jalon Validation 1 ; soutenance impossible à préparer sans répartition des interventions. |
| Action corrective | Renseigner les 4 noms + valider la répartition des rôles, puis cocher la case du §5 de `suivi_sprints.md`. |
| Responsable | Chef de projet / PO |
| Échéance | 29/11/2026 |

### A-02 — Aucun remote Git configuré (push impossible)

| | |
|---|---|
| Gravité | 🟠 Majeur |
| État | 🔵 Ouvert — en attente de l'URL du dépôt |
| Découvert le | 20/11/2026 |
| Description | `git remote -v` ne renvoie aucune entrée. Les 5 commits sont locaux sur `develop` ; aucun push ni tag n'a pu être effectué. |
| Impact | Jalon Validation 3 : « Dépôt Git complet (branches fusionnées, tags) » non validable ; travail collaboratif impossible. |
| Action corrective | `git remote add origin <URL GitHub>` puis `git push -u origin develop` ; créer les tags `v0.1`, `v0.5`, `v1.0` puis pousser `main`. |
| Responsable | Back-end |
| Échéance | 10/01/2027 (avant préjury) |
| Contournement | Historique local complet et lisible (`git log --oneline`) — voir preuve dans `git_workflow.md`. |

### A-03 — Visuels produits non versionnés

| | |
|---|---|
| Gravité | 🟡 Mineur |
| État | ⏭️ Reporté V1.1 (contourné) |
| Découvert le | 20/11/2026 |
| Description | `Product::images()` renvoie des chemins `uploads/products/...` mais aucun fichier image n'est présent dans le dépôt (volontaire : `.gitignore` exclut `public/uploads/*`). |
| Impact | Pictogrammes de secours à la place des photos ; aucun `alt` d'image indexable (voir A-05 / SEO-04). |
| Action corrective | Upload sécurisé (extension + MIME + taille) puis dépôt des 24 visuels ; générer les `alt_text` depuis la BDD. |
| Responsable | UX/UI + Back-end |
| Échéance | V1.1 |
| Contournement ✅ | `CatalogController::show()` ajoute `exists` / `src` et la vue affiche `role="img"` + `aria-label`. Aucune image cassée, aucune requête 404. |

### A-04 — Upload du logo boutique non implémenté

| | |
|---|---|
| Gravité | 🟡 Mineur |
| État | ⏭️ Reporté V1.1 (contourné) |
| Découvert le | 21/11/2026 (écran 8) |
| Description | La zone « Logo de la boutique » de `app/Views/seller/shop.php` est informative : le dépôt de fichier n'est pas câblé. |
| Impact | Le vendeur ne peut pas personnaliser visuellement sa vitrine. Les champs textuels (nom, adresse, description, coordonnées) sont, eux, réellement enregistrés. |
| Action corrective | Endpoint `POST /vendeur/boutique/logo` avec validation serveur, puis mise à jour de `shops.logo_path`. |
| Responsable | Back-end |
| Échéance | V1.1 |
| Contournement ✅ | Message explicite dans l'UI : « L'upload sécurisé du logo sera disponible au prochain sprint. » |

### A-05 — SEO technique incomplet (US-047) ✅

| | |
|---|---|
| Gravité | 🟠 Majeur (corrigé) |
| État | ✅ Corrigé — 21/11/2026 |
| Découvert le | 21/11/2026 (audit SEO) |
| Description | 6 critères sur 12 étaient conformes (URLs, title, description, Hn, alt, lang). Manquaient : balise canonical, JSON-LD Schema.org, `sitemap.xml`, `robots.txt`, Open Graph, suivi KPI. |
| Impact | Critère de recette globale « SEO : balises uniques, sitemap.xml, robots.txt, JSON-LD, URLs propres » non coché ; US-047 non terminée. |
| Correction | Lot P0 livré : `Seo` (canonical + JSON-LD avec nonce CSP), `public/robots.txt`, `public/sitemap.xml` et `tools/generate_sitemap.php`, `X-Robots-Tag` par chemin, `tests/seo.php` (12 tests). Voir [plan SEO](../08_seo_marketing/plan_seo.md) §3.1. **10 critères/12 conformes** ; Open Graph et KPI restent en P1. |
| Responsable | Architecte BDD / Sécurité / SEO |
| Vérification | `composer test:seo` → 12 PASS / 0 FAIL ; `composer test:all` → 106 PASS / 0 FAIL. |

### A-06 — Limite de débit par IP absente en production

| | |
|---|---|
| Gravité | 🟡 Mineur |
| État | ⏭️ Reporté (production) |
| Découvert le | 20/11/2026 (audit OWASP) |
| Description | Le throttling 5 tentatives / 15 min est appliqué par compte (`LoginThrottle`). Aucune limite globale par IP derrière un reverse-proxy n'est configurée. |
| Impact | Risque de déni de service distribué si l'application est exposée publiquement sans protection amont. |
| Action corrective | Configurer `limit_req` (nginx) ou un WAF / CDN en production. |
| Responsable | Architecte sécurité |
| Échéance | Déploiement |
| Contournement | `LoginThrottle` + `InputSanitizer` + CSRF déjà actifs ; environnement de démo local. |

### A-07 — Double authentification (2FA) absente

| | |
|---|---|
| Gravité | 🟡 Mineur |
| État | ⏭️ Reporté V1.1 |
| Découvert le | 20/11/2026 (audit OWASP) |
| Description | Les comptes vendeurs et administrateurs ne disposent pas de 2FA (TOTP). |
| Impact | Un mot de passe volé suffit à accéder au back-office (atténué par Argon2id + throttle + audit RG-11). |
| Action corrective | Ajouter TOTP (`OTPHP`) pour les rôles `vendeur` et `admin`. |
| Responsable | Architecte sécurité |
| Échéance | V1.1 |

### A-08 — Prototype Figma livré sous forme de plan, pas de binaire `.fig`

| | |
|---|---|
| Gravité | ⚪ Information |
| État | ⏭️ Reporté (livrable papier) |
| Découvert le | 20/11/2026 |
| Description | `docs/02_ux_ui/prototype_figma.md` décrit la structure du fichier, les interactions et la checklist de livraison. Le fichier `.fig` binaire n'est pas versionné. |
| Impact | Le jury ne peut pas ouvrir Figma, mais le prototype cliquable HTML (12 écrans × 3 supports) remplit le même rôle de démonstration. |
| Action corrective | Exporter le `.fig` et le lien de partage au format PDF des écrans si le formateur le demande explicitement. |
| Responsable | UX/UI |
| Échéance | Validation 2 (20/12/2026) |

### A-09 — Warning PHP sur le total d'une ligne de panier ✅

| | |
|---|---|
| Gravité | 🔴 Bloquant (corrigé) |
| État | ✅ Corrigé — commit `7d74622` |
| Découvert le | 20/11/2026 (recette `composer test:all`) |
| Description | `app/Views/cart/index.php` lisait `$item['line_total_ttc']` sans vérifier sa présence, provoquant `Undefined array key` sur certaines lignes. |
| Impact | Warning affiché en sortie, polluant le HTML et risquant de rompre les en-têtes HTTP. |
| Correction | Expression remplacée par un `isset()` avec repli calculé `unit_price_ttc × quantity`. |
| Vérification | Recette E-commerce 15/15 et frontend 12/12, aucun warning. |

### A-10 — Faux négatif du test F05 (mot `innerHTML` dans un commentaire) ✅

| | |
|---|---|
| Gravité | 🔴 Bloquant (corrigé) |
| État | ✅ Corrigé — commit `7d74622` |
| Découvert le | 21/11/2026 (recette `composer test:frontend`) |
| Description | Le test F05 interdisait la chaîne littérale `innerHTML` dans `app.js`. Or le mot apparaissait uniquement dans un commentaire de documentation (« jamais d'innerHTML avec des données »), faisant échouer la recette à tort. |
| Impact | Recette frontend bloquée à 11 PASS / 1 FAIL alors que le code était conforme. |
| Correction | Le test détecte désormais une **affectation réelle** (`innerHTML =` / `innerHTML +=`) via expression régulière. |
| Vérification | `composer test:frontend` → 12 PASS / 0 FAIL. |

### A-11 — Erreur de parse `?>` dans un commentaire PHP ✅

| | |
|---|---|
| Gravité | 🔴 Bloquant (corrigé) |
| État | ✅ Corrigé — commit `7d74622` |
| Découvert le | 21/11/2026 |
| Description | Un commentaire de `tests/frontend.php` contenait littéralement `<?= … ?>`, ce qui fermait le bloc PHP et provoquait `Unclosed '{' on line 114`. |
| Impact | Aucun test frontend exécutable. |
| Correction | Commentaire réécrit sans séquence `?>`. |
| Vérification | `php -l` sur 64 fichiers : 0 erreur. |

### A-12 — Divergence des compteurs de routes dans la documentation ✅

| | |
|---|---|
| Gravité | 🟡 Mineur (corrigé) |
| État | ✅ Corrigé — commit `7d74622` |
| Découvert le | 21/11/2026 |
| Description | Après ajout de 3 routes (`/compte`, `GET|POST /vendeur/boutique`), plusieurs documents annonçaient encore 23 routes et 23 tests. |
| Impact | Incohérence entre le code (26 routes), le test T02 et la documentation. |
| Correction | README, `docs/05_backend/*`, `docs/06_ecommerce/*` et `tests/run.php` mis à jour vers 26 routes / 24 tests socle. |
| Vérification | T02 et T02b verts ; documentation alignée. |

---

## 4. Procédure de remontée (pendant la recette)

1. **Observer** : noter l'écran, l'action, le navigateur et la largeur (320 / 640 / 1024 px).
2. **Reproduire** : au moins 2 fois, avec le compte démo concerné.
3. **Qualifier** : gravité + état + composant (Vue / Contrôleur / Service / BDD).
4. **Documenter** : ajouter une ligne `A-xx` dans ce registre avec la trame ci-dessus.
5. **Corriger** : branche `fix/US-xxx-<court>` → PR vers `develop` (voir [git_workflow.md](git_workflow.md)).
6. **Clore** : joindre la preuve de test (sortie `composer test:*`) et passer l'état à ✅.

## 5. Comptes de démonstration utiles à la recette

| Rôle | Email | Mot de passe | Cas couverts |
|---|---|---|---|
| Admin | `ghazi.karoui@tunishop.tn` | `Test1234!` | Dashboard, validation vendeurs, factures |
| Vendeur | `fatma.bensalah@tunishop.tn` | `Test1234!` | Écran 8, stocks, commandes |
| Client | `aymen.benali@tunishop.tn` | `Test1234!` | Écran 7, commande, facture PDF |

Carte sandbox : `4242 4242 4242 4242` · Codes promo : `ARTISAN5`, `BIENVENUE10`.

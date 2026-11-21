# Plan SEO & acquisition locale — TuniShop

| Champ | Valeur |
|---|---|
| Version | 1.1 — 21/11/2026 (lot P0 implémenté) |
| Portée | SEO technique, on-page, contenu, référencement local Sidi Bou Saïd |
| Origine | FR-28 · NFR-05 · US-047 (2 pts, Sprint 8) · DEC-001 stack PHP/MVC |
| Responsable | Architecte BDD / Sécurité / SEO (rôle à confirmer — voir A-01) |
| Jalons | Préjury 10/01/2027 · Validation 3 24/01/2027 |

---

## 1. Objectifs

1. Être **trouvable** sur des requêtes locales et artisanales : « céramique Sidi Bou Saïd », « vase bleu et blanc Tunis », « artisanat tunisien fait main ».
2. Offrir aux moteurs un **contenu structuré et sans duplication** (title/description uniques, canonical, JSON-LD).
3. Servir un **sitemap** exact et un `robots.txt` contraignant l'indexation aux pages utiles.
4. Mesurer l'acquisition avec des **KPI** simples, exploitables au jury comme en production.

### 1.1 Hypothèse de départ

Le cahier des charges note l'absence de « référencement local structurant » chez la concurrence (§1.2). TuniShop peut occuper ce créneau avec des pages produits très descriptives, une identité géographique forte et des données structurées `Product`.

---

## 2. État des lieux (audit du 21/11/2026)

| # | Critère NFR-05 / US-047 | Implémentation actuelle | Verdict |
|---|---|---|---|
| 1 | URLs propres | Routeur frontal, `/produit/{slug}` avec slug issu du nom | ✅ Conforme |
| 2 | `<title>` unique par page | `app/Views/layouts/main.php` ligne 7, `$pageTitle` fourni par chaque contrôleur | ✅ Conforme |
| 3 | Meta description unique | idem ligne 8, `$pageDescription` fourni par les contrôleurs | ✅ Conforme |
| 4 | Hiérarchie Hn | Un `h1` par écran, `h2` de section, `h3` de carte produit | ✅ Conforme |
| 5 | Images avec `alt` | `alt_text` BDD quand le fichier existe ; sinon `role="img"` + `aria-label` | ✅ Conforme (fallback) |
| 6 | Attribut `lang` | `<html lang="fr">` | ✅ Conforme |
| 7 | Balise `canonical` | `Seo::canonical()` + `$canonicalPath` partagé par `Application` | ✅ Conforme (P0) |
| 8 | Données structurées Schema.org | `@graph` Organization/WebSite, Product, BreadcrumbList via `Seo::jsonLdTag()` | ✅ Conforme (P0) |
| 9 | `sitemap.xml` à la racine | `public/sitemap.xml` régénéré par `tools/generate_sitemap.php` (23 URL) | ✅ Conforme (P0) |
| 10 | `robots.txt` à la racine | `public/robots.txt` + `X-Robots-Tag` sur les routes privées | ✅ Conforme (P0) |
| 11 | Open Graph / Twitter Card | Absentes | ⏳ À faire (P1) |
| 12 | Suivi d'audience / KPI | Non équipé | ⏳ À faire (P1) |

**10 critères conformes / 12.** Le lot P0 (US-047) est livré : les 4 éléments « machine-readable » manquants sont en place et couverts par 12 tests automatisés (SEO-01 → SEO-12). Les 2 critères restants relèvent de P1 (déploiement et images).

### 2.1 Points de vigilance

- Les visuels produits ne sont **pas versionnés** (`Product::images()` pointe vers `uploads/products/...` absents du dépôt). Le contrôleur affiche une illustration textuelle de secours. Tant que les fichiers ne sont pas déposés, les moteurs ne disposent d'aucun `alt` d'image indexable. Voir anomalie A-03.
- `sitemap.xml` ne doit **pas** inclure les pages de compte, du panier, du checkout ni les back-offices (`noindex` global via `robots.txt` et `X-Robots-Tag` recommandé).

---

## 3. Stratégie

### 3.1 SEO technique (P0 — ✅ livré le 21/11/2026)

| Action | Détail | Fichier |
|---|---|---|
| Balise canonique | `<link rel="canonical">` absolu, chemin normalisé par la `Request`, sans query de filtre | `app/Core/Seo.php`, `app/Views/layouts/main.php` |
| `robots.txt` | `Allow: /`, `Disallow` sur panier/commande/compte/connexion/inscription/admin/vendeur/api/sante, `Sitemap:` absolu | `public/robots.txt` |
| `sitemap.xml` | 23 URL (accueil + catalogue + 21 produits), `<lastmod>` et `<changefreq>` ; régénération par `php tools/generate_sitemap.php` | `public/sitemap.xml`, `tools/generate_sitemap.php` |
| JSON-LD global | `@graph` `Organization` (Sidi Bou Saïd, TN) + `WebSite` + `SearchAction` | `app/Core/Seo.php`, layout |
| JSON-LD fiche produit | `Product` : `name`, `description`, `brand`, `offers` (prix `TND`, disponibilité), `image`, `aggregateRating` | `app/Views/catalog/show.php` |
| JSON-LD fil d'Ariane | `BreadcrumbList` avec positions 1..n, rendu dès que `$breadcrumb` est fourni | layout |
| `X-Robots-Tag` | `index, follow` sur le public ; `noindex, nofollow` sur les 9 préfixes privés | `Application::robotsDirective()`, `Response::withHeader()` |

> **Décision CSP retenue (DEC-011)** : le JSON-LD est un **data block** généré côté serveur dans `<script type="application/ld+json">`. Un **nonce aléatoire par requête** est généré par `Application`, injecté dans `script-src 'self' 'nonce-…'` et repris sur chaque bloc. Cela autorise exactement ces blocs sans jamais ajouter `'unsafe-inline'`, et Google lit le JSON-LD dans le HTML source sans JavaScript. Les valeurs sont échappées (`<` → `\\u003C`) pour empêcher toute fermeture anticipée du bloc. Aucun attribut `on*`, aucun JS inline. L'alternative « fichier `.jsonld` externe » initialement envisagée a été écartée : Google ne garantit pas l'exploration d'une référence `<link rel="alternate">` pour les rich results.

### 3.2 SEO on-page (P1)

- **Title** : modèle `« Nom produit — Atelier | TuniShop »`, 50–60 caractères, mot-clé au début.
- **Meta description** : 150–160 caractères, bénéfice + zone géographique + appel à l'action.
- **Contenu** : enrichir les descriptions produits (matière, dimensions, technique, atelier) — cible 300 mots minimum sur les 6 coups de cœur.
- **Maillage interne** : liens catalogue → fiche → boutique → produits similaires (déjà partiellement en place via `Coups de cœur`).
- **URLs** : conserver les slugs actuels, sans paramètres de filtre indexables (`/catalogue?category=…` en `noindex`).

### 3.3 Référencement local (P1)

- Pages `/catalogue` et `/` : mention explicite « Sidi Bou Saïd », « Tunis », « fait main ».
- Cohérence NAP (Name, Address, Phone) entre `shops` et les coordonnées affichées — déjà centralisée dans `SellerController::shopUpdate()`.
- Fiche `Organization` avec `@id` stable et adresse de l'atelier principal.
- À prévoir au déploiement : inscription Google Business Profile et Pages Jaunes Tunisie (hors périmètre code).

### 3.4 Acquisition & KPI (P2)

| KPI | Cible à 3 mois | Source |
|---|---|---|
| Impressions de recherche organique | > 2 000 / mois | Search Console |
| Position moyenne sur « artisanat Sidi Bou Saïd » | Top 20 | Search Console |
| CTR organique | > 2,5 % | Search Console |
| Sessions organiques | > 300 / mois | Analytics / Matomo |
| Taux de conversion organique | > 1,5 % | Suivi commandes `placed_at` |
| Pages indexées / pages attendues | 100 % | `site:tunishop.tn` + sitemap |

Outil recommandé : **Matomo auto-hébergé** (RGPD, pas de cookie tiers) plutôt que Google Analytics. À décider au déploiement (DEC à venir).

---

## 4. Plan d'action priorisé

| Priorité | Tâche | Charge | Échéance | Dépendance |
|---|---|---:|---|---|
| ✅ P0 | `robots.txt` + balise canonical | 0,5 j | **21/11 livré** | — |
| ✅ P0 | Sitemap généré depuis `v_catalogue` + script de régénération | 1 j | **21/11 livré** | BDD importée |
| ✅ P0 | JSON-LD `Organization` + `WebSite` (nonce CSP) | 1 j | **21/11 livré** | CSP validée |
| ✅ P0 | JSON-LD `Product` + `BreadcrumbList` | 1 j | **21/11 livré** | fiche produit |
| ✅ P0 | `X-Robots-Tag` sur routes privées | 0,5 j | **21/11 livré** | `Response` |
| P1 | Open Graph / Twitter Card | 0,5 j | 08/01 | images A-03 |
| P1 | Enrichissement 6 descriptions produits | 1 j | 09/01 | vendeur |
| P1 | Matomo + tableau de bord KPI | 1 j | 10/01 | déploiement |
| P2 | Google Business Profile | 0,5 j | 17/01 | coordonnées boutique |
| P2 | Campagne réseaux sociaux + newsletter | 1 j | 24/01 | contenu |

**Total : 8 jours** — compatible avec les 30 points du Sprint 5. **Les 4 jalons P0 (4 jours) sont livrés au 21/11/2026**, soit avant le préjury. Restent 4 jours de P1/P2.

---

## 5. Recette SEO

### 5.1 Recette automatisée — `composer test:seo`

`tests/seo.php` contient **12 tests SEO-01 → SEO-12**, tous verts :

| Test | Contrôle |
|---|---|
| SEO-01 | `site_url` absolu configuré |
| SEO-02 | canonical unique, absolu, sans paramètre de filtre |
| SEO-03 | `robots.txt` : `Allow` public + `Disallow` privés + `Sitemap:` |
| SEO-04 | `sitemap.xml` XML valide, 23 URL dont les 21 produits |
| SEO-05 | CSP `script-src 'self'` + nonce, sans `'unsafe-inline'` |
| SEO-06 | Blocs JSON-LD rendus avec le nonce de la réponse |
| SEO-07 | `@graph` Organization + WebSite + SearchAction |
| SEO-08 | BreadcrumbList avec positions et dernier élément sans `item` |
| SEO-09 | Product / Offer en `TND`, prix et disponibilité |
| SEO-10 | `X-Robots-Tag` `noindex` privé / `index` public |
| SEO-11 | Échappement `</script>` dans les données JSON-LD |
| SEO-12 | Aucun script inline exécutable dans le HTML rendu |

```bash
composer test:seo                       # 12 PASS / 0 FAIL
composer test:all                       # 106 PASS / 0 FAIL (24 + 15 + 18 + 12 + 12)
mysql -u root -e "SOURCE database/tests.sql"  # 25 PASS / 0 FAIL
php tools/generate_sitemap.php
```

### 5.2 Vérifications manuelles

1. `curl http://localhost:8000/robots.txt` → règles `Disallow` présentes.
2. `curl http://localhost:8000/sitemap.xml` → XML valide, une `<url>` par produit publié.
3. Sur `/produit/vase-bleu-et-blanc` : title unique, canonical absolu, un seul `h1`, JSON-LD valide dans [Rich Results Test](https://search.google.com/test/rich-results).
4. Vue « source » : seuls des `<script type="application/ld+json" nonce="…">` (data blocks) et le `<script src… defer>` du JS externe — aucun script exécutable inline (`script-src 'self'` respecté).
5. `curl -I http://localhost:8000/compte` → `X-Robots-Tag: noindex, nofollow`.
6. Validator W3C Nu : aucune erreur de structure.

---

## 6. Risques

| ID | Risque | Mitigation |
|---|---|---|
| SEO-01 | JSON-LD inline casse la CSP | ✅ **Mitigé** : nonce par requête dans `script-src`, aucun `'unsafe-inline'` (SEO-05/SEO-06) |
| SEO-02 | Sitemap obsolète après ajout produit | Script `php tools/generate_sitemap.php` à rejouer après publication ; 23 URL générées depuis `v_catalogue` |
| SEO-03 | Duplication de contenu via filtres | canonical vers `/catalogue` + `noindex` des variantes |
| SEO-04 | Images absentes → pas d'alt indexable | A-03 : upload sécurisé à prévoir en V1.1 |

---

## 7. Liens utiles

- [Matrice des 12 écrans](../03_frontend/matrice_ecrans.md)
- [Accessibilité & responsive](../03_frontend/accessibilite_responsive.md)
- [Comportements JS / CSP](../03_frontend/comportements_js.md)
- [Liste des anomalies](../01_gestion_projet/liste_anomalies.md) (A-03, A-05)

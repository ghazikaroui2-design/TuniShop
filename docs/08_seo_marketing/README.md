# TuniShop — Livrables Marketing du Web / SEO (matière E09)

| Champ | Valeur |
|---|---|
| **Matière** | Marketing du Web — SEO on-page / technique, acquisition locale, KPI |
| **Période** | Sprint 8 → Sprint 5 (anticipation), préjury 10/01/2027 |
| **Référentiel** | FR-28, NFR-05 du cahier des charges · US-047 du backlog |
| **Zone cible** | Sidi Bou Saïd, Tunis — requêtes géolocalisées et artisanales |
| **Références** | [Cahier des charges](../01_gestion_projet/cahier_des_charges.md) · [Backlog](../01_gestion_projet/backlog_produit.md) · [Frontend](../03_frontend/README.md) |

---

## 1. Fichiers livrés

| Document | Contenu |
|---|---|
| [plan_seo.md](plan_seo.md) | État des lieux, stratégie, plan d'action priorisé, KPI et recette SEO |

> Ce dossier complète la **8ᵉ matière** exigée à la Validation 3 (documentation des 8 matières). Il correspond au dossier `08_seo_marketing/` annoncé dans l'arborescence du README.

## 2. État des lieux express

| Volet | État | Preuve |
|---|---|---|
| URLs propres | ✅ | `/produit/{slug}`, `/catalogue`, `/compte` — 26 routes nommées |
| `<title>` unique | ✅ | `app/Views/layouts/main.php` alimenté par `$pageTitle` |
| Meta description | ✅ | idem, alimentée par `$pageDescription` |
| Hiérarchie Hn | ✅ | un seul `h1` par écran, `h2`/`h3` dans les sections |
| Attributs `alt` | ✅ | `alt_text` de `product_images`, sinon `role="img"` + `aria-label` |
| `lang="fr"` | ✅ | `<html lang="fr">` dans le layout |
| Balise `canonical` | ✅ | `Seo::canonical()` dans le layout, URL absolue sans filtre |
| JSON-LD Schema.org | ✅ | `Organization`, `WebSite`, `Product`, `BreadcrumbList` avec nonce CSP |
| `robots.txt` | ✅ | `public/robots.txt` + `X-Robots-Tag` sur 9 préfixes privés |
| `sitemap.xml` | ✅ | `public/sitemap.xml` (23 URL), régénéré par `tools/generate_sitemap.php` |
| Open Graph / Twitter | ⏳ | Balises de partage social (P1) |
| Suivi d'audience | ⏳ | KPI définis, outil à choisir au déploiement (P1) |

Détail et plan d'action : [plan_seo.md](plan_seo.md).

## 3. Situation au 21/11/2026

- ✅ Socle SEO « technique de base » livré avec l'intégration frontend (commit `7d74622`).
- ✅ **US-047 terminée (lot P0)** : canonical, JSON-LD, sitemap et robots livrés et couverts par 12 tests (`composer test:seo`). Voir anomalie A-05 clôturée.
- ⏳ Open Graph et KPI de lancement (P1) à mettre en place au déploiement.

> ✅ SEO et CSP : le JSON-LD est généré **côté serveur** dans un data block `<script type="application/ld+json">` portant un **nonce par requête** (`script-src 'self' 'nonce-…'`). Aucun `'unsafe-inline'`, aucun JavaScript d'injection. Voir DEC-011 dans [plan_seo.md](plan_seo.md) §3.1.

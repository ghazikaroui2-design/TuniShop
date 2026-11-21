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
| Balise `canonical` | ⏳ | À ajouter dans le layout (P0) |
| JSON-LD Schema.org | ⏳ | `Organization`, `WebSite`, `Product`, `BreadcrumbList` (P0) |
| `robots.txt` | ⏳ | À déposer dans `public/` (P0) |
| `sitemap.xml` | ⏳ | À générer depuis `v_catalogue` (P0) |
| Open Graph / Twitter | ⏳ | Balises de partage social (P1) |
| Suivi d'audience | ⏳ | KPI définis, outil à choisir au déploiement (P1) |

Détail et plan d'action : [plan_seo.md](plan_seo.md).

## 3. Situation au 21/11/2026

- ✅ Socle SEO « technique de base » livré avec l'intégration frontend (commit `7d74622`).
- 🟡 US-047 partiellement couverte : il reste canonical, JSON-LD, sitemap et robots à finaliser.
- ⏳ Analyse de positionnement et KPI de lancement à mettre en place au déploiement.

> ⚠️ SEO et CSP : toute balise JSON-LD doit être insérée côté **serveur** dans le layout PHP. L'ajouter en JavaScript violerait `script-src 'self'` (aucun `<script>` inline autorisé). Voir [comportements JS](../03_frontend/comportements_js.md).

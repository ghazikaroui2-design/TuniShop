# 🏺 TuniShop — Marketplace locale de l'artisanat tunisien

> Projet pluridisciplinaire BTS Développement sur Internet – 2ᵉ année (BTS DI 2-1)
> Département Infographie & Multimédia — **16/11/2026 → 25/01/2027**

TuniShop est une marketplace e-commerce dédiée aux **artisans et commerçants de Sidi Bou Saïd**. Elle permet aux visiteurs de découvrir et d'acheter des produits locaux (céramique, tapis, parfumerie, bijoux, décoration…), et aux vendeurs de gérer leur boutique, leurs stocks et leurs commandes.

---

## 📁 Arborescence du dépôt

```
TuniShop/
├── docs/
│   ├── 01_gestion_projet/   ← cahier des charges, backlog, Gantt, suivi Agile, Git
│   ├── 02_ux_ui/            ← personas, user flows, wireframes, prototype Figma
│   ├── 03_frontend/         ← pages HTML5/CSS3/JS responsives
│   ├── 04_bdd/              ← dictionnaire, MCD/MLD, scripts SQL
│   ├── 05_backend/          ← UML, architecture MVC, CRUD (POO avancée)
│   ├── 06_ecommerce/        ← panier, checkout, paiement sandbox, factures PDF
│   ├── 07_securite/         ← authentification, RBAC, audit OWASP
│   ├── 08_seo_marketing/    ← SEO, sitemap, plan de lancement digital
│   └── 09_soutenance/       ← support de présentation, démo, manuel utilisateur
├── public/                  ← point d'entrée web, assets, uploads, sitemap.xml, robots.txt
├── app/                     ←Controllers / Models / Services / Middlewares / Views / Core
├── config/                  ← configuration (BDD, constantes, routing)
├── database/                ← schema.sql, seed.sql, triggers.sql, procedures.sql
├── tests/                   ← tests fonctionnels et de sécurité
├── .gitignore
└── README.md
```

> ⚠️ Les dossiers techniques seront créés au fur et à mesure des sprints. Le contenu actuel couvre la **gestion de projet (S1)** et le **design UX/UI (S2–S3)**.

---

## 🚀 Démarrage rapide (prévisionnel)

| Élément | Choix retenu |
|---|---|
| Back-end | **PHP 8.3** (architecture MVC maison, namespaces PSR-4) |
| Base de données | **MySQL 8.0 / InnoDB**, accès **PDO** (requêtes préparées) |
| Front-end | HTML5 sémantique, CSS3 (Grid/Flexbox, media queries), JavaScript ES6+ (`fetch`) |
| Serveur local | XAMPP (Apache + MySQL) |
| Sécurité | `password_hash()` Argon2id/bcrypt, sessions PHP, jetons CSRF, RBAC |
| PDF | DomPDF ou TCPDF (via Composer) |
| Versioning | Git — branches `feature/*`, `fix/*`, `docs/*` |

**Environnement cible :** `http://localhost/tunishop/public/`

> Git et Composer ne sont pas encore installés sur la machine de développement (état au 20/11/2026) : voir `docs/01_gestion_projet/git_workflow.md` pour l'installation et les conventions.

---

## 📚 Livrables de gestion de projet (disponibles maintenant)

| Document | Contenu |
|---|---|
| [Cahier des charges](docs/01_gestion_projet/cahier_des_charges.md) | Contexte, périmètre, besoins fonctionnels/non fonctionnels, règles métier, architecture, risques |
| [Backlog produit](docs/01_gestion_projet/backlog_produit.md) | 58 user stories priorisées, estimées et réparties sur 10 sprints |
| [Planning Gantt](docs/01_gestion_projet/planning_gantt.md) | Tableau des lots, jalons et chemin critique |
| [Diagramme de Gantt visuel](docs/01_gestion_projet/gantt.html) | Vue graphique imprimable (ouvrir dans le navigateur) |
| [Suivi des sprints](docs/01_gestion_projet/suivi_sprints.md) | Objectifs, vélocité, burndown, rituels, registre des risques |
| [Workflow Git](docs/01_gestion_projet/git_workflow.md) | Branches, conventions de commit, tags, procédure PR |

### Livrables UX/UI (en cours — jalon 29/11)

| Document | Contenu |
|---|---|
| [Personas](docs/02_ux_ui/personas.md) | Aymen (client), Fatma (artisane), Karim (admin) + touriste, scénarios d'usage, matrice de synthèse |
| [User flows](docs/02_ux_ui/user_flows.md) | 7 parcours en diagrammes Mermaid (achat, recherche, validation vendeur, paiement, avis…) |
| [Wireframes](docs/02_ux_ui/wireframes.md) | 6 wireframes détaillés + inventaire des 12 écrans + variantes mobiles |
| [Prototype cliquable](docs/02_ux_ui/prototype_tunishop.html) | **12 écrans interactifs**, bascule Wireframe ↔ Hi-Fi, vues Desktop/Tablette/Mobile 🎨 |
| [UI kit](docs/02_ux_ui/ui_kit.md) | Palette « Bleu de Sidi Bou », typographie, composants, accessibilité WCAG AA |
| [Plan Figma](docs/02_ux_ui/prototype_figma.md) | Structure du fichier .fig, interactions, variantes, checklist de livraison |

---

## 🎯 Jalons

| Jalon | Date | Attendu |
|---|---|---|
| Validation 1 | 29/11/2026 | Cahier des charges, Gantt, backlog, wireframes |
| Validation 2 | 20/12/2026 | Interfaces responsives, prototype Figma, base SQL opérationnelle |
| Préjury | 10/01/2027 | Démonstration fonctionnelle complète |
| Validation 3 | 24/01/2027 | Dépôt Git + dossier numérique complets |
| **Jury final** | **25/01/2027** | Soutenance et démonstration en direct |

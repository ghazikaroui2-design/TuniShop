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
│   ├── 05_backend/          ← UML, architecture MVC, POO/SOLID (livré ✅)
│   ├── 06_ecommerce/        ← panier, checkout, paiement sandbox, factures PDF
│   ├── 07_securite/         ← authentification, RBAC, audit OWASP
│   ├── 08_seo_marketing/    ← SEO, sitemap, plan de lancement digital
│   └── 09_soutenance/       ← support de présentation, démo, manuel utilisateur
├── public/                  ← point d'entrée web, assets, uploads, sitemap.xml, robots.txt
├── app/                     ← Controllers / Models / Services / Middlewares / Views / Core
├── config/                  ← configuration (BDD, constantes, routing)
├── database/                ← schema.sql, seed.sql, triggers.sql, procedures.sql
├── tests/                   ← tests fonctionnels et de sécurité
├── .gitignore
└── README.md
```

> ⚠️ Les dossiers techniques seront créés au fur et à mesure des sprints. Le contenu actuel couvre la **gestion de projet (S1)**, le **design UX/UI (S2–S3)**, la **base de données (S4–S5)** et le **socle POO/MVC/UML (S5)**.

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

> ✅ Environnement vérifié le 20/11/2026 : **Git 2.56.1**, **PHP 8.3.36 (XAMPP)** et **Composer 2.11.2** installés. Dépôt initialisé (`main` + `develop`). Détails et commandes de vérification : `docs/01_gestion_projet/git_workflow.md`.

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

### Livrables UX/UI (disponibles — jalon 29/11)

| Document | Contenu |
|---|---|
| [Personas](docs/02_ux_ui/personas.md) | Aymen (client), Fatma (artisane), Karim (admin) + touriste, scénarios d'usage, matrice de synthèse |
| [User flows](docs/02_ux_ui/user_flows.md) | 7 parcours en diagrammes Mermaid (achat, recherche, validation vendeur, paiement, avis…) |
| [Wireframes](docs/02_ux_ui/wireframes.md) | 6 wireframes détaillés + inventaire des 12 écrans + variantes mobiles |
| [Prototype cliquable](docs/02_ux_ui/prototype_tunishop.html) | **12 écrans interactifs**, bascule Wireframe ↔ Hi-Fi, vues Desktop/Tablette/Mobile 🎨 |
| [UI kit](docs/02_ux_ui/ui_kit.md) | Palette « Bleu de Sidi Bou », typographie, composants, accessibilité WCAG AA |
| [Plan Figma](docs/02_ux_ui/prototype_figma.md) | Structure du fichier .fig, interactions, variantes, checklist de livraison |

### Livrables Base de données (disponibles — jalon 20/12)

| Document | Contenu |
|---|---|
| [Synthèse BDD](docs/04_bdd/README.md) | Périmètre, couverture RG-01 → RG-11, correspondance écrans, convention de nommage |
| [Dictionnaire des données](docs/04_bdd/dictionnaire_donnees.md) | 20 tables, champs, types, contraintes, index, domaines et formats |
| [MCD / MLD](docs/04_bdd/mcd_mld.md) | Modèle conceptuel Merise, cardinalités, passage relationnel, normalisation 3NF |
| [Schéma SQL](database/schema.sql) | DDL exécutable : 20 tables InnoDB, contraintes, index FULLTEXT, 6 vues |
| [Procédures](database/procedures.sql) | 7 fonctions + 13 procédures : checkout transactionnel, paiement sandbox, stats |
| [Triggers](database/triggers.sql) | 11 triggers : stock, TVA, facturation, statuts, avis, audit |
| [Jeu d'essai](database/seed.sql) | 24 produits, 8 boutiques, 10 commandes, 19 comptes (mot de passe `Test1234!`) |
| [Tests SQL](database/tests.sql) | **25 scénarios PASS / 0 FAIL** — règles métier et garde-fous vérifiés |
| [Guide d'import](database/README.md) | Ordre d'import, comptes de démo, sauvegarde/restauration, dépannage PDO |

> ✅ Base de données importée et validée localement sur MariaDB 10.11 (XAMPP) — cible documentée MySQL 8.0 / InnoDB.

### Livrables POO / MVC / UML (disponibles — socle S5)

| Document | Contenu |
|---|---|
| [Synthèse backend](docs/05_backend/README.md) | Périmètre, fichiers livrés, validation, couverture RG-01 → RG-11, choix techniques |
| [Architecture MVC](docs/05_backend/architecture_mvc.md) | Routeur frontal, composants Core, 22 routes, middlewares, modèles, services, sécurité |
| [Diagrammes UML](docs/05_backend/diagrammes_uml.md) | Cas d'utilisation, classes, 4 séquences, composants, états commande/boutique (Mermaid) |
| [Concepts POO / SOLID](docs/05_backend/poo_concepts.md) | 4 piliers, SOLID, 10 patterns, PHP 8.3, démonstration soutenance |
| [Socle applicatif](public/index.php) | Point d'entrée HTTP — `php -S localhost:8000 -t public` |
| [Tests de recette](tests/run.php) | **23 tests PASS / 0 FAIL** — `composer test` |

> ✅ Socle MVC exécutable : autoload PSR-4, PHP linté, 23 tests verts. Couche Services branchée sur les procédures stockées de la BDD (panier, commande, paiement sandbox, audit).

---

## 🎯 Jalons

| Jalon | Date | Attendu |
|---|---|---|
| Validation 1 | 29/11/2026 | Cahier des charges, Gantt, backlog, wireframes |
| Validation 2 | 20/12/2026 | Interfaces responsives, prototype Figma, base SQL opérationnelle |
| Préjury | 10/01/2027 | Démonstration fonctionnelle complète |
| Validation 3 | 24/01/2027 | Dépôt Git + dossier numérique complets |
| **Jury final** | **25/01/2027** | Soutenance et démonstration en direct |

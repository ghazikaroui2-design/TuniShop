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
│   ├── 06_ecommerce/        ← panier, checkout, paiement sandbox, factures PDF (livré ✅)
│   ├── 07_securite/         ← authentification, RBAC, audit OWASP (livré ✅)
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

> ✅ Les **9 dossiers `docs/`** sont livrés : 8 matières (gestion de projet, UX/UI, responsive, BDD, POO/UML, e-commerce, sécurité, SEO) + dossier de soutenance. Les modules techniques des sprints 1 à 4 ont été livrés par anticipation au 21/11/2026. Voir [suivi des sprints](docs/01_gestion_projet/suivi_sprints.md) et [liste des anomalies](docs/01_gestion_projet/liste_anomalies.md).

---

## 🚀 Démarrage rapide (prévisionnel)

| Élément | Choix retenu |
|---|---|
| Back-end | **PHP 8.3** (architecture MVC maison, namespaces PSR-4) |
| Base de données | **MySQL 8.0 / InnoDB**, accès **PDO** (requêtes préparées) |
| Front-end | HTML5 sémantique, CSS3 (Grid/Flexbox, media queries), JavaScript ES6+ (`fetch`) |
| Serveur local | XAMPP (Apache + MySQL) |
| Sécurité | `password_hash()` Argon2id/bcrypt, sessions PHP, jetons CSRF, RBAC |
| PDF | **DomPDF 3.1.6** via Composer (`composer.lock` versionné) |
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
| [Suivi des sprints](docs/01_gestion_projet/suivi_sprints.md) | Objectifs, vélocité, burndown, état réel au 21/11, rituels, risques |
| [Liste des anomalies](docs/01_gestion_projet/liste_anomalies.md) | 12 anomalies priorisées — Préjury 10/01 (0 bloquante ouverte) |
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
| [Architecture MVC](docs/05_backend/architecture_mvc.md) | Routeur frontal, composants Core, 26 routes, middlewares, modèles, services, sécurité |
| [Diagrammes UML](docs/05_backend/diagrammes_uml.md) | Cas d'utilisation, classes, 4 séquences, composants, états commande/boutique (Mermaid) |
| [Concepts POO / SOLID](docs/05_backend/poo_concepts.md) | 4 piliers, SOLID, 10 patterns, PHP 8.3, démonstration soutenance |
| [Socle applicatif](public/index.php) | Point d'entrée HTTP — `php -S localhost:8000 -t public` |
| [Tests de recette](tests/run.php) | **24 tests PASS / 0 FAIL** — `composer test` |

> ✅ Socle MVC exécutable : autoload PSR-4, PHP linté, 24 tests verts. Couche Services branchée sur les procédures stockées de la BDD (panier, commande, paiement sandbox, audit).

### Livrables E-commerce (disponibles — jalon 10/01)

| Document | Contenu |
|---|---|
| [Synthèse E-commerce](docs/06_ecommerce/README.md) | Chaîne transactionnelle complète, couverture RG-01 → RG-11, validation et démarrage local |
| [Parcours client](docs/06_ecommerce/parcours_client.md) | Panier persistant, quantités, checkout 3 étapes, adresses, codes promo |
| [Paiement sandbox](docs/06_ecommerce/paiement_sandbox.md) | Carte `4242 4242 4242 4242`, RG-07, absence totale de données bancaires |
| [Facturation PDF](docs/06_ecommerce/facturation_pdf.md) | Numérotation `F-YYYY-NNNN`, DomPDF 3.1.6, gabarit A4, téléchargement sécurisé |
| [Recette E-commerce](docs/06_ecommerce/tests_ecommerce.md) | 15 scénarios E01–E15 et résultats |
| [Tests](tests/ecommerce.php) | **15 tests PASS / 0 FAIL** — `composer test:ecommerce` |

> ✅ Module transactionnel livré : panier persistant, checkout en 3 étapes, code promo, paiement simulé sans donnée bancaire, facture PDF téléchargeable par le client propriétaire ou l'administrateur.

### Livrables Sécurité / OWASP (disponibles — jalon 24/01)

| Document | Contenu |
|---|---|
| [Synthèse sécurité](docs/07_securite/README.md) | Modèle de menace, contrôles, résultat des tests, état d'avancement |
| [Authentification](docs/07_securite/authentification.md) | Argon2id, politique de mot de passe, verrou anti-force-brute, anti-énumération, cookies |
| [RBAC](docs/07_securite/rbac.md) | 4 rôles, matrice des routes, middlewares, protection contre l'IDOR |
| [OWASP Top 10](docs/07_securite/owasp_top10.md) | Les 10 catégories 2021, contrôles TuniShop et preuves de test |
| [Checklist sécurité](docs/07_securite/checklist_securite.md) | Audit NFR-01 case par case, règles d'or du dépôt, plan de remédiation |
| [Recette sécurité](docs/07_securite/tests_securite.md) | 18 scénarios S01–S18 et résultats |
| [Tests](tests/security.php) | **18 tests PASS / 0 FAIL** — `composer test:security` |

> ✅ Sécurité durcie : en-têtes de sécurité globaux (CSP, nosniff, anti-clickjacking), Argon2id, 5 tentatives max, RBAC côté serveur, audit des refus.

### Livrables Frontend (disponibles — jalon 20/11)

| Document | Contenu |
|---|---|
| [Synthèse frontend](docs/03_frontend/README.md) | Design system, matrice des 12 écrans, responsive, accessibilité, CSP |
| [Matrice des écrans](docs/03_frontend/matrice_ecrans.md) | Prototype → vue MVC → route → contrôleur, pour les 12 écrans |
| [Accessibilité & responsive](docs/03_frontend/accessibilite_responsive.md) | WCAG 2.1 AA, 320/640/1024 px, focus, lecteurs d'écran |
| [Comportements JS](docs/03_frontend/comportements_js.md) | Hooks `data-*`, navigation, filtres, toasts, onglets, carte sandbox |
| [Recette frontend](docs/03_frontend/recette_frontend.md) | 12 scénarios F01–F12 et résultats |
| [Tests](tests/frontend.php) | **12 tests PASS / 0 FAIL** — `composer test:frontend` |

> ✅ Intégration frontend livrée : design system « Bleu de Sidi Bou », 12 écrans câblés sur le MVC, JS externe conforme CSP, responsive 320/640/1024 px+ et accessibilité WCAG 2.1 AA. **94 tests au vert** au total (24 MVC + 15 E-commerce + 18 Sécurité + 12 Frontend + 25 SQL).

### Livrables Marketing du Web / SEO (disponibles — 8ᵉ matière)

| Document | Contenu |
|---|---|
| [Synthèse SEO](docs/08_seo_marketing/README.md) | État des lieux (6 critères/12 conformes), 8ᵉ matière, liens avec NFR-05 |
| [Plan SEO](docs/08_seo_marketing/plan_seo.md) | Stratégie locale Sidi Bou Saïd, plan P0/P1/P2, KPI, recette, contrainte CSP |

> 🟡 Socle indexable en place (URLs propres, title/description uniques, Hn, alt, lang). Restent à finaliser : canonical, JSON-LD, `sitemap.xml`, `robots.txt` — voir anomalie A-05.

### Livrables Soutenance (disponibles — dossier 9)

| Document | Contenu |
|---|---|
| [Synthèse soutenance](docs/09_soutenance/README.md) | Chiffres clés, 9 dossiers, règles d'or de la présentation |
| [Support de soutenance](docs/09_soutenance/support_soutenance.md) | 13 slides, messages clés, démo scriptée client/vendeur/admin, plan B matériel |
| [Questions de jury](docs/09_soutenance/questions_jury.md) | 25 questions classées par thème, avec réponses et preuves documentées |

> ✅ Dossier de jury prêt : démonstration en 10 minutes, 94 tests affichables en sauvegarde et réponses argumentées aux questions techniques.

---

## 🎯 Jalons

| Jalon | Date | Attendu |
|---|---|---|
| Validation 1 | 29/11/2026 | Cahier des charges, Gantt, backlog, wireframes |
| Validation 2 | 20/12/2026 | Interfaces responsives, prototype Figma, base SQL opérationnelle |
| Préjury | 10/01/2027 | Démonstration fonctionnelle complète |
| Validation 3 | 24/01/2027 | Dépôt Git + dossier numérique complets |
| **Jury final** | **25/01/2027** | Soutenance et démonstration en direct |

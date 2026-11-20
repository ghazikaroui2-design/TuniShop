# Cahier des charges fonctionnel et technique — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| **Projet** | Conception et développement de la marketplace locale « TuniShop » |
| **Classe** | BTS Développement sur Internet – 2ᵉ année (BTS DI 2-1) |
| **Département** | Infographie & Multimédia / Technologies de l'Informatique |
| **Version** | 1.0 |
| **Date** | 20 novembre 2026 |
| **Statut** | En cours de validation — Jalon 1 (29/11/2026) |
| **Période** | 16/11/2026 → 24/01/2027 (10 semaines) + jury le 25/01/2027 |

## 0. Historique des versions

| Version | Date | Auteur | Modification |
|---|---|---|---|
| 1.0 | 20/11/2026 | Équipe projet (S1) | Rédaction initiale à partir du cahier des charges pédagogique |

---

## 1. Contexte et problématique

Sidi Bou Saïd, village perché du Golfe de Tunis classé parmi les plus beaux villages de Tunisie, est un haut lieu de l'artisanat tunisien : céramique bleue et blanche, tapis, broderies, parfumerie, bijoux, calligraphie et objets de décoration. Les artisans et petits commerçants locaux y sont souvent **cloisonnés dans une vitrine physique** et dépendent fortement du flux touristique saisonnier.

**Constat :**

- Absence de vitrine numérique mutualisée et de vente en ligne pour la majorité des artisans ;
- Visibilité réduite hors de la zone géographique (aucun référencement local structuring) ;
- Gestion manuelle des commandes, des stocks et des factures (perte de temps, erreurs) ;
- Aucun moyen simple pour le visiteur de comparer les produits, de payer et de suivre sa commande ;
- Méfiance envers les marketplaces internationales, perte de la valeur « fait main local ».

**Problématique :** *Comment digitaliser et valoriser le commerce artisanal de Sidi Bou Saïd grâce à une marketplace locale sécurisée, responsive et évolutive, tout en offrant aux artisans un outil de gestion simple et aux clients un parcours d'achat fluide ?*

**Réponse apportée :** la plateforme web **TuniShop**, marketplace à trois espaces (client, vendeur, administrateur), avec catalogue recherchable, panier persistant, tunnel de commande, paiement simulé en mode sandbox, facturation PDF, suivi des commandes, avis clients et tableau de bord analytique.

---

## 2. Objectifs du projet

### 2.1 Objectifs métier

1. Offrir aux artisans et commerçants de Sidi Bou Saïd une **vitrine numérique mutualisée** et un back-office de gestion léger.
2. Permettre aux clients de **découvrir, comparer, commander et suivre** des produits locaux en quelques clics, sur tout type d'écran.
3. Créer de la **confiance** : profils vendeurs validés, avis modérés, paiement sécurisé (simulé), factures générées automatiquement.
4. Poser les bases d'une **stratégie d'acquisition digitale locale** (SEO, réseaux sociaux, newsletter).

### 2.2 Objectifs pédagogiques (transversalité des 8 matières)

| Matière | Objectif atteint dans le projet |
|---|---|
| Gestion de projet | Cadrage, Agile/Scrum, backlog, Gantt, suivi des jalons, Git |
| Design UX/UI | Personas, user flows, wireframes, design system, prototype Figma |
| Sites web responsives | Intégration HTML5/CSS3/JS, 3 breakpoints, accessibilité |
| Bases de données | Modélisation MCD/MLD, SQL avancé, index, vues, triggers, procédures |
| POO avancée | UML, MVC, héritage/interfaces/namespaces, exceptions, services métier |
| Commerce électronique | Panier, tunnel de commande, sandbox, stocks, factures PDF |
| Sécurité | Authentification, hachage, RBAC, OWASP (SQLi/XSS/CSRF), audit |
| Marketing du Web | SEO on-page/technique, sitemap, plan de lancement, KPI |

---

## 3. Périmètre du projet

### 3.1 Fonctionnalités incluses (V1)

- **Espace client (front-office)** : navigation par catégories, recherche multicritères, fiches produits détaillées, panier persistant, tunnel de commande, simulation de paiement, suivi des commandes, dépôt d'avis, espace personnel.
- **Espace vendeur/artisan** : création et gestion de la boutique, CRUD produits, gestion des stocks, traitement des commandes reçues, tableau de bord des ventes.
- **Espace administrateur** : validation des comptes vendeurs, gestion des utilisateurs/catégories/commandes/avis, tableau de bord global (CA, commandes, stocks, vendeurs).
- **Transverse** : authentification sécurisée, RBAC, protection OWASP, responsive, SEO, factures PDF, jeu de données de démonstration.

### 3.2 Hors périmètre (V1)

- Paiement bancaire réel et encaissement (uniquement **simulation sandbox**) ;
- Gestion logistique réelle avec transporteurs (statuts de livraison simulés par l'admin/vendeur) ;
- Application mobile native (le site web est responsive) ;
- Marketplace multi-pays / multi-devises (devise unique : **TND/DT**) ;
- Retours, remboursements automatisés et litiges complexes (statut « Annulée » manuel) ;
- Chat temps réel, messagerie vendeur-client, système de fidélité avancé ;
- Hébergement de production à grande échelle (démo locale XAMPP acceptable pour le jury).

---

## 4. Acteurs et rôles (RBAC)

| Rôle | Description | Permissions principales |
|---|---|---|
| **Visiteur** | Utilisateur non connecté | Parcourir le catalogue, recherche, fiches produits, ajouter au panier, s'inscrire |
| **Client** | Compte authentifié ayant commandé | Passer commande, payer (simulation), suivre ses commandes, déposer un avis, gérer son profil |
| **Vendeur / Artisan** | Compte validé par l'administrateur | Gérer sa boutique, ses produits, ses stocks, traiter ses commandes, consulter son tableau de bord |
| **Administrateur** | Gestionnaire de la plateforme | Tout : valider les vendeurs, gérer utilisateurs/catégories/commandes/avis, consulter les statistiques globales, modifier les statuts |

> Règle : un compte vendeur reste **inactif** tant que l'administrateur ne l'a pas validé ; sa boutique n'est pas visible publiquement avant validation.

---

## 5. Besoins fonctionnels

Priorités : **M** = Must (indispensable), **S** = Should (important), **C** = Could (optionnel).

| ID | Besoin | Acteur | Prio. | Sprint | US liée |
|---|---|---|---|---|---|
| FR-01 | Créer un compte client avec validation d'email | Visiteur | M | S7 | US-040 |
| FR-02 | Se connecter / se déconnecter (session sécurisée) | Client/Vendeur/Admin | M | S7 | US-040 |
| FR-03 | Réinitialiser un mot de passe oublié | Tous | S | S9 | US-053 |
| FR-04 | Consulter et modifier son profil | Client/Vendeur | S | S7 | US-040 |
| FR-05 | Naviguer dans le catalogue par catégories | Tous | M | S4 | US-022 |
| FR-06 | Rechercher avec filtres (texte, catégorie, prix, localisation) | Tous | M | S6 | US-033 |
| FR-07 | Consulter une fiche produit (photos, prix TTC, stock, artisan) | Tous | M | S4 | US-021 |
| FR-08 | Gérer un panier persistant (ajout, quantité, suppression) | Visiteur/Client | M | S7 | US-035 |
| FR-09 | Calculer sous-total, TVA, frais de livraison et total | Tous | M | S7 | US-036 |
| FR-10 | Appliquer un code promo | Client | C | S10 | US-054 |
| FR-11 | Passer commande en 3 étapes (panier → livraison → paiement) | Client | M | S7 | US-037 |
| FR-12 | Simuler un paiement par carte en sandbox | Client | M | S7 | US-038 |
| FR-13 | Recevoir une confirmation de commande avec numéro unique | Client | M | S7 | US-037 |
| FR-14 | Suivre le statut de ses commandes | Client | M | S8 | US-043 |
| FR-15 | Déposer un avis (note 1–5 + commentaire) après achat | Client | M | S8 | US-045 |
| FR-16 | Créer et modifier sa boutique (nom, description, logo, contact) | Vendeur | M | S6 | US-030 |
| FR-17 | Valider ou refuser un compte vendeur | Admin | M | S8 | US-041 |
| FR-18 | CRUD produits (créer, lire, modifier, supprimer) | Vendeur | M | S6 | US-031 |
| FR-19 | Gérer les stocks (quantité, seuil d'alerte) | Vendeur | M | S7 | US-039 |
| FR-20 | Consulter un tableau de bord vendeur (ventes, stock faible) | Vendeur | S | S8 | US-046 |
| FR-21 | Traiter les commandes et changer leur statut | Vendeur/Admin | M | S8 | US-043 |
| FR-22 | Gérer les catégories | Admin | M | S6 | US-031 |
| FR-23 | Gérer les utilisateurs (rôle, activation, suppression) | Admin | M | S6 | US-029 |
| FR-24 | Consulter le tableau de bord global (CA, commandes, vendeurs) | Admin | M | S8 | US-046 |
| FR-25 | Modérer les avis (publier/masquer/supprimer) | Admin | S | S8 | US-045 |
| FR-26 | Générer et télécharger une facture PDF | Client/Admin | M | S8 | US-044 |
| FR-27 | Appliquer les règles d'accès selon le rôle (RBAC) | Tous | M | S8 | US-041 |
| FR-28 | Bénéficier d'un référencement (balises, URLs, sitemap, Schema.org) | Tous | M | S8 | US-047 |
| FR-29 | Affichage responsive et accessible (PC, tablette, mobile) | Tous | M | S5 | US-035 |
| FR-30 | Journaliser les actions sensibles (connexions, commandes, validation) | Admin | S | S9 | US-048 |

---

## 6. Règles métier

| ID | Règle |
|---|---|
| **RG-01** | Lors d'une commande validée, le stock de chaque produit est **décrémenté automatiquement par un trigger SQL**. Si le stock est insuffisant, l'ajout au panier est refusé et un message est affiché. |
| **RG-02** | Les prix affichés sont **TTC** avec **TVA de 19 %** incluse. La facture détaille le montant HT, la TVA et le total TTC. Devise : dinar tunisien (TND / DT). |
| **RG-03** | Frais de livraison forfaitaires : **8 DT**, offerts à partir de **100 DT** d'achat. Retrait gratuit à l'atelier du vendeur à Sidi Bou Saïd possible (option Could). |
| **RG-04** | Cycle de statut d'une commande : `En attente → Payée → En préparation → Expédiée → Livrée`. Statuts alternatifs : `Annulée`, `Remboursée`. |
| **RG-05** | Seul un client **ayant acheté le produit** peut déposer un avis, une seule fois par produit et par commande. Les avis sont modérés avant publication. |
| **RG-06** | Un vendeur ne peut publier des produits qu'après **validation de son compte par l'administrateur**. |
| **RG-07** | Le paiement est **simulé** : carte de test `4242 4242 4242 4242`, date future, CVC `123`. Aucune donnée bancaire réelle n'est stockée ; seul un identifiant de transaction factice est conservé. |
| **RG-08** | Les factures sont numérotées `F-YYYY-0001` (année + séquence), générées uniquement après paiement confirmé. |
| **RG-09** | Les mots de passe sont hachés avec `password_hash()` (Argon2id, repli bcrypt). Aucun mot de passe en clair en base ni dans les logs. |
| **RG-10** | Le panier est conservé **30 jours** maximum en base/session ; les paniers abandonnés sont purgés. |
| **RG-11** | Toute action sensible (connexion, validation vendeur, changement de statut, suppression) est journalisée avec date, utilisateur et adresse IP. |

---

## 7. Besoins non fonctionnels

| ID | Exigence | Cible |
|---|---|---|
| NFR-01 | Sécurité | Hachage Argon2id/bcrypt, sessions régénérées, jetons CSRF, requêtes préparées PDO, échappement HTML, RBAC strict côté serveur |
| NFR-02 | Performance | Temps de chargement < 2,5 s (LCP), réponse SQL < 100 ms sur le jeu d'essai, pagination des listes |
| NFR-03 | Compatibilité | Chrome, Edge, Firefox, Safari (2 dernières versions) ; PC ≥ 1024 px, tablette 640–1023 px, mobile 320–639 px |
| NFR-04 | Accessibilité | Conformité WCAG 2.1 AA : contrastes, alternatives textuelles, navigation clavier, labels de formulaire, `aria-*` |
| NFR-05 | SEO | URLs propres, balises title/description uniques, hiérarchie Hn, JSON-LD Schema.org, `sitemap.xml`, `robots.txt`, images avec `alt` |
| NFR-06 | Maintenabilité | Architecture MVC, namespaces PSR-4, code commenté, services métier découplés, exceptions typées |
| NFR-07 | Testabilité | Jeu d'essai réaliste (≥ 20 produits, 5 vendeurs, 10 clients), scénarios de recette documentés |
| NFR-08 | Intégrité des données | Clés étrangères InnoDB, contraintes `CHECK`, transactions pour le checkout, triggers de stock |
| NFR-09 | Sauvegarde | Export SQL de la base livré (`database/`), restauration documentée |
| NFR-10 | Confidentialité | Aucune donnée bancaire stockée, données personnelles limitées au nécessaire, déconnexion sécurisée |

---

## 8. Architecture technique prévisionnelle

### 8.1 Choix technologiques (validés en S1)

- **Back-end** : PHP 8.3, architecture **MVC maison** (Modèle – Vue – Contrôleur), autoload PSR-4 via Composer, namespaces `App\Controllers`, `App\Models`, `App\Services`, `App\Core`, `App\Middlewares`.
- **Base de données** : MySQL 8.0 (moteur InnoDB), accès **PDO** avec requêtes préparées, transactions pour la commande.
- **Front-end** : HTML5 sémantique, CSS3 (Grid, Flexbox, variables CSS, media queries), JavaScript ES6+ natif (`fetch`, DOM), pas de framework lourd.
- **Sécurité** : sessions PHP sécurisées (`httponly`, `samesite`), `password_hash()`/`password_verify()`, CSRF token par formulaire, RBAC par middleware, validation et échappement centralisés.
- **PDF** : DomPDF ou TCPDF (dépendance Composer).
- **Outils** : XAMPP (Apache/MySQL), Git, Composer, VS Code, Figma, MySQL Workbench.

### 8.2 Arborescence applicative

```
tunishop/
├── public/                       # Document root
│   ├── index.php                 # Routeur frontal (front controller)
│   ├── assets/{css,js,img}/      # Ressources statiques
│   ├── uploads/                  # Photos produits / logos (non versionnés)
│   ├── sitemap.xml
│   └── robots.txt
├── app/
│   ├── Core/                     # Router, Database, Session, Request, Response, Controller
│   ├── Middlewares/              # AuthMiddleware, CsrfMiddleware, RoleMiddleware
│   ├── Controllers/              # Product, Category, Cart, Order, User, Shop, Admin…
│   ├── Models/                   # Entités + accès données (PDO)
│   ├── Services/                 # CartService, OrderService, PaymentService, PdfService
│   ├── Views/                    # Layouts + vues (front, vendeur, admin)
│   └── Exceptions/               # ValidationException, NotFoundException…
├── config/                       # app.php, database.php, routes.php
├── database/                     # schema.sql, seed.sql, triggers.sql, procedures.sql
├── docs/                         # Les 9 dossiers de livrables
├── tests/                        # Scénarios de recette, audit
└── vendor/                       # Dépendances Composer (ignoré par Git)
```

### 8.3 Flux de traitement

```
Navigateur → public/index.php (routeur) → Middlewares (session, CSRF, rôle)
          → Contrôleur → Service métier → Modèle (PDO) → MySQL
          ← Vue (HTML/CSS/JS) ← Contrôleur ← Réponse
```

---

## 9. Planning et jalons

Le planning détaillé figure dans [planning_gantt.md](planning_gantt.md) et sa version graphique dans [gantt.html](gantt.html).

| Jalon | Échéance | Critères de validation |
|---|---|---|
| **Validation 1** | 29/11/2026 | CDC complet, backlog, Gantt, wireframes validés |
| **Validation 2** | 20/12/2026 | Interfaces responsives intégrées, prototype Figma, base SQL avec triggers/procédures |
| **Préjury** | 10/01/2027 | Démonstration parcours client + back-office, détection des anomalies |
| **Validation 3** | 24/01/2027 | Dépôt Git complet, scripts SQL, documentation des 8 matières |
| **Jury final** | 25/01/2027 | Soutenance orale + démonstration en direct |

Rythme Agile : **5 sprints de 2 semaines**, revues aux jalons S2, S5, S8 ; voir [suivi_sprints.md](suivi_sprints.md).

---

## 10. Critères de recette globale

- [ ] Parcours client complet fonctionnel : accueil → catalogue → recherche → fiche → panier → commande → paiement → confirmation → facture PDF → avis.
- [ ] Parcours vendeur : validation du compte par l'admin → création boutique → ajout produit → stock → traitement commande → dashboard.
- [ ] Parcours admin : validation vendeur → gestion catégories/utilisateurs → suivi commandes → dashboard → modération avis.
- [ ] Responsive vérifié sur 3 résolutions (PC, tablette, mobile) et prototype Figma conforme.
- [ ] Base de données : script reproductible, jeu d'essai chargé, triggers de stock et procédures statistiques démontrés.
- [ ] Sécurité : inscription/connexion hachée, RBAC respecté, jetons CSRF présents, aucune requête SQL concaténée, rapport d'audit rendu.
- [ ] SEO : balises uniques, sitemap.xml, robots.txt, JSON-LD, URLs propres.
- [ ] Documentation des 8 matières + support de soutenance livrés avant le 24/01/2027.

---

## 11. Risques et mesures correctives

| Risque | Prob. | Impact | Mesure de mitigation |
|---|---|---|---|
| Retard sur le jalon Validation 2 (S5) | Moyenne | Fort | Découpage en sprints de 2 semaines, suivi hebdomadaire, périmètre Could déplaçable en S10 |
| Périmètre trop large pour 10 semaines | Forte | Fort | Priorisation MoSCoW, gel du périmètre à S2, éléments « Could » en backlog de réserve |
| Git / Composer non installés | Forte | Moyen | Installation en début de projet (voir `git_workflow.md`), procédure d'autoload de secours documentée |
| Conflits de fusion entre membres | Moyenne | Moyen | Branches par fonctionnalité, commits atomiques, revue avant merge sur `develop` |
| Jeu de données irréaliste | Moyenne | Faible | Jeu d'essai construit à partir de vrais produits/artisans de Sidi Bou Saïd |
| Mauvais fonctionnement du trigger de stock | Moyenne | Fort | Tests SQL dédiés + transaction sur le checkout + verrouillage `FOR UPDATE` |
| Incompréhension du paiement sandbox | Faible | Moyen | Page de test explicite, mention « aucun débit réel » dans l'UI et le manuel |
| Absence de Figma / outil de design | Faible | Moyen | Wireframes papier + maquettes HTML/CSS de repli, prototype cliquable en HTML |

---

## 12. Glossaire

| Terme | Définition |
|---|---|
| **Marketplace** | Plateforme permettant à plusieurs vendeurs indépendants de proposer leurs produits à des acheteurs. |
| **Back-office** | Interface d'administration réservée aux vendeurs et administrateurs. |
| **Front-office** | Partie publique visible par les visiteurs et clients. |
| **RBAC** | *Role-Based Access Control* : contrôle d'accès selon le rôle de l'utilisateur. |
| **CSRF** | *Cross-Site Request Forgery* : attaque qui force un utilisateur à émettre une requête non désirée. |
| **XSS** | *Cross-Site Scripting* : injection de script dans une page web. |
| **SQLi** | *Injection SQL* : injection de requête SQL via une entrée utilisateur. |
| **Sandbox** | Environnement de test simulant un paiement sans opération bancaire réelle. |
| **MCD / MLD** | Modèle Conceptuel / Logique de Données (méthode Merise). |
| **User story** | Description courte d'un besoin utilisateur : « En tant que… je veux… afin de… ». |
| **Definition of Done** | Liste des critères qui doivent être remplis pour qu'une tâche soit considérée comme terminée. |

---

## 13. Références

- *Cahier des charges pédagogique — BTS DI 2-1 (TuniShop)*, Département Infographie & Multimédia, document de référence, 10 pages.
- [Backlog produit](backlog_produit.md) — user stories et critères d'acceptation.
- [Planning Gantt](planning_gantt.md) — lots de travail et jalons.
- [Suivi des sprints](suivi_sprints.md) — rituels Agile et vélocité.

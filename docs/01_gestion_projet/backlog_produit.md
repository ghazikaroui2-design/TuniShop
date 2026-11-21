# Backlog produit — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 |
| Produit | TuniShop Marketplace |
| Product Owner | Chef de projet (rôle tournant à chaque sprint) |
| Estimation | Story points (fibonacci : 1, 2, 3, 5, 8) |
| Total | **58 user stories — 183 points** |
| Répartition | 51 Must · 5 Should · 2 Could |

> Le périmètre **Must est gelé à la fin de S2**. Les éléments Should/Could servent de tampon en cas de retard et basculent en backlog de réserve V1.1.

---

## 1. Epics (thèmes)

| Code | Epic | User stories | Points | Couleur |
|---|---|---|---|---|
| E01 | Cadrage & gestion de projet | US-001 → US-005, US-010, US-028, US-049, US-056 → US-058 | 32 | Gris |
| E02 | Design UX/UI | US-006 → US-009, US-011 → US-013, US-034 | 23 | Violet |
| E03 | Base de données | US-014 → US-018, US-023, US-024, US-050 | 24 | Vert |
| E04 | Architecture & POO | US-025, US-029 → US-032, US-051 | 21 | Bleu |
| E05 | Catalogue & recherche | US-019 → US-022, US-026, US-033 | 23 | Cyan |
| E06 | E-commerce | US-027, US-035 → US-039, US-044, US-054, US-055 | 30 | Orange |
| E07 | Sécurité & rôles | US-040 → US-042, US-048, US-053 | 14 | Rouge |
| E08 | Administration & pilotage | US-043, US-045, US-046 | 11 | Jaune |
| E09 | SEO & Marketing | US-047, US-052 | 5 | Rose |
| **TOTAL** | | **58 stories** | **183** | |

> Un thème peut intervenir dans plusieurs sprints ; chaque user story n'est comptée qu'une seule fois dans le total.

---

## 2. Capacité et vélocité prévisionnelle

| Sprint | Semaines | Période | Points prévus | Objectif du sprint |
|---|---|---|---|---|
| **Sprint 1** | S1–S2 | 16/11 → 29/11 | **27** | Cadrage, backlog, Gantt, personas, wireframes, validation 1 |
| **Sprint 2** | S3–S4 | 30/11 → 13/12 | **40** | Design UI, prototype Figma, modélisation et script SQL, pages publiques |
| **Sprint 3** | S5–S6 | 14/12 → 27/12 | **41** | Base SQL avancée, socle MVC, CRUD, responsive complet, validation 2 |
| **Sprint 4** | S7–S8 | 28/12 → 10/01 | **45** | Authentification, panier, checkout, paiement, sécurité, SEO, préjury |
| **Sprint 5** | S9–S10 | 11/01 → 24/01 | **30** | Audit, tests, corrections, marketing, déploiement, documentation, validation 3 |
| **TOTAL** | | | **183** | |

---

## 3. Backlog par sprint

Légende priorité : **M** Must · **S** Should · **C** Could.
Chaque ligne est une *user story* avec ses critères d'acceptation (AC) résumés. Les 10 stories critiques sont détaillées en section 4.

### Sprint 1 — Cadrage et spécifications (27 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-001 | E01 | En tant que chef de projet, je veux un cahier des charges complet afin de cadrer le périmètre. | M | 5 | Contexte, acteurs, 30 besoins fonctionnels, 11 règles métier, NFR, risques, glossaire. Validé par l'équipe. |
| US-002 | E01 | En tant qu'équipe, nous voulons un backlog priorisé afin de savoir quoi développer. | M | 3 | 58 US avec AC, estimations, priorités MoSCoW et répartition en sprints. |
| US-003 | E01 | En tant que chef de projet, je veux un planning Gantt afin de suivre les jalons. | M | 3 | 10 semaines, jalons S2/S5/S8/S10, chemin critique, dépendances, version graphique HTML. |
| US-004 | E01 | En tant que développeur, je veux un dépôt Git structuré afin de collaborer sans conflit. | M | 2 | `.gitignore`, README, branches `main`/`develop`, convention de commits documentée. |
| US-005 | E01 | En tant qu'équipe, nous voulons répartir les rôles afin de clarifier les responsabilités. | M | 2 | 5 rôles identifiés (chef de projet, UX/UI, intégration front, back/POO/E-commerce, BDD/Sécurité/SEO), planning des rituels. ✅ Livrée le 21/11. |

### Sprint 2 — Recherche UX & wireframing (12 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-006 | E02 | En tant que designer, je veux 3 personas afin d'orienter les choix d'interface. | M | 2 | Acheteur curieux, artisan local, administrateur : objectifs, frustrations, citations. |
| US-007 | E02 | En tant que designer, je veux des user flows afin de visualiser les parcours clés. | M | 2 | Parcours d'achat, parcours vendeur, parcours de validation admin (diagrammes). |
| US-008 | E02 | En tant que designer, je veux des wireframes front-office afin de valider la structure. | M | 3 | Accueil, catalogue, filtres, fiche produit, panier, checkout (basse fidélité). |
| US-009 | E02 | En tant que designer, je veux des wireframes back-office afin de valider la structure. | M | 3 | Dashboard admin, boutique vendeur, produits/stocks, commandes. |
| US-010 | E01 | En tant que chef de projet, je veux un dossier de recette afin de préparer la validation 1. | M | 2 | Checklist des livrables S2, comptes rendus, plan de validation. |

### Sprint 3 — Design UI & modélisation BDD (19 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-011 | E02 | En tant que designer, je veux une identité graphique et un UI kit afin d'harmoniser les écrans. | M | 3 | Palette bleu/blanc Sidi Bou Saïd + ocre, typographies, boutons, cartes, formulaires, icônes. |
| US-012 | E02 | En tant que designer, je veux un prototype Figma Desktop afin de simuler l'application. | M | 5 | Parcours client + back-office cliquables, états hover/erreur, ≥ 10 écrans. |
| US-013 | E02 | En tant que designer, je veux une version Mobile du prototype afin de valider le responsive. | M | 3 | Écrans clés adaptés 375 px, navigation mobile, gestes, hiérarchie conservée. |
| US-014 | E03 | En tant qu'architecte données, je veux un dictionnaire de données afin de documenter la structure. | M | 3 | Toutes les tables, champs, types, contraintes, cardinalités, domaines de valeurs. |
| US-015 | E03 | En tant qu'architecte données, je veux un MCD afin de modéliser le domaine. | M | 3 | Entités, associations, cardinalités (utilisateurs, boutiques, produits, commandes, avis…). |
| US-016 | E03 | En tant qu'architecte données, je veux un MLD afin de préparer le SQL. | M | 2 | Tables, clés primaires/étrangères, tables de liaison, normalisation 3FN. |

### Sprint 4 — Intégration front-end & base SQL (21 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-017 | E03 | En tant que développeur, je veux le script SQL de création afin d'avoir une base opérationnelle. | M | 5 | DDL InnoDB, contraintes, index, `database/schema.sql` exécutable d'un seul bloc. |
| US-018 | E03 | En tant que testeur, je veux un jeu d'essai afin de démontrer l'application. | M | 3 | ≥ 5 vendeurs, 20 produits, 10 clients, catégories, commandes et avis réalistes. |
| US-019 | E05 | En tant que visiteur, je veux une page d'accueil responsive afin de découvrir TuniShop. | M | 3 | Héros, catégories, produits mis en avant, valeur « 100 % local », 3 breakpoints. |
| US-020 | E05 | En tant que visiteur, je veux un catalogue avec filtres dynamiques afin de trouver un produit. | M | 5 | Grille produits, filtres catégorie/prix/vendeur, JS `fetch`, état vide, chargement. |
| US-021 | E05 | En tant que visiteur, je veux une fiche produit détaillée afin de décider d'acheter. | M | 3 | Galerie, prix TTC, stock disponible, description, vendeur, bouton « Ajouter au panier ». |
| US-022 | E05 | En tant que visiteur, je veux naviguer par catégories afin de parcourir l'offre. | M | 2 | Menu catégories, fil d'Ariane, comptage des produits par catégorie. |

### Sprint 5 — Front complet, triggers SQL et socle MVC (21 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-023 | E03 | En tant qu'architecte, je veux un trigger de stock afin de garantir l'inventaire. | M | 3 | Décrément automatique après insertion d'une ligne de commande, rollback si insuffisant. |
| US-024 | E03 | En tant qu'administrateur, je veux des vues et procédures stockées afin d'obtenir des statistiques. | M | 3 | CA par vendeur, meilleures ventes, stock faible ; appelables en SQL et depuis le dashboard. |
| US-025 | E04 | En tant que développeur, je veux le socle MVC et les diagrammes UML afin de structurer le code. | M | 5 | Routeur, autoload, contrôleurs/modèles/vues de base, diagrammes de classes et de séquence. |
| US-026 | E05 | En tant qu'utilisateur, je veux les espaces client/vendeur/admin responsive afin d'accéder à mes fonctions. | M | 5 | 3 layouts distincts, menu adapté au rôle, vérifié PC/tablette/mobile. |
| US-027 | E06 | En tant que client, je veux l'interface du tunnel de commande afin de visualiser le parcours. | M | 3 | Étapes panier → livraison → paiement, barre de progression, récapitulatif. |
| US-028 | E01 | En tant que chef de projet, je veux la revue de sprint 2 afin de valider le jalon 2. | M | 2 | Démo front + BDD, PV de validation, anomalies listées et priorisées. |

### Sprint 6 — CRUD et recherche avancée (20 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-029 | E04 | En tant qu'admin, je veux gérer les utilisateurs afin de contrôler les accès. | M | 3 | Liste, recherche, changement de rôle, activation/suspension, suppression sécurisée. |
| US-030 | E04 | En tant que vendeur, je veux gérer ma boutique afin de présenter mon savoir-faire. | M | 3 | Création/modification nom, description, logo, contact, quartier à Sidi Bou Saïd. |
| US-031 | E04 | En tant que vendeur/admin, je veux gérer produits et catégories afin d'alimenter le catalogue. | M | 5 | CRUD complet, upload image, validation des champs, slug SEO, catégorie obligatoire. |
| US-032 | E04 | En tant que développeur, je veux le CRUD des avis afin de les stocker et modérer. | M | 2 | Ajout, édition, suppression, statut `publié`/`masqué`, lié à un client et un produit. |
| US-033 | E05 | En tant que visiteur, je veux une recherche multicritères afin d'affiner les résultats. | M | 5 | Texte libre + catégorie + fourchette de prix + vendeur + tri + pagination. |
| US-034 | E02 | En tant qu'utilisateur, je veux une interface accessible afin de naviguer au clavier. | S | 2 | Contrast ≥ 4.5:1, `alt`, labels, focus visible, annonces ARIA, test Lighthouse. |

### Sprint 7 — Tunnel d'achat et authentification (23 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-035 | E06 | En tant que visiteur, je veux un panier persistant afin de ne pas perdre ma sélection. | M | 5 | Ajout, modification quantité, suppression, persistance session/BDD, recap par vendeur. |
| US-036 | E06 | En tant que client, je veux voir le détail des frais afin de comprendre le total. | M | 3 | Sous-total HT, TVA 19 %, livraison 8 DT (offerte ≥ 100 DT), total TTC. |
| US-037 | E06 | En tant que client, je veux valider ma commande afin de recevoir mes produits. | M | 5 | 3 étapes, adresse de livraison, vérification stock, numéro de commande unique, email/écran de confirmation. |
| US-038 | E06 | En tant que client, je veux simuler un paiement afin de finaliser sans risque. | M | 5 | Formulaire carte test, validation côté serveur, transaction enregistrée, mention « sandbox », aucun débit. |
| US-039 | E06 | En tant que vendeur, je veux gérer mes stocks afin d'éviter les ruptures. | M | 2 | Quantité, seuil d'alerte, indicateur « stock faible », mise à jour automatique. |
| US-040 | E07 | En tant qu'utilisateur, je veux m'inscrire et me connecter afin d'accéder à mon espace. | M | 3 | `password_hash()` Argon2id, session régénérée, `httponly`/`samesite`, profil modifiable. |

### Sprint 8 — Back-office, sécurité et SEO (22 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-041 | E07 | En tant qu'admin, je veux valider les vendeurs afin de garantir la qualité de la marketplace. | M | 3 | Liste des comptes en attente, accepter/refuser avec motif, notification, boutique visible après validation. |
| US-042 | E07 | En tant que développeur, je veux protéger les formulaires afin d'éviter les attaques. | M | 3 | Jeton CSRF par formulaire, `htmlspecialchars()` à l'affichage, PDO préparé partout, validation serveur. |
| US-043 | E08 | En tant que client/vendeur, je veux suivre les commandes afin de connaître leur état. | M | 3 | Historique client, changement de statut côté vendeur/admin, horodatage, filtre par statut. |
| US-044 | E06 | En tant que client, je veux télécharger ma facture afin d'avoir une preuve d'achat. | M | 3 | PDF `F-YYYY-0001`, coordonnées vendeur/client, lignes, TVA, total, généré après paiement. |
| US-045 | E08 | En tant que client/admin, je veux publier et modérer des avis afin de construire la confiance. | M | 3 | 1 avis max par produit/commande, note 1–5, contrôle achat, modération avant publication. |
| US-046 | E08 | En tant qu'admin/vendeur, je veux un tableau de bord afin de piloter l'activité. | M | 5 | KPI : CA, nombre de commandes, vendeurs actifs, stock faible, graphiques, filtres de période. |
| US-047 | E09 | En tant que visiteur, je veux un site référencé afin de trouver TuniShop sur Google. | M | 2 | Balises title/description uniques, Hn, Schema.org, URLs propres, `sitemap.xml`, `robots.txt`. |

### Sprint 9 — Audit, tests et marketing (18 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-048 | E07 | En tant que responsable sécurité, je veux journaliser et auditer afin de produire le rapport. | M | 3 | Logs des actions sensibles (IP, date, utilisateur), rapport d'audit OWASP, fiches correctives. |
| US-049 | E01 | En tant que testeur, je veux exécuter les scénarios de recette afin de fiabiliser la démo. | M | 5 | Parcours client/vendeur/admin testés, anomalie corrigée ou documentée, PV de recette. |
| US-050 | E03 | En tant qu'architecte, je veux optimiser la base afin d'améliorer les performances. | S | 2 | Index sur clés de recherche, `EXPLAIN` des requêtes lentes, contraintes d'intégrité vérifiées. |
| US-051 | E04 | En tant que développeur, je veux refactorer le code afin d'améliorer sa maintenabilité. | S | 3 | Services métier découplés, interfaces, exceptions typées, duplication réduite. |
| US-052 | E09 | En tant que responsable marketing, je veux un plan de lancement afin d'attirer les visiteurs. | S | 3 | Mots-clés, calendrier social, newsletter, KPI (trafic, paniers, commandes), maquettes de campagne. |
| US-053 | E07 | En tant qu'utilisateur, je veux réinitialiser mon mot de passe afin de retrouver mon compte. | S | 2 | Token à usage unique et expirant, envoi simulé, nouveau hachage, ancien mot de passe invalidé. |

### Sprint 10 — Finition, déploiement et soutenance (12 pts)

| ID | Epic | User story | Prio | Pts | Critères d'acceptation (résumé) |
|---|---|---|---|---|---|
| US-054 | E06 | En tant que client, je veux saisir un code promo afin de bénéficier d'une réduction. | C | 2 | Code valide, pourcentage ou montant fixe, contrôle date et cumul, recalcul du total. |
| US-055 | E06 | En tant que client, je veux retirer ma commande à l'atelier afin d'éviter les frais. | C | 2 | Option « retrait Sidi Bou Saïd », adresse du vendeur, livraison à 0 DT, statut dédié. |
| US-056 | E01 | En tant que développeur, je veux déployer une démo afin de la présenter au jury. | M | 3 | XAMPP configuré, base importée, URL locale documentée, sauvegarde SQL fournie. |
| US-057 | E01 | En tant que chef de projet, je veux le dossier technique et le manuel utilisateur afin de satisfaire à la remise. | M | 3 | 7 dossiers conformes, manuel avec captures, scripts SQL, dépendances listées. |
| US-058 | E01 | En tant que présentateur, je veux un support de soutenance afin d'argumenter devant le jury. | M | 2 | Diaporama PDF/PPTX, démonstration scriptée, réponses préparées aux questions techniques. |

---

## 4. Fiches détaillées des stories critiques (Given / When / Then)

### US-035 — Panier persistant (E06, 5 pts, Sprint 7)

**En tant que** visiteur, **je veux** ajouter, modifier et supprimer des articles de mon panier **afin de** préparer ma commande sans perdre ma sélection.

```gherkin
Scénario 1 : Ajout d'un produit disponible
  Etant donné le produit "Vase bleu et blanc" avec un stock de 8
  Quand je clique sur "Ajouter au panier" avec la quantité 2
  Alors le panier contient 2 exemplaires et le sous-total est recalculé

Scénario 2 : Stock insuffisant
  Etant donné un stock de 1 exemplaire
  Quand je demande 3 exemplaires
  Alors l'ajout est refusé avec le message "Stock insuffisant (1 disponible)"

Scénario 3 : Persistance
  Etant donné un panier avec 2 articles
  Quand je me déconnecte puis me reconnecte dans les 30 jours
  Alors je retrouve exactement les mêmes articles et quantités
```

### US-037 — Checkout et confirmation (E06, 5 pts, Sprint 7)

```gherkin
Scénario : Commande complète
  Etant donné un panier non vide et un client connecté
  Quand je renseigne une adresse de livraison valide et confirme
  Alors une commande est créée avec le statut "En attente" et un numéro CMD-2026-0001
  Et le stock de chaque produit est décrémenté par le trigger
  Et une page de confirmation affiche le numéro, le total TTC et la date
```

### US-038 — Paiement sandbox (E06, 5 pts, Sprint 7)

```gherkin
Scénario 1 : Paiement accepté
  Etant donné la carte de test 4242 4242 4242 4242, une date future et le CVC 123
  Quand je valide le paiement
  Alors la commande passe au statut "Payée", une transaction factice est enregistrée
  Et la facture PDF devient téléchargeable

Scénario 2 : Carte invalide
  Etant donné un numéro de carte incorrect
  Quand je valide le paiement
  Alors un message d'erreur est affiché, la commande reste "En attente"
  Et aucune transaction n'est enregistrée
```

### US-040 — Authentification sécurisée (E07, 3 pts, Sprint 7)

```gherkin
Scénario : Connexion
  Etant donné un compte client actif avec un mot de passe haché Argon2id
  Quand je soumets les identifiants corrects
  Alors l'identifiant de session est régénéré, un cookie httponly/samesite est posé
  Et je suis redirigé vers mon espace client
  En cas d'échec : message générique, aucune indication sur l'existence du compte
```

### US-041 — Validation des vendeurs (E07, 3 pts, Sprint 8)

```gherkin
Scénario : Validation acceptée
  Etant donné un compte vendeur en attente de validation
  Quand l'administrateur clique sur "Valider"
  Alors le compte devient actif, la boutique est visible publiquement
  Et l'action est journalisée (admin, date, IP)
```

### US-042 — Protection CSRF / XSS / SQLi (E07, 3 pts, Sprint 8)

```gherkin
Scénario : Formulaire protégé
  Etant donné un formulaire contenant un jeton CSRF lié à la session
  Quand une requête est soumise sans jeton valide
  Alors la requête est rejetée (HTTP 403) et rien n'est modifié en base
  Et tout contenu utilisateur est échappé à l'affichage (htmlspecialchars)
  Et toutes les requêtes SQL utilisent des marqueurs nominatifs PDO
```

### US-043 — Suivi des commandes (E08, 3 pts, Sprint 8)

```gherkin
Scénario : Changement de statut
  Etant donné une commande "Payée"
  Quand le vendeur la passe en "En préparation"
  Alors le client voit le nouveau statut dans son historique avec l'horodatage
  Et les transitions invalides (ex. "Livrée" → "Payée") sont refusées
```

### US-044 — Facture PDF (E06, 3 pts, Sprint 8)

```gherkin
Scénario : Génération
  Etant donné une commande payée numérotée CMD-2026-0001
  Quand je clique sur "Télécharger la facture"
  Alors un PDF "F-2026-0001.pdf" est généré avec les coordonnées du vendeur et du client,
  les lignes, le montant HT, la TVA 19 %, le total TTC et la mention "Paiement simulé"
```

### US-045 — Avis clients (E08, 3 pts, Sprint 8)

```gherkin
Scénario : Dépôt d'avis
  Etant donné un client ayant acheté le produit lors d'une commande livrée
  Quand il dépose un avis noté 4 avec un commentaire
  Alors l'avis est enregistré avec le statut "En attente de modération"
  Et il n'est visible publiquement qu'après validation de l'administrateur
```

### US-047 — SEO technique (E09, 2 pts, Sprint 8)

```gherkin
Scénario : Page fiche produit
  Etant donné une fiche produit
  Quand la page est générée
  Alors elle contient un title unique, une meta description, un H1, du JSON-LD Product,
  une URL propre "/produit/vase-bleu-blanc", et une image avec un attribut alt
  Et le sitemap.xml et le robots.txt sont accessibles à la racine
```

---

## 5. Definition of Ready (DoR)

Une story entre en sprint seulement si :

- [ ] Le besoin est formulé en « En tant que… je veux… afin de… » ;
- [ ] Les critères d'acceptation sont testables et validés avec le Product Owner ;
- [ ] La dépendance technique (BDD, maquette, autre US) est identifiée ;
- [ ] L'estimation est consensuelle (planning poker) ;
- [ ] La priorité MoSCoW est définie.

## 6. Definition of Done (DoD)

Une story est terminée seulement si :

- [ ] Tous les critères d'acceptation passent ;
- [ ] Le code respecte le MVC, les namespaces et les conventions de nommage ;
- [ ] Aucune requête SQL concaténée, aucune sortie HTML non échappée ;
- [ ] Le comportement responsive est vérifié sur 3 largeurs (375 / 768 / 1440 px) ;
- [ ] Le scénario de recette correspondant est ajouté au dossier de tests ;
- [ ] La documentation est mise à jour et la branche est fusionnée dans `develop` après revue ;
- [ ] LeProduct Owner valide la démonstration.

---

## 7. Règles de gestion associées aux stories

| Règle | Stories concernées |
|---|---|
| RG-01 Stock décrémenté par trigger | US-023, US-035, US-037, US-039 |
| RG-02 TVA 19 % incluse | US-036, US-044 |
| RG-03 Frais 8 DT / offerts ≥ 100 DT | US-036, US-037 |
| RG-04 Cycle de statuts commande | US-037, US-043 |
| RG-05 Avis après achat, modéré | US-032, US-045 |
| RG-06 Validation vendeur avant publication | US-041, US-030 |
| RG-07 Paiement sandbox | US-038 |
| RG-08 Numérotation facture | US-044 |
| RG-09 Hachage mot de passe | US-040, US-053 |
| RG-10 Panier 30 jours | US-035 |
| RG-11 Journalisation | US-048, US-041 |

---

## 8. Backlog de réserve (V1.1 — non financé)

- Export CSV des commandes pour le vendeur.
- Comparateur de produits et liste de souhaits.
- Notifications par email (confirmation, expédition).
- Support de plusieurs langues (arabe / français).
- Paiement à la livraison avec génération d'un reçu.
- Géolocalisation des ateliers sur une carte interactive.

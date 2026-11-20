# User flows — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 |
| Outil de lecture | Diagrammes **Mermaid** ( rendus nativement sur GitHub/GitLab, ou copiables dans <https://mermaid.live> ) |
| Personas associés | Aymen (client), Fatma (vendeuse), Karim (admin) — voir [personas.md](personas.md) |
| Prototype cliquable | [prototype_tunishop.html](prototype_tunishop.html) |

Légende des couleurs Mermaid : 🟢 succès / état final · 🟡 décision · 🔵 action utilisateur · 🔴 erreur.

---

## 1. Flux principal — Parcours d'achat client (happy path)

```mermaid
flowchart TD
    A([Visiteur]) --> B[Accueil TuniShop]
    B --> C{Navigation}
    C -->|Catégories| D[Catalogue filtré]
    C -->|Recherche| E[Résultats de recherche]
    D --> F[Fiche produit]
    E --> F
    F --> G{Stock > 0 ?}
    G -->|Non| H([Afficher 'Rupture de stock' + produits similaires])
    G -->|Oui| I[Ajouter au panier]
    I --> J[Panier persistant]
    J --> K{Vérifier montant}
    K -->|Livraison ≥ 100 DT| L[Frais offerts]
    K -->|< 100 DT| M[Frais 8 DT]
    L --> N[Checkout — étape 2 : livraison]
    M --> N
    N --> O[Checkout — étape 3 : paiement sandbox]
    O --> P{Carte de test valide ?}
    P -->|Non| Q([Erreur : commande reste 'En attente'])
    P -->|Oui| R[Commande 'Payée' + numéro CMD]
    R --> S[(Trigger SQL : stock décrémenté)]
    S --> T([Page de confirmation + facture PDF])
    T --> U[Espace client : suivi du statut]
    U --> V([Avis après livraison])
```

### Étapes détaillées

| # | Étape | Écran | Action utilisateur | Feedback système | US |
|---|---|---|---|---|---|
| 1 | Découverte | Accueil | Clique sur « Découvrir le catalogue » | Navigation vers catalogue | US-019 |
| 2 | Exploration | Catalogue | Filtre par catégorie / prix / vendeur | Grille mise à jour sans rechargement (fetch) | US-020, US-022, US-033 |
| 3 | Décision | Fiche produit | Lit photos, prix TTC, stock, avis | Badge « 7 en stock », note 4,8/5 | US-021 |
| 4 | Ajout | Fiche produit | Clique « Ajouter au panier » | Toast « Ajouté au panier » + badge panier (2) | US-035 |
| 5 | Contrôle | Panier | Modifie quantité, saisit code promo (option) | Recalcul HT / TVA 19 % / livraison / total | US-036, US-054 |
| 6 | Checkout | Livraison | Renseigne adresse, choisit livraison ou retrait | Récapitulatif latéral mis à jour | US-037, US-055 |
| 7 | Paiement | Paiement | Saisit `4242 4242 4242 4242` / date / `123` | Mention « Simulation — aucun débit réel » | US-038 |
| 8 | Confirmation | Confirmation | Lit le numéro de commande | `CMD-2026-0001`, bouton facture PDF | US-037, US-044 |
| 9 | Suivi | Espace client | Consulte « Mes commandes » | Statut `Payée → En préparation → Expédiée → Livrée` | US-043 |
| 10 | Avis | Espace client | Note 5/5 + commentaire | Avis « En attente de modération » | US-045 |

### Chemins alternatifs et erreurs

| Situation | Comportement attendu |
|---|---|
| Visiteur non connecté au checkout | Redirection vers connexion/inscription, puis retour au panier (panier conservé) |
| Stock devenu insuffisant entre l'ajout et le paiement | Message « Stock insuffisant, quantité adjustée à N », mise à jour automatique |
| Carte invalide | Message générique « Paiement refusé », commande reste `En attente`, aucune transaction enregistrée |
| Session expirée | Redirection vers connexion avec message « Votre session a expiré » |
| Avis déjà déposé | Bouton désactivé + message « Vous avez déjà noté ce produit » |

---

## 2. Flux de recherche multicritères

```mermaid
flowchart LR
    A[Visiteur sur le catalogue] --> B[Saisit un mot-clé]
    B --> C[Choix catégorie / prix / vendeur]
    C --> D[Tri : prix croissant, récence, popularité]
    D --> E[Requête AJAX GET filtres]
    E --> F{Résultats ?}
    F -->|Oui| G[Grille produits + pagination]
    F -->|Non| H([État vide : 'Aucun produit' + bouton 'Réinitialiser les filtres'])
    G --> I[Clic produit → Fiche]
```

**Règles :** les filtres sont conservés dans l'URL (`?q=vase&cat=ceramique&min=20&max=60`) pour permettre le partage et le référencement ; la pagination limite 12 produits par page.

---

## 3. Flux devenir vendeur (inscription + validation admin)

```mermaid
sequenceDiagram
    participant F as Fatma (vendeuse)
    participant T as TuniShop
    participant K as Karim (admin)
    F->>T: Inscription (rôle = vendeur)
    T->>T: Mot de passe haché (Argon2id)
    T-->>F: Email de validation simulé
    T->>K: Notification "Nouvelle demande vendeur"
    K->>T: Ouvre la liste des demandes
    alt Compte validé
        K->>T: Valider
        T->>T: compte.actif = 1, boutique.visible = 1
        T-->>F: "Votre boutique est en ligne"
    else Compte refusé
        K->>T: Refuser + motif obligatoire
        T-->>F: "Demande refusée : motif"
    end
    F->>T: Créer sa boutique et ajouter des produits
```

---

## 4. Flux vendeur — Ajout produit et suivi des commandes

```mermaid
flowchart TD
    A([Fatma connectée]) --> B[Espace vendeur]
    B --> C{Que veut-elle faire ?}
    C -->|Boutique| D[Modifier nom, description, quartier, contact]
    C -->|Produit| E[Formulaire : photo, titre, prix HT, catégorie, stock]
    E --> F{Champs valides ?}
    F -->|Non| G([Messages d'erreur inline])
    F -->|Oui| H[(Insertion produit + slug SEO)]
    H --> I([Toast 'Produit publié' + aperçu dans le catalogue])
    C -->|Commandes| J[Liste des commandes]
    J --> K[Choisir un statut valide]
    K --> L[(Mise à jour + horodatage)]
    L --> M([Client informé via son espace])
    C -->|Stock| N[Voir produits en stock faible]
    N --> O[Modifier la quantité]
```

**Transition de statut autorisée** (RG-04) :
`En attente → Payée → En préparation → Expédiée → Livrée` — toute transition inverse ou saut invalide est refusée et signalée.

---

## 5. Flux administrateur — Validation et pilotage

```mermaid
flowchart TD
    A([Karim connecté — RBAC admin]) --> B[Dashboard]
    B --> C{Alertes visibles}
    C -->|Demandes vendeurs| D[Liste 'En attente']
    D --> E{Valider ?}
    E -->|Oui| F[(Compte activé + journalisation)]
    E -->|Non| G[(Refus + motif + journalisation)]
    C -->|Commande bloquée| H[Filtrer par statut 'En attente']
    H --> I[Relancer / changer le statut]
    C -->|Stock faible| J[Voir la liste des produits concernés]
    C -->|Avis signalés| K[Modérer : publier ou masquer]
    B --> L[Consulter KPI : CA, commandes, vendeurs actifs]
    L --> M[(Procédures stockées : statistiques de ventes)]
    M --> N([Bilan affiché + export possible])
```

---

## 6. Flux de paiement sandbox (avec gestion d'erreur)

```mermaid
flowchart TD
    A[Commande 'En attente'] --> B[Écran de paiement]
    B --> C[Saisie carte de test]
    C --> D[Validation côté serveur]
    D --> E{Numéro = 4242…4242 ?}
    E -->|Non| F([Message 'Paiement refusé' — aucun débit])
    F --> B
    E -->|Oui| G[(Transaction factice enregistrée)]
    G --> H[(Commande → 'Payée')]
    H --> I([Facture PDF disponible])
```

> **Important UX :** la mention « Mode simulation — aucune opération bancaire réelle n'est effectuée » est affichée en pendant la saisie et sur la facture, afin que le jury comprenne immédiatement le périmètre.

---

## 7. Flux de dépôt d'avis

```mermaid
flowchart TD
    A([Client connecté]) --> B[Espace client → Mes commandes]
    B --> C{Commande 'Livrée' ?}
    C -->|Non| D([Bouton avis indisponible + explication])
    C -->|Oui| E[Choisir un produit acheté]
    E --> F{Déjà noté ?}
    F -->|Oui| G([Message 'Avis déjà déposé'])
    F -->|Non| H[Note 1–5 + commentaire]
    H --> I[(Avis enregistré : 'En attente de modération')]
    I --> J([Merci ! Votre avis sera publié après validation])
    J --> K{Karim modère}
    K -->|Publier| L([Avis visible sur la fiche produit])
    K -->|Masquer| M([Avis non publié])
```

---

## 8. Synthèse des états d'interface à prévoir

| Élément | États à designer | Où |
|---|---|---|
| Bouton | normal, survol, focus clavier, actif, chargement, désactivé | Partout |
| Champ de formulaire | vide, au focus, valide, erreur, message d'aide | Inscription, checkout, produits |
| Carte produit | normale, survol, rupture de stock, nouveau, coup de cœur | Catalogue, accueil |
| Badge commande | En attente (gris), Payée (bleu), En préparation (orange), Expédiée (violet), Livrée (vert), Annulée (rouge) | Espace client, vendeur, admin |
| Notification | succès (vert), erreur (rouge), info (bleu), avertissement (orange) | Toast global |
| Liste vide | illustration + message + action | Catalogue, commandes, avis |
| Chargement | squelette gris (skeleton) | Catalogue, dashboard |

---

## 9. Correspondance flows ↔ écrans du prototype

| Flow | Écrans du prototype |
|---|---|
| Achat | `accueil → catalogue → produit → panier → checkout → confirmation → compte` |
| Recherche | `catalogue` |
| Devenir vendeur | `accueil → vendeurs (vue admin)` |
| Vendeur | `boutique → produits → commandes` |
| Admin | `dashboard → vendeurs → commandes` |
| Paiement | `checkout → confirmation` |
| Avis | `compte → produit` |

---

## 10. Validation

- [x] 7 flows documentés (Mermaid + tableaux)
- [x] Happy path, chemins alternatifs et états d'erreur couverts
- [x] Liens explicites avec les user stories du backlog
- [ ] Flows à importer dans Figma (page `02_Flows`) — voir [prototype_figma.md](prototype_figma.md)
- [ ] Validation jalon 1 (29/11)

# Wireframes basse fidélité — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 (jalon 1) |
| Fidélité | Basse (gris, blocs, pas de couleur définitive) |
| Prototype cliquable | **[Ouvrir prototype_tunishop.html](prototype_tunishop.html)** — bascule « Wireframe / Hi-Fi » + 3 largeurs d'écran |
| UI kit (haute fidélité) | [ui_kit.md](ui_kit.md) — livrable S3 |

---

## 1. Principes de structure

### 1.1 Grille et dimensions

| Support | Largeur | Colonnes | Gouttière | Marge |
|---|---|---|---|---|
| Mobile | 320–639 px | 4 | 16 px | 16 px |
| Tablette | 640–1023 px | 8 | 20 px | 24 px |
| Desktop | ≥ 1024 px | 12 | 24 px | 40 px (max 1440 px) |

### 1.2 Zones récurrentes

```
┌───────────────────────────────────────────────┐
│ HEADER : logo | navigation | recherche | compte | panier │  ← fixe au scroll
├───────────────────────────────────────────────┤
│ FIL D'ARIANE (sauf accueil)                   │
├──────────────────────────┬────────────────────┤
│ CONTENU PRINCIPAL        │ SIDEBAR (filtres   │
│                          │ ou menu back-office)│
├──────────────────────────┴────────────────────┤
│ FOOTER : liens | contact | réseaux | mentions │
└───────────────────────────────────────────────┘
```

### 1.3 Règles de wireframing

- Les blocs gris représentent un contenu (image, texte, donnée) sans le dessiner ;
- Les libellés entre crochets `[…]` sont des **zones fonctionnelles** à nommer dans Figma ;
- Aucune décision de couleur ici : la charte est définie dans l'UI kit ;
- Chaque écran annoté précise : objectif, persona, action principale (CTA), états.

---

## 2. Wireframe 1 — Accueil (desktop)

```
┌──────────────────────────────────────────────────────────────┐
│ [LOGO TuniShop]  Accueil  Catégories  Artisans  À propos      │
│                          [RECHERCHE............] [👤] [🛒 2] │
├──────────────────────────────────────────────────────────────┤
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ [IMAGE HERO — village bleu/blanc, Sidi Bou Saïd]        │ │
│ │   TITRE : L'artisanat tunisien à portée de clic          │ │
│ │   Sous-titre : 100 % local • fait main • livré chez vous │ │
│ │   [ BOUTON : Découvrir le catalogue ] →                  │ │
│ └──────────────────────────────────────────────────────────┘ │
├──────────────────────────────────────────────────────────────┤
│ NOS CATÉGORIES                                               │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐      │
│ │[ICÔNE] │ │[ICÔNE] │ │[ICÔNE] │ │[ICÔNE] │ │[ICÔNE] │      │
│ │Céramiq.│ │Tapis   │ │Parfums │ │Bijoux  │ │Décor   │      │
│ └────────┘ └────────┘ └────────┘ └────────┘ └────────┘      │
├──────────────────────────────────────────────────────────────┤
│ COUPS DE CŒUR DES ARTISANS                     [Voir tout →] │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│ │ [IMAGE]  │ │ [IMAGE]  │ │ [IMAGE]  │ │ [IMAGE]  │         │
│ │ [TITRE]  │ │ [TITRE]  │ │ [TITRE]  │ │ [TITRE]  │         │
│ │ [PRIX]   │ │ [PRIX]   │ │ [PRIX]   │ │ [PRIX]   │         │
│ │ [Atelier]│ │ [Atelier]│ │ [Atelier]│ │ [Atelier]│         │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
├──────────────────────────────────────────────────────────────┤
│ [BANDEAU : Devenir vendeur à Sidi Bou Saïd → inscription]    │
├──────────────────────────────────────────────────────────────┤
│ [FOOTER — liens, contact, réseaux, mentions légales]         │
└──────────────────────────────────────────────────────────────┘
```

**Annotation** : persona Aymen / touriste · CTA principal = « Découvrir le catalogue » · objectif = présentation de la valeur locale et redirection vers le catalogue. Contraste et lisibilité du hero à vérifier (WCAG AA).

---

## 3. Wireframe 2 — Catalogue avec filtres (desktop)

```
┌──────────────────────────────────────────────────────────────┐
│ [HEADER]                                                     │
├──────────────────────────────────────────────────────────────┤
│ Accueil › Catalogue                                          │
│ TITRE : Catalogue des artisans   12 produits                │
├──────────────────┬───────────────────────────────────────────┤
│ FILTRES          │ [RECHERCHE........................] [Tri ▼]│
│ Catégories       │ ┌─────────┐ ┌─────────┐ ┌─────────┐      │
│ ☐ Céramique (8)  │ │[IMAGE]  │ │[IMAGE]  │ │[IMAGE]  │      │
│ ☐ Tapis (4)      │ │[TITRE]  │ │[TITRE]  │ │[TITRE]  │      │
│ ☐ Parfumerie (5) │ │[PRIX]   │ │[PRIX]   │ │[PRIX]   │      │
│ Prix             │ │[★ 4,8]  │ │[★ 4,5]  │ │[★ 5,0]  │      │
│ [───●────] 0-200 │ │[Bouton  │ │[Bouton  │ │[Bouton  │      │
│ Vendeurs         │ │ Voir]   │ │ Voir]   │ │ Voir]   │      │
│ ☐ Atelier Bleu   │ └─────────┘ └─────────┘ └─────────┘      │
│ ☐ Dar Sidi       │ ┌─────────┐ ┌─────────┐ ┌─────────┐      │
│ [BOUTON Filtrer] │ │ [IMAGE] │ │ [IMAGE] │ │ [IMAGE] │      │
│ [Réinitialiser]  │ │  ...    │ │  ...    │ │  ...    │      │
├──────────────────┴───────────────────────────────────────────┤
│                        ‹ 1  2  3 ›                           │
├──────────────────────────────────────────────────────────────┤
│ [FOOTER]                                                     │
└──────────────────────────────────────────────────────────────┘
```

**Annotation** : responsive → sur mobile, les filtres se replient dans un panneau « Filtrer » (dessus de la grille) ; la grille passe de 3 colonnes (tablette) à 2 puis 1 colonne (mobile). État vide prévu avec bouton « Réinitialiser les filtres ».

---

## 4. Wireframe 3 — Fiche produit (desktop)

```
┌──────────────────────────────────────────────────────────────┐
│ [HEADER]                                                     │
├──────────────────────────────────────────────────────────────┤
│ Accueil › Catalogue › Céramique › Vase bleu et blanc         │
├──────────────────────┬───────────────────────────────────────┤
│ ┌──────────────────┐ │ VASE BLEU ET BLANC — ATELIER SIDI BO  │
│ │                  │ │ [★ 4,8 — 12 avis]                     │
│ │  [IMAGE PRINC.]  │ │ PRIX : 48,00 DT TTC                   │
│ │                  │ │ [● ● ●] miniatures                    │
│ └──────────────────┘ │ ┌───────────────────────────────────┐ │
│ ┌──────┐┌──────┐┌───┐ │ │ ● 7 en stock — expédié sous 48 h │ │
│ │[IMG] ││[IMG] ││[+]│ │ │ ● Vendeur validé ✓               │ │
│ └──────┘└──────┘└───┘ │ │ ● Retrait gratuit à l'atelier     │ │
│                      │ └───────────────────────────────────┘ │
│                      │ Quantité [ − 1 + ]                    │
│                      │ [ BOUTON : Ajouter au panier ]        │
│                      │ [ BOUTON : Acheter maintenant ]       │
│                      │ ┌───────────────────────────────────┐ │
│                      │ │ [Description courte du savoir-faire]│ │
│                      │ └───────────────────────────────────┘ │
│                      │ Onglets : Description | Avis | Livraison│
├──────────────────────┴───────────────────────────────────────┤
│ [FOOTER]                                                     │
└──────────────────────────────────────────────────────────────┘
```

**Annotation** : le prix est **TTC** (RG-02) ; le badge de stock est un élément de confiance clé pour Aymen ; le CTA « Ajouter au panier » doit rester visible sur mobile (barre fixe en bas).

---

## 5. Wireframe 4 — Panier (desktop)

```
┌──────────────────────────────────────────────────────────────┐
│ [HEADER]                                                     │
├──────────────────────────────────────────────────────────────┤
│ Accueil › Panier                        VOTRE PANIER (2 articles)│
├────────────────────────────────────┬─────────────────────────┤
│ [IMAGE] Vase bleu et blanc         │ RÉCAPITULATIF           │
│ Atelier Bleu de Sidi Bou           │ Sous-total HT  76,47 DT │
│ Prix : 48,00 DT   Qté [− 1 +]  ✕  │ TVA 19 %       14,53 DT │
│────────────────────────────────────│ Livraison       8,00 DT │
│ [IMAGE] Carreau décoratif          │ ─────────────────────── │
│ Dar Sidi Bou                       │ TOTAL TTC      99,00 DT │
│ Prix : 38,00 DT   Qté [− 1 +]  ✕  │                         │
│                                    │ [CODE PROMO........] [OK]│
│ [ Continuer mes achats ]           │ [ BOUTON : Passer commande ]│
│                                    │ 🔒 Paiement simulé sécurisé│
├────────────────────────────────────┴─────────────────────────┤
│ [FOOTER]                                                     │
└──────────────────────────────────────────────────────────────┘
```

**Annotation** : le détail TVA/livraison répond au besoin de transparence ; le message « livraison offerte dès 100 DT » s'affiche si le seuil est proche (ex. « Plus que 1,00 DT pour la livraison offerte »).

---

## 6. Wireframe 5 — Checkout (desktop, étape 3)

```
┌──────────────────────────────────────────────────────────────┐
│ [HEADER]                                                     │
├──────────────────────────────────────────────────────────────┤
│ ÉTAPES :  ① Panier ✓ ── ② Livraison ✓ ── ③ Paiement (actif)  │
├────────────────────────────────────┬─────────────────────────┤
│ MODE DE PAIEMENT                   │ VOTRE COMMANDE          │
│ ○ Paiement à la livraison (option) │ 2 articles              │
│ ● Carte bancaire (simulation)      │ Sous-total  76,47 DT    │
│                                    │ TVA 19 %    14,53 DT    │
│ [NUMÉRO DE CARTE 4242…]            │ Livraison    8,00 DT    │
│ [MM / AA]  [CVC]                   │ TOTAL       99,00 DT    │
│ ⚠ Mode simulation — aucun débit réel│                        │
│ [ BOUTON : Payer 99,00 DT ]        │ [Modifier le panier]    │
│ [ ← Retour à la livraison ]        │                         │
├────────────────────────────────────┴─────────────────────────┤
│ [FOOTER]                                                     │
└──────────────────────────────────────────────────────────────┘
```

**Annotation** : la barre d'étapes rassure ; le jargon bancaire est évité ; la mention sandbox est visible avant l'action (RG-07).

---

## 7. Wireframe 6 — Tableau de bord administrateur (desktop)

```
┌──────────────────────────────────────────────────────────────┐
│ [LOGO] ADMIN TuniShop        [Recherche] [👤 Karim] [Déconnexion]│
├──────────────┬───────────────────────────────────────────────┤
│ MENU         │ TABLEAU DE BORD — Vue d'ensemble du mois      │
│ ▸ Dashboard  │ ┌────────┐┌────────┐┌────────┐┌────────┐     │
│ ▸ Vendeurs 3 │ │CA      ││Commandes││Vendeurs││Stock   │     │
│ ▸ Commandes  │ │12 480  ││87      ││14      ││faible 6│     │
│ ▸ Produits   │ │DT      ││        ││actifs  ││        │     │
│ ▸ Catégories │ └────────┘└────────┘└────────┘└────────┘     │
│ ▸ Utilisat.  │ ┌───────────────────────────────────────────┐ │
│ ▸ Avis       │ │ [GRAPHique ventes — 7 derniers jours]    │ │
│              │ └───────────────────────────────────────────┘ │
│              │ COMMANDES RÉCENTES          [Tout voir →]    │
│              │ [N°] [Client] [Date] [Total] [Statut ▼] [⋯]  │
│              │ [CMD-2026-0001] [Aymen] [20/11] [99 DT] [Payée]│
│              │ TOP PRODUITS                                 │
│              │ 1. [Vase bleu]  2. [Carreau]  3. [Tapis]     │
├──────────────┴───────────────────────────────────────────────┤
│ [FOOTER ADMIN]                                               │
└──────────────────────────────────────────────────────────────┘
```

**Annotation** : persona Karim · KPI en haut, actions en un clic, badge « 3 » sur le menu Vendeurs pour les demandes en attente · sur tablette, le menu se replie en barre horizontale.

---

## 8. Inventaire des écrans du prototype

| # | Écran | Espace | Persona | CTA principal | Lien |
|---|---|---|---|---|---|
| 1 | Accueil | Public | Aymen / touriste | Découvrir le catalogue | [prototype](prototype_tunishop.html#accueil) |
| 2 | Catalogue + filtres | Public | Aymen | Voir la fiche | [prototype](prototype_tunishop.html#catalogue) |
| 3 | Fiche produit | Public | Aymen | Ajouter au panier | [prototype](prototype_tunishop.html#produit) |
| 4 | Panier | Public | Aymen | Passer commande | [prototype](prototype_tunishop.html#panier) |
| 5 | Checkout | Client | Aymen | Payer | [prototype](prototype_tunishop.html#checkout) |
| 6 | Confirmation | Client | Aymen | Télécharger facture | [prototype](prototype_tunishop.html#confirmation) |
| 7 | Espace client | Client | Aymen | Suivre commande | [prototype](prototype_tunishop.html#compte) |
| 8 | Ma boutique | Vendeur | Fatma | Enregistrer | [prototype](prototype_tunishop.html#boutique) |
| 9 | Mes produits + stocks | Vendeur | Fatma | Nouveau produit | [prototype](prototype_tunishop.html#produits) |
| 10 | Commandes | Vendeur/Admin | Fatma / Karim | Changer statut | [prototype](prototype_tunishop.html#commandes) |
| 11 | Dashboard | Admin | Karim | Consulter KPI | [prototype](prototype_tunishop.html#dashboard) |
| 12 | Validation vendeurs | Admin | Karim | Valider / Refuser | [prototype](prototype_tunishop.html#vendeurs) |

---

## 9. Version mobile (points clés)

| Écran | Adaptation mobile |
|---|---|
| Accueil | Hero pleine largeur, catégories en carrousel horizontal, 2 cartes produits par ligne |
| Catalogue | Filtres dans un panneau coulissant « Filtrer + Trier », grille 2 colonnes |
| Fiche produit | Galerie en carrousel, CTA « Ajouter au panier » fixe en bas d'écran |
| Panier | Lignes en cartes empilées, récapitulatif collant en bas |
| Checkout | Étapes compactes, formulaire en une colonne, bouton large |
| Back-office | Menu latéral remplacé par un menu à onglets en bas, tableaux en cartes |

---

## 10. Validation

- [x] 6 wireframes détaillés en ASCII + 12 écrans dans le prototype HTML
- [x] Annotations objectives / persona / CTA / états
- [x] Variantes mobile documentées
- [ ] Wireframes à transférer sur la page Figma `03_Wireframes` (S2)
- [ ] Validation jalon 1 (29/11)

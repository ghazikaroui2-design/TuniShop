# Plan du prototype Figma — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 |
| Livrable attendu | Prototype interactif haute fidélité, Desktop + Mobile (S3, validation S5) |
| Fichier Figma | `TuniShop_Prototype.fig` (à créer par l'équipe — non générable automatiquement) |
| Référence cliquable immédiate | [prototype_tunishop.html](prototype_tunishop.html) — 12 écrans, 3 largeurs, bascule wireframe/hi-fi |
| Spécification visuelle | [ui_kit.md](ui_kit.md) |
| Structure | [wireframes.md](wireframes.md) |

> Ce document est le **plan de montage** du fichier Figma : il suffit de le suivre pour obtenir un prototype conforme, sans perdre de temps à décider de la structure. Le prototype HTML sert de référence interactive et de solution de repli si Figma n'est pas disponible (risque R-08 du cahier des charges).

---

## 1. Structure du fichier Figma (pages)

| Page | Contenu | Responsable |
|---|---|---|
| `00 — Cover` | Titre du projet, persona cible, version, date, noms de l'équipe | UX/UI |
| `01 — Design System` | Variables couleurs, styles de texte, composants avec variantes | UX/UI |
| `02 — Flows` | Les 7 user flows importés/dessinés (voir `user_flows.md`) | UX/UI + Chef de projet |
| `03 — Wireframes` | Versions basse fidélité des 12 écrans (voir `wireframes.md`) | UX/UI |
| `04 — Front-office` | Accueil, catalogue, fiche produit, panier — Desktop 1440 & Mobile 375 | UX/UI |
| `05 — Espace client` | Checkout, confirmation, espace client — Desktop & Mobile | UX/UI |
| `06 — Espace vendeur` | Boutique, produits/stocks, commandes — Desktop & Mobile | UX/UI |
| `07 — Administration` | Dashboard, validation vendeurs, gestion commandes — Desktop | UX/UI |
| `08 — Responsive` | Vues tablette 768 px des écrans clés | UX/UI |
| `09 — Handoff` | Notes de développement, états, annotations pour l'intégrateur | UX/UI + Front |

---

## 2. Nommage des frames

Convention : `[Espace] - [Écran] - [Support] - [État]`

Exemples :
- `Public - Accueil - Desktop - Default`
- `Public - Catalogue - Mobile - Filtres ouverts`
- `Client - Panier - Desktop - Livraison offerte`
- `Vendeur - Produits - Desktop - Stock faible`
- `Admin - Vendeurs - Desktop - Validation en attente`

Préfixes : `Public-` · `Client-` · `Vendeur-` · `Admin-`.

---

## 3. Inventaire des écrans et interactions du prototype

| ID | Frame | Déclencheur (hotspot) | Destination | Note |
|---|---|---|---|---|
| S01 | `Public - Accueil` | Bouton « Découvrir le catalogue » | S02 | CTA principal |
| S01 | `Public - Accueil` | Catégorie « Céramique » | S02 (filtre actif) | Préfiltrer |
| S01 | `Public - Accueil` | Carte produit | S03 | — |
| S02 | `Public - Catalogue` | Carte produit | S03 | — |
| S02 | `Public - Catalogue` | Bouton « Filtrer » (mobile) | Overlay filtres | État `Filtres ouverts` |
| S03 | `Public - Fiche produit` | « Ajouter au panier » | S04 + toast succès | Incrément badge panier |
| S03 | `Public - Fiche produit` | « Acheter maintenant » | S05 | Court-circuit panier |
| S04 | `Public - Panier` | « Passer commande » | S05 | — |
| S04 | `Public - Panier` | « Continuer mes achats » | S02 | — |
| S05 | `Client - Checkout` | « Payer 99,00 DT » | S06 | Simulation sandbox |
| S05 | `Client - Checkout` | « Retour à la livraison » | Étape 2 (même frame, variant) | — |
| S06 | `Client - Confirmation` | « Télécharger la facture » | Overlay PDF (visuel) | Aucun vrai fichier dans Figma |
| S06 | `Client - Confirmation` | « Suivre ma commande » | S07 | — |
| S07 | `Client - Espace client` | Ligne de commande | Overlay détail statut | — |
| S08 | `Vendeur - Ma boutique` | « Enregistrer » | Toast succès | — |
| S09 | `Vendeur - Produits` | « + Nouveau produit » | Overlay formulaire | — |
| S10 | `Vendeur/Admin - Commandes` | Liste déroulante statut | Mise à jour badge | Transition RG-04 |
| S11 | `Admin - Dashboard` | KPI « Vendeurs actifs » | S12 | — |
| S12 | `Admin - Vendeurs` | « Valider » / « Refuser » | Toast + retrait de la ligne | Refus → champ motif |

---

## 4. Variantes de composants à créer

| Composant | Propriétés / Variantes |
|---|---|
| `Button` | Type = Primary / Secondary / Tertiary / Danger · Size = S/M/L · State = Default/Hover/Focus/Disabled/Loading |
| `ProductCard` | State = Default/Hover/Rupture/Coup de cœur · Support = Desktop/Mobile |
| `Badge` | Label = En attente/Payée/En préparation/Expédiée/Livrée/Annulée/Stock faible |
| `Input` | State = Default/Focus/Error/Disabled · Aide = avec/sans |
| `Navbar` | Espace = Public/Client/Vendeur/Admin · Support = Desktop/Mobile |
| `Toast` | Type = Success/Error/Info/Warning |
| `MenuItem` | State = Default/Actif/Avec notification |
| `StatCard` | Label + Valeur + Tendance (hausse/baisse) |

---

## 5. Annotations à porter sur les frames (handoff)

Pour chaque écran, ajouter un cadre d'annotation contenant :

```text
ÉCRAN : Public - Catalogue - Desktop
OBJECTIF : Permettre à Aymen de trouver un produit local en < 3 clics
PERSONA : Aymen (client)
CTA PRINCIPAL : Voir la fiche produit
ÉTATS : chargement (skeleton) · résultats · aucun résultat · erreur réseau
DONNÉES : products(category_id, price, stock, shop_id), shops.status = 'validé'
RÈGLES : RG-02 prix TTC · RG-06 vendeur validé uniquement
ACCESSIBILITÉ : H1 = "Catalogue des artisans" · filtres avec fieldset/legend · focus visible
```

---

## 6. Procédure de montage (check-list)

1. Créer le fichier `TuniShop_Prototype.fig` et les 10 pages ci-dessus.
2. Dans `01_Design System`, créer les **variables** de `ui_kit.md` puis les composants en variantes.
3. Importer ou redessiner les 7 flows Mermaid dans `02_Flows`.
4. Recopier les 12 wireframes ASCII dans `03_Wireframes` (blocs gris, aucun style).
5. Dupliquer chaque wireframe vers la page d'espace correspondante, appliquer le design system.
6. Créer les versions Mobile (375) et Tablette (768) des écrans clés.
7.Dans l'onglet **Prototype**, relier les frames selon le tableau §3, avec transitions « Smart animate » 300 ms.
8. Ajouter les overlays (filtres, formulaire produit, détail commande, facture).
9. Tester le prototype du début à la fin : accueil → paiement → facture → avis.
10. Exporter une vidéo de démonstration (Loom / OBS) et un PDF des écrans pour le dossier UX.

---

## 7. Critères de validation du prototype Figma

- [ ] 12 écrans minimum, Desktop 1440 **et** Mobile 375 pour les écrans publics/clients ;
- [ ] Design system avec variables et composants réutilisés (aucun texte/couleur en dur) ;
- [ ] Parcours d'achat cliquable jusqu'à la confirmation (S01 → S06) ;
- [ ] Parcours back-office cliquable (validation vendeur → produit → commande) ;
- [ ] États d'erreur et états vides présents ;
- [ ] Annotations handoff sur chaque écran ;
- [ ] Vidéo de démonstration exportée ;
- [ ] Lien Figma partagé (mode « anyone with the link — view ») inséré dans le dossier UX.

---

## 8. Liens utiles

- Prototype HTML cliquable : [prototype_tunishop.html](prototype_tunishop.html)
- Personas : [personas.md](personas.md)
- Flows : [user_flows.md](user_flows.md)
- Wireframes : [wireframes.md](wireframes.md)
- UI kit : [ui_kit.md](ui_kit.md)
- Figma : <https://www.figma.com> · Mermaid : <https://mermaid.live>

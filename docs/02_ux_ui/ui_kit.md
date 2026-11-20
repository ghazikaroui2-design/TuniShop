# UI Kit & Design System — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 (spécification à transférer dans Figma en S3) |
| Identité | « Bleu de Sidi Bou » — bleu chaux, blanc crème et touche d'ocre |
| Principes | Local · Fait main · Clair · Accessible · Mobile-first |
| Lien prototype | [prototype_tunishop.html](prototype_tunishop.html) (bascule Hi-Fi) |

---

## 1. Palette de couleurs

| Rôle | Nom | Hex | Usage |
|---|---|---|---|
| Primaire | Bleu Sidi Bou | `#1D5D8C` | Boutons principaux, liens, en-têtes, focus |
| Primaire clair | Bleu céleste | `#4E9BC7` | Survol, dégradés, illustrations |
| Accent | Ocre soleil | `#D9A441` | Badges « coup de cœur », accents décoratifs (jamais de texte dessus) |
| Fond | Blanc chaux | `#FDFBF6` | Fond des pages |
| Surface | Blanc pur | `#FFFFFF` | Cartes, panneaux, tableaux |
| Texte | Bleu nuit | `#16324A` | Titres et corps de texte |
| Texte secondaire | Gris ardoise | `#5C7488` | Légendes, métadonnées |
| Bordure | Bleu très clair | `#DCE6EE` | Séparateurs, bordures de champs |
| Succès | Vert olive | `#2E9E6B` | Confirmation, statut « Livrée », stock OK |
| Avertissement | Ambre | `#E0A526` | Stock faible, action à faire |
| Erreur | Rouge brique | `#C7443C` | Erreurs de formulaire, statut « Annulée » |
| Info | Bleu info | `#2E86BF` | Messages informatifs |

### Contrastes (WCAG 2.1 AA)

| Combinaison | Ratio estimé | Conforme |
|---|---|---|
| `#16324A` sur `#FFFFFF` | ~12,5:1 | ✅ AAA |
| `#1D5D8C` sur `#FFFFFF` | ~6,7:1 | ✅ AA |
| `#5C7488` sur `#FFFFFF` | ~4,7:1 | ✅ AA (texte normal) |
| `#FFFFFF` sur `#1D5D8C` | ~6,7:1 | ✅ AA |
| `#D9A441` sur `#FFFFFF` | ~2,1:1 | ❌ décoration uniquement |

> Règle : l'ocre sert uniquement de fond à des formes ou de bordure. Pour un texte sur fond ocre, utiliser `#16324A`.

### Dégradé signature

```css
background: linear-gradient(135deg, #1D5D8C 0%, #4E9BC7 100%);
```

Utilisé pour le hero de l'accueil et les bandeaux promotionnels.

---

## 2. Typographie

| Rôle | Police | Fallback | Poids |
|---|---|---|---|
| Titres (H1–H3) | **Playfair Display** | Georgia, serif | 600 / 700 |
| Interface (corps, boutons, tableaux) | **Inter** | Segoe UI, Arial, sans-serif | 400 / 500 / 600 / 700 |
| Données chiffrées | **Inter** | — | 700, `font-variant-numeric: tabular-nums` |

### Échelle

| Niveau | Taille desktop | Taille mobile | Interligne |
|---|---|---|---|
| H1 | 40 px | 28 px | 1,15 |
| H2 | 30 px | 24 px | 1,2 |
| H3 | 22 px | 19 px | 1,3 |
| Corps | 16 px | 16 px | 1,6 |
| Petit texte | 14 px | 14 px | 1,5 |
| Légende | 12 px | 12 px | 1,4 |

> Le corps reste à **16 px minimum** sur mobile (confort de lecture pour Fatma et accessibilité).

---

## 3. Espacement, rayons et ombres

**Grille de base : 4 px.**

| Token | Valeur |
|---|---|
| `space-1` | 4 px |
| `space-2` | 8 px |
| `space-3` | 12 px |
| `space-4` | 16 px |
| `space-6` | 24 px |
| `space-8` | 32 px |
| `space-12` | 48 px |
| `space-16` | 64 px |

| Token | Valeur |
|---|---|
| `radius-sm` | 4 px (champs, badges) |
| `radius-md` | 8 px (cartes, boutons) |
| `radius-lg` | 16 px (panneaux, modales) |
| `radius-pill` | 999 px (tags, statuts) |

| Ombre | Valeur |
|---|---|
| `shadow-sm` | `0 1px 2px rgba(22,50,74,.08)` |
| `shadow-md` | `0 4px 14px rgba(22,50,74,.10)` |
| `shadow-lg` | `0 12px 32px rgba(22,50,74,.14)` |

---

## 4. Breakpoints

| Nom | Min-width | Colonnes |
|---|---|---|
| `mobile` | 320 px | 4 |
| `tablette` | 640 px | 8 |
| `desktop` | 1024 px | 12 |
| `large` | 1440 px | 12 (contenu centré, max 1440 px) |

---

## 5. Composants

### 5.1 Boutons

| Variante | Fond | Texte | Bordure | Usage |
|---|---|---|---|---|
| Primaire | `#1D5D8C` | `#FFFFFF` | aucun | Action principale (Ajouter, Payer, Valider) |
| Secondaire | `#FFFFFF` | `#1D5D8C` | 1 px `#1D5D8C` | Action secondaire (Continuer, Retour) |
| Tertiaire | transparent | `#1D5D8C` | aucun | Liens d'action, « Voir tout » |
| Danger | `#C7443C` | `#FFFFFF` | aucun | Supprimer, Refuser |

**Tailles :** `sm` 36 px · `md` 44 px · `lg` 52 px.  
**États :** survol (éclaircir de 10 %), focus (anneau 3 px `rgba(29,93,140,.25)`), actif (enfoncer 1 px), désactivé (opacité .5, `cursor:not-allowed`), chargement (spinner + libellé « Traitement… »).

```css
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;
     min-height:44px;padding:0 20px;border-radius:8px;font-weight:600;
     font-family:Inter,sans-serif;border:1px solid transparent;cursor:pointer}
.btn-primary{background:#1D5D8C;color:#fff}
.btn-primary:hover{background:#174B72}
.btn:focus-visible{outline:3px solid rgba(29,93,140,.35);outline-offset:2px}
.btn:disabled{opacity:.5;cursor:not-allowed}
```

### 5.2 Cartes produit

```
┌─────────────────────────┐
│  [IMAGE 4:3]     [★]    │  ← badge « Coup de cœur » en ocre
├─────────────────────────┤
│ Catégorie (12 px, gris) │
│ Titre produit (16 px, 600, 2 lignes max) │
│ Atelier (14 px, gris)   │
│ 48,00 DT  (18 px, 700)  │  [+ Panier]   │
└─────────────────────────┘
```

- Fond `#FFFFFF`, bordure `1px #DCE6EE`, rayon 8 px, ombre `shadow-sm` ;
- Survol : `transform: translateY(-3px)` + `shadow-md` + soulignement du titre ;
- Image : `object-fit: cover`, ratio 4:3, `alt` obligatoire ;
- Rupture de stock : image en niveaux de gris + badge « Rupture de stock », bouton désactivé.

### 5.3 Badges de statut (commandes)

| Statut | Fond | Texte |
|---|---|---|
| En attente | `#EEF2F5` | `#5C7488` |
| Payée | `#E3F0FA` | `#1D5D8C` |
| En préparation | `#FDF3DC` | `#8A6210` |
| Expédiée | `#EDE8F8` | `#5B3FA8` |
| Livrée | `#E4F5EC` | `#1D7549` |
| Annulée / Refusée | `#FCE9E7` | `#A3332C` |
| Stock faible | `#FDF3DC` | `#8A6210` |

### 5.4 Champs de formulaire

- Hauteur 44 px, bordure `1px #DCE6EE`, rayon 4 px, padding 12 px ;
- Label au-dessus, gras 14 px, **toujours visible** (pas de placeholder comme seul label) ;
- Focus : bordure `#1D5D8C` + anneau `rgba(29,93,140,.20)` ;
- Erreur : bordure `#C7443C` + message 12 px rouge sous le champ + `aria-invalid="true"` ;
- Aide : texte 12 px gris sous le champ ;
- Champ obligatoire : astérisque + `required` HTML et côté serveur.

### 5.5 Navigation

- Hauteur 72 px desktop / 60 px mobile, fond blanc, ombre `shadow-sm`, `position: sticky; top:0`;
- Logo « TuniShop » en Playfair Display 22 px + mot « Sidi Bou Saïd » en 11 px ;
- Liens actifs : soulignement 2 px `#1D5D8C` ;
- Mobile : menu hamburger, recherche réduite à une icône, panier toujours visible avec compteur ;
- Back-office : menu latéral 240 px desktop, barre d'onglets en bas sur mobile.

### 5.6 Autres composants

| Composant | Spécification |
|---|---|
| **Fil d'Ariane** | `Accueil › Catalogue › Céramique`, 13 px gris, dernier élément en texte fort |
| **Toast** | Coin supérieur droit, 4 variantes (succès/erreur/info/attention), disparition après 5 s, `role="status"` |
| **Modale** | Fond assombri 50 %, panneau `radius-lg`, bouton de fermeture, focus piégé, `Escape` pour fermer |
| **Tableau** | En-tête gris clair, lignes alternées, tri cliquable, pagination en bas, scroll horizontal sur mobile |
| **Pagination** | Boutons 36×36 px, page courante en fond primaire, `aria-current="page"` |
| **Skeleton** | Blocs gris animés pendant le chargement AJAX du catalogue |
| **État vide** | Illustration simple + message + bouton d'action (« Réinitialiser les filtres ») |

### 5.7 Icônes

Bibliothèque recommandée : **Feather Icons** ou **Lucide** (SVG inline, 20×20, `stroke-width: 2`, couleur héritée). Icônes nécessaires :

`search` · `shopping-cart` · `user` · `heart` · `star` · `menu` · `close` · `filter` · `chevron-right` · `chevron-left` · `plus` · `minus` · `trash` · `edit` · `check` · `x` · `download` · `package` · `truck` · `home` · `box` · `users` · `bar-chart` · `shield` · `log-out` · `mail` · `phone` · `map-pin`.

---

## 6. Variables Figma (à créer telles quelles)

```text
Colors/
  primary/blue-900  #16324A
  primary/blue-700  #1D5D8C
  primary/blue-400  #4E9BC7
  accent/ochre      #D9A441
  surface/cream     #FDFBF6
  surface/white     #FFFFFF
  text/secondary    #5C7488
  border/light      #DCE6EE
  feedback/success  #2E9E6B
  feedback/warning  #E0A526
  feedback/error    #C7443C
Typography/
  heading   Playfair Display / 600
  body      Inter / 400
  number    Inter / 700
Spacing/  4 8 12 16 24 32 48 64
Radius/   4 8 16 999
Shadow/   sm md lg
```

---

## 7. Accessibilité (checklist design)

- [x] Contraste ≥ 4,5:1 pour le texte courant, ≥ 3:1 pour les grands titres ;
- [x] Taille de cible tactile ≥ 44 × 44 px ;
- [x] Labels de champs visibles et associés (`for` / `id`) ;
- [x] Focus visible sur tous les éléments interactifs ;
- [x] Alternatives textuelles pour les images (`alt`) ;
- [x] Pas d'information portée uniquement par la couleur (badges avec libellé) ;
- [x] Hiérarchie H1 → H2 → H3 respectée sur chaque écran ;
- [x] Messages d'erreur textuels, pas seulement une bordure rouge.

---

## 8. Livrables Figma attendus (S3)

- [ ] Page `01_Design System` avec les variables ci-dessus et les composants en **variantes** ;
- [ ] Composants `Button`, `ProductCard`, `Badge`, `Input`, `Toast`, `Navbar`, `Footer` ;
- [ ] Styles de texte nommés `H1`, `H2`, `H3`, `Body`, `Caption` ;
- [ ] Prototype avec liens d'interaction entre les 12 écrans ;
- [ ] Export des icônes en SVG ;
- [ ] Version Desktop (1440) et Mobile (375) pour chaque écran clé.

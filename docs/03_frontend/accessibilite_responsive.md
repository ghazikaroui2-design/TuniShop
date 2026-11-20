# Accessibilité & responsive — WCAG 2.1 AA

## 1. Grille et points de rupture

| Support | Largeur | Colonnes | Gouttière | Marge latérale |
|---|---|---|---|---|
| Mobile | 320–639 px | 4 | 16 px | 16 px |
| Tablette | 640–1023 px | 8 | 20 px | 24 px |
| Desktop | ≥ 1024 px | 12 | 24 px | 40 px (max 1440 px) |

Implémentée avec des media queries CSS et des tokens `--gutter` / `--max-width`. Aucune largeur fixe n'empêche le zoom à 200 %.

### Comportements par largeur

| Élément | < 640 px | 640–1023 px | ≥ 1024 px |
|---|---|---|---|
| Navigation | burger `data-nav-toggle` | burger | liens horizontaux |
| Recherche | pleine largeur, 4ᵉ rangée | pleine largeur | 360 px max, alignée à droite |
| Héros | une colonne | une colonne | deux colonnes |
| Grille produits | 2 colonnes | auto-fill | 4 colonnes |
| Menu back-office | onglets `.bo-tabs` | onglets | colonne latérale 240 px |
| Tableaux | cartes avec `data-label` | défilement horizontal | tableau complet |
| Panneau filtres | repliable | visible | visible |
| CTA fiche produit | barre fixe basse | formulaire | formulaire |

`prefers-reduced-motion: reduce` désactive transitions, animations et scroll fluide.

## 2. Critères WCAG 2.1 AA couverts

| Critère | Dispositif TuniShop |
|---|---|
| 1.1.1 Textes alternatifs | `role="img"` + `aria-label` sur les illustrations ; `alt` issu de `product_images.alt_text` quand le fichier existe |
| 1.3.1 Structure | `header`, `nav`, `main`, `section`, `aside`, `footer`, `h1` → `h3`, `th scope="col"` |
| 1.3.2 Séquence logique | DOM ordonné ; la navigation mobile ne réordonne pas le contenu |
| 1.4.1 Utilisation de la couleur | statuts doublés par un texte explicite (jamais la couleur seule) |
| 1.4.3 Contraste | bleu nuit `#16324A` sur blanc crée un ratio > 7:1 ; bleu Sidi Bou > 4,5:1 |
| 1.4.4 Redimensionnement | tailles en `rem`/`%`, pas de `height` fixe sur les conteneurs de texte |
| 1.4.10 Reflow | cartes `minmax(0, 1fr)`, tableaux en cartes sous 640 px, pas de défilement horizontal de page |
| 1.4.11 Contraste non textuel | bordures de champs `#DCE6EE` renforcées au focus par une bague bleue |
| 1.4.12 Espacement du texte | `line-height: 1.6`, marges en tokens |
| 2.1.1 Clavier | tous les contrôles sont `<a>`, `<button>` ou champs de formulaire ; `tabindex="-1"` sur `main` pour le skip link |
| 2.1.2 Pas de piège | menu Escape ferme le toggle et rend le focus ; onglets flèches/Home/End |
| 2.4.1 Aller au contenu | `.skip-link` visible au focus |
| 2.4.3 Ordre de focus | ordre DOM respecté ; CTA mobile utilise `form="add-to-cart-form"` sans dupliquer le champ |
| 2.4.6 Intitulés | `aria-label` sur les liens iconiques, `aria-current="page"`, libellés associés aux champs |
| 2.4.7 Focus visible | `:focus-visible` avec bague de 3 px et `outline-offset: 2px` |
| 3.3.1/3.3.2 Erreurs et libellés | `label for`, `aria-describedby`, `role="alert"` sur les messages d'erreur |
| 3.3.3 Aide à la correction | hint sous le mot de passe, indicating de force, motif de refus explicite |
| 4.1.2 Nom, rôle, valeur | `role="tablist"/"tab"/"tabpanel"`, `aria-selected`, `aria-expanded`, `aria-live="polite"` |
| 4.1.3 Messages de statut | toasts `role="status"`/`alert`, pile `aria-live="polite"` |

## 3. Tableau responsive

Sous 640 px, l'en-tête est masqué visuellement (toujours présent pour les lecteurs d'écran) et chaque cellule porte son libellé :

```html
<td data-label="Total TTC">94,00 DT</td>
```

```css
td::before { content: attr(data-label); }
```

Le contrôle `.table-scroll` reçoit `tabindex="0"`, `role="region"` et un `aria-label` via `app.js`, afin que le défilement horizontal soit accessible au clavier sur tablette.

## 4. Test manuel recommandé

1. Tabuler depuis le haut de page : le lien « Aller au contenu principal » doit apparaître.
2. Ouvrir le menu mobile avec `Entrée`, le fermer avec `Échap`.
3. Sur la fiche produit, naviguer entre les onglets avec `←`/`→` puis `Home`/`End`.
4. Agrandir à 200 % à 320 px : aucun texte tronqué, pas de défilement horizontal.
5. Couper JavaScript : tous les formulaires et liens restent fonctionnels.

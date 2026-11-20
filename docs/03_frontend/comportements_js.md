# Comportements JavaScript — hooks `data-*`

`public/assets/js/app.js` est le **seul** fichier JavaScript chargé. Il est inclus avec `defer` dans `app/Views/layouts/main.php` : compatible CSP `script-src 'self'`, sans `eval`, sans `innerHTML` et sans attribut `on*`.

## 1. Principe d'amélioration progressive

Le HTML est autonome : les quantités se modifient via un formulaire POST, les filtres via un GET, les onglets affichent leur contenu par défaut. Le JS ne fait qu'améliorer l'ergonomie. Si le script ne charge pas, l'application reste utilisable.

## 2. Catalogue des hooks

| Hook | Élément | Comportement |
|---|---|---|
| `data-base-url` | `<body>` | Préfixe des appels `fetch` (chemin de base configuré) |
| `data-nav-toggle` | bouton burger | Ouvre/ferme `#primary-nav`, met à jour `aria-expanded`, fermeture avec `Échap` |
| `data-toast="success\|error"` | messages flash | Déplacement vers `#toast-stack`, fermeture manuelle, disparition après 5 s |
| `data-qty` | conteneur de quantité | Regroupe les boutons `data-qty-step` et l'`<input type="number">` |
| `data-qty-step="-1\|1"` | bouton +/− | Incrémente en respectant `min` et `max`, émet un événement `input` |
| `data-filter-toggle` | bouton mobile | Affiche/masque le panneau ciblé par `aria-controls` |
| `data-live-search` | champ de recherche | `fetch('GET /api/produits?q=…')` avec debounce 220 ms et `AbortController` |
| `data-card-input` | numéro de carte (sans `name`) | Formatage local en groupes de 4 chiffres, indication carte de test |
| `data-expiry-input` | expiration (sans `name`) | Formatage `MM/AA` |
| `data-cvc-input` | CVC (sans `name`) | Chiffres uniquement, 4 max |
| `data-card-hint` | message sandbox | Retour visuel `aria-live="polite"` |
| `data-password-meter-input` | mot de passe | Score 0→4 (longueur, casse, chiffre, symbole), indicatif uniquement |
| `data-password-meter-bar` / `data-password-meter-label` | jauge / libellé | Mise à jour du score et du texte |
| `data-tabs` | conteneur d'onglets | Activation au clic, navigation `←`/`→`/`Home`/`End`, `aria-selected` et `hidden` |
| `data-confirm="…"` | formulaire destructif | `window.confirm()` avant soumission (suppression d'un article du panier) |
| `.table-scroll` | conteneur de tableau | Ajout de `tabindex="0"`, `role="region"`, `aria-label` |

## 3. Recherche instantanée

```js
fetch(baseUrl + '/api/produits?q=' + encodeURIComponent(query), {
  method: 'GET',
  headers: { Accept: 'application/json', 'X-Requested-With': 'fetch' },
  credentials: 'same-origin',
  signal: controller.signal
});
```

- Même origine uniquement (`connect-src 'self'`).
- Aucune donnée bancaire, aucune information personnelle envoyée.
- Les résultats sont construits avec `textContent` (aucune injection HTML).
- En cas d'erreur réseau, la liste est simplement masquée : la recherche classique via `GET /catalogue?q=` reste disponible.

## 4. RG-07 : carte sandbox

Les trois champs bancaires du checkout n'ont **pas d'attribut `name`** : le navigateur ne les soumet donc pas. Le serveur ne reçoit que `confirm_sandbox=1` et le code promo éventuel. Le formatage est purement local et visuel.

> Le JS n'a aucun accès à un endpoint de paiement externe. Le paiement est entièrement simulé côté serveur (`PaymentService`).

## 5. Sécurité et compatibilité

- ES5 + API DOM modernes raisonnables (`closest`, `fetch`, `AbortController`, `matchMedia`) : aucun polyfill nécessaire pour les navigateurs cibles (Chrome/Edge/Firefox récents).
- Aucune dépendance npm/CDN : `composer audit` reste à `No security vulnerability advisories found`.
- Aucune chaîne HTML construite à partir de données utilisateur : `el(tag, class, text)` utilise `textContent`.
- `prefers-reduced-motion` est lu au démarrage pour adapter la disparition des toasts.

# Recette frontend — F01 à F12

Exécution :

```powershell
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' test:frontend
```

Prérequis : base `tunishop` importée (les tests F11/F12 appellent le routeur). Le test est sans effet de bord sur les données.

## Scénarios

| ID | Vérification | Attendu |
|---|---|---|
| **F01** | Présence des 12 vues du prototype | 12 fichiers PHP existants (accueil, catalogue ×2, panier, checkout, confirmation, compte, boutique, dashboard, articles, auth ×2) |
| **F02** | Layout accessible | `<html lang="fr">`, viewport, skip link, CSS externe, JS en `defer`, `data-base-url`, `#toast-stack` |
| **F03** | CSP — aucun script inline | Aucun `<script>` avec contenu ni attribut `on*` dans les vues ; seul le `<script src … defer>` du layout |
| **F04** | En-têtes de sécurité | `Content-Security-Policy` contient `script-src 'self'` et aucun `'unsafe-inline'` dans `script-src` |
| **F05** | Hooks JS externes | `app.js` contient les 12 hooks attendus (navigation, toasts, quantités, filtres, recherche, carte, mot de passe, onglets, confirmation, tableaux) |
| **F06** | Design system responsive | tokens CSS, breakpoints 640/1024 px, `:focus-visible`, `prefers-reduced-motion`, media query max 639 px, `@media print` |
| **F07** | RG-07 sur le checkout | `4242 4242 4242 4242`, `ARTISAN5`, `BIENVENUE10`, `confirm_sandbox`, aucun `name="card_*"` ni `name="cvc"` |
| **F08** | Catalogue et fiche produit | filtres GET, état vide, `role="tablist"`, 3 `role="tab"`, fallback image `exists`/`src`, fil d'Ariane |
| **F09** | Panier et commande | `.qty`, `data-confirm`, récapitulatif HT/TVA/TTC, 3 étapes, CTA mobile `form="add-to-cart-form"` |
| **F10** | Tableaux mobiles | `data-label` present dans `dashboard/index.php` et `account/index.php`, `.table-scroll`, `th scope="col"` |
| **F11** | Routes frontend | 26 routes ; `GET /compte` avec `auth` ; `GET /vendeur/boutique` avec `role:vendeur` ; `POST` avec `csrf` |
| **F12** | Rendu HTTP des 12 écrans | Les 12 vues se rendent via le routeur avec la session adaptée (client, vendeur, admin) et contiennent le fil d'Ariane/titre attendu |

## Résultat attendu

```
=== 12 PASS / 0 FAIL ===
```

## Recette manuelle complémentaire

1. `php -S localhost:8000 -t public` puis parcourir les 12 écrans à 320, 640 et 1024 px.
2. Se connecter avec `aymen.benali@tunishop.tn` / `Test1234!` et vérifier `/compte`.
3. Se connecter comme vendeur puis modifier `/vendeur/boutique` (enregistrement réel en base).
4. Se connecter comme admin puis valider/refuser une boutique en attente.
5. Passer une commande avec la carte `4242 4242 4242 4242` et télécharger la facture PDF.
6. Couper JavaScript et refaire le parcours : tout doit rester fonctionnel.

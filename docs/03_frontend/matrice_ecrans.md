# Matrice des 12 écrans — prototype → MVC

| # | Écran prototype | Vue MVC | Route | Contrôleur @ action | Données clés |
|---|---|---|---|---|---|
| 1 | Accueil (`#accueil`) | `app/Views/home/index.php` | `GET /` | `HomeController@index` | catégories actives, 6 coups de cœur |
| 2 | Catalogue (`#catalogue`) | `app/Views/catalog/index.php` | `GET /catalogue` | `CatalogController@index` | `v_catalogue`, filtres q/catégorie/prix |
| 3 | Fiche produit (`#produit`) | `app/Views/catalog/show.php` | `GET /produit/{slug}` | `CatalogController@show` | produit, images, stock, Avis/Livraison |
| 4 | Panier (`#panier`) | `app/Views/cart/index.php` | `GET /panier` | `CartController@index` | lignes, `v_cart_totals`, CSRF |
| 5 | Checkout (`#checkout`) | `app/Views/checkout/index.php` | `GET\|POST /commande` | `OrderController@checkout` / `@place` | étape 1/2, adresses, totals |
| 6 | Confirmation (`#confirmation`) | `app/Views/order/confirmation.php` | `GET /commande/{id}` | `OrderController@confirmation` | commande, facture, historique RG-04 |
| 7 | Espace client (`#compte`) | `app/Views/account/index.php` | `GET /compte` | `AccountController@index` | commandes, adresses, avis, dépenses |
| 8 | Ma boutique (`#boutique`) | `app/Views/seller/shop.php` | `GET\|POST /vendeur/boutique` | `SellerController@shopForm` / `@shopUpdate` | boutique du vendeur, statut RG-06 |
| 9 | Produits & stocks (`#produits`) | `app/Views/dashboard/index.php` | `GET /vendeur/produits` | `SellerController@products` | catalogue + niveaux de stock |
| 10 | Commandes (`#commandes`) | `app/Views/dashboard/index.php` | `GET /vendeur/commandes` | `SellerController@orders` | `v_orders_detail` |
| 11 | Dashboard admin (`#dashboard`) | `app/Views/dashboard/index.php` | `GET /admin` | `AdminController@dashboard` | `sp_get_dashboard_stats`, graphique 7 jours |
| 12 | Validation vendeurs (`#vendeurs`) | `app/Views/dashboard/index.php` | `GET /admin/vendeurs` | `AdminController@shops` | boutiques en attente, désécision RG-06 |

## Écrans transverses

| Écran | Vue | Route | Remarque |
|---|---|---|---|
| Connexion | `auth/login.php` | `GET /connexion` | compte démo, throttle 5/15 min |
| Inscription | `auth/register.php` | `GET /inscription` | indicateur de force du mot de passe |
| Facture PDF | `invoices/pdf.php` | `GET /commande/{id}/facture.pdf` | gabarit DomPDF, hors design system |
| État du service | `SystemController` | `GET /sante` | JSON, non visuel |

## Découpage du back-office

La vue unique `dashboard/index.php` est paramétrée par le contrôleur :

| Variable | Rôle | Valeurs |
|---|---|---|
| `$context` | Libellé du périmètre | `Vendeur`, `Administrateur` |
| `$boContext` | Jeu de menu | `seller`, `admin` |
| `$activeMenu` | Entrée mise en évidence | `dashboard`, `boutique`, `produits`, `commandes`, `vendeurs` |
| `$stats` | KPI | tableau associatif issu des procédures stockées |
| `$chart` | Série 7 jours | liste de `['label' => 'L', 'value' => 3]` |
| `$rows` / `$columns` | Tableau | lignes et colonnes BDD |
| `$pending` | Mode décision | `true` sur la validation des vendeurs |
| `$csrf` | Jeton | obligatoire pour les formulaires de décision |

## Traçabilité des choix

- Les écrans 9 à 12 partagent une seule vue : même structure `.bo-shell`, même tableau responsive, même logique de badges. Cela évite quatre copies divergentes.
- L'écran 7 est une nouvelle vue dédiée car son information est centrée sur le client (commandes, factures, avis, adresses) et non sur la gestion.
- L'écran 8 dispose de sa propre vue avec un formulaire réel (POST + CSRF + validation serveur) et un bandeau de statut de validation.
- Aucun lien mort n'est laissé dans les menus : seules les routes existantes sont proposées.

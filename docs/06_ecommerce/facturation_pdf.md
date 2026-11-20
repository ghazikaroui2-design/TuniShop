# TuniShop — Facturation PDF (FR-26, RG-08)

> La facture est créée **côté base de données** au moment du paiement, puis mise en forme et téléchargée via DomPDF côté application.

---

## 1. Numérotation (RG-08)

```text
F-YYYY-NNNN
│   │   └─ séquence annuelle à 4 chiffres (fn_next_sequence('INVOICE', année))
│   └───── année de la commande
└───────── préfixe « Facture »
```

Exemple : `F-2026-0001`.

| Élément | Responsable |
|---|---|
| Séquence | Table `sequences` + fonction `fn_next_sequence()` |
| Création de la ligne `invoices` | Trigger `trg_orders_au` lors du premier passage de `orders.status` à `payée` |
| Montants | Copiés depuis `orders` : HT, TVA, livraison, remise, TTC |
| Unicité | `UNIQUE KEY uq_invoices_number`, `UNIQUE KEY uq_invoices_order` |
| PDF | `InvoiceService` (application) |

> Ainsi, une facture ne peut **jamais** être générée pour une commande en `en_attente`, et deux paiements ne produisent pas deux factures.

---

## 2. Dépendance DomPDF

```powershell
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' require dompdf/dompdf:^3.1.6
```

| Élément | Version / valeur |
|---|---|
| `dompdf/dompdf` | **3.1.6** (version la plus récente non frappée d'avis de sécurité au 20/11/2026) |
| Dépendances | `php-font-lib`, `php-svg-lib`, `masterminds/html5`, `php-css-parser` |
| Extensions PHP requises | `dom`, `mbstring` (présentes sur XAMPP) |
| Autoload | PSR-4 via `vendor/autoload.php` |
| Lock file | `composer.lock` versionné |

> ⚠️ Les versions `3.1.0` à `3.1.5` sont bloquées par Composer pour raisons de sécurité. Ne pas les utiliser.

---

## 3. Service `InvoiceService`

| Méthode | Rôle |
|---|---|
| `data(int $orderId)` | Récupère commande + client + facture + lignes + artisans (jointures SQL) |
| `renderHtml(int $orderId)` | Rend `app/Views/invoices/pdf.php` **sans layout** |
| `renderPdf(int $orderId)` | `Dompdf::loadHtml()` → `setPaper('A4')` → `render()` → `output()` |
| `save(int $orderId)` | Écrit `public/factures/F-YYYY-NNNN.pdf` et met à jour `invoices.pdf_path` |
| `filename(int $orderId)` | Nom proposé au téléchargement |

Options DomPDF utilisées :

```php
$options->set('isRemoteEnabled', false);        // aucune ressource externe
$options->set('defaultFont', 'DejaVu Sans');    // accents français
$options->set('isFontSubsettingEnabled', true); // PDF plus léger
```

---

## 4. Contenu du gabarit `app/Views/invoices/pdf.php`

| Section | Informations |
|---|---|
| En-tête | Logo texte TuniShop, adresse Sidi Bou Saïd, numéro et date de facture |
| Facturé à | Nom, email, adresse de livraison (ou adresse invitée) |
| Commande | Numéro `CMD-YYYY-NNNN`, date, mode de livraison, badge « Payée » |
| Articles | Produit, artisan, PU TTC, quantité, total TTC par ligne |
| Totaux | Sous-total HT, TVA 19 %, livraison, remise éventuelle, **TOTAL TTC** |
| Paiement | Référence `SBX-…` et rappel RG-07 |
| Pied de page | Mentions légales courtes, numérotation RG-08 |

Toutes les valeurs passent par `View::e()` et `number_format(..., ',', ' ')` : affichage français, aucune injection HTML possible.

---

## 5. Stockage et téléchargement

```text
public/
└── factures/            ← ignoré par Git (*.pdf)
    ├── .gitkeep         ← seul fichier versionné
    └── F-2026-0001.pdf  ← généré à la demande
```

| Aspect | Choix |
|---|---|
| Emplacement | `public/factures/` accessible directement, mais non versionné |
| `invoices.pdf_path` | `factures/F-2026-0001.pdf` (chemin relatif) |
| Route | `GET /commande/{id}/facture.pdf` → `OrderController::invoice()` |
| En-têtes | `Content-Type: application/pdf`, `Content-Disposition: attachment`, `Content-Length`, `X-Content-Type-Options: nosniff` |
| Autorisation | Client propriétaire de la commande **ou** administrateur |
| Commande en attente | `ForbiddenException` : « La facture est disponible après paiement » |
| Commande d'autrui | `NotFoundException` neutre (404), sans révéler l'existence de la commande |

---

## 6. Parcours utilisateur

1. Le client passe commande et simulé le paiement.
2. La page de confirmation affiche le numéro de facture et un bouton **📄 Télécharger la facture PDF**.
3. Le clic appelle `/commande/1/facture.pdf`.
4. Le contrôleur vérifie les droits, génère/écrase le PDF, met à jour `pdf_path`, puis renvoie le fichier en pièce jointe.

---

## 7. Tests associés

| Test | Vérification |
|---|---|
| E11 | Le HTML contient `F-2026-0001`, `72,27 DT`, `13,73 DT`, `94,00 DT` et la référence `SBX-` |
| E12 | `renderPdf(1)` commence par `%PDF-` et dépasse 2 Ko |
| E13 | `GET /commande/1/facture.pdf` renvoie 200 + `application/pdf` + nom de fichier correct |
| E14 | `invoices.pdf_path` est renseigné après génération |
| E15 | Un autre client reçoit une 404 (pas de fuite d'information) |

Commande : `composer test:ecommerce` → **15 PASS / 0 FAIL**.

---

## 8. Limites et évolutions

- Le PDF est régénéré à chaque téléchargement ; un cache avec date d'émission permettrait d'éviter l'écriture répétée.
- Les images de produits ne sont pas intégrées au PDF (gabarit volontairement léger et rapide).
- Le numéro de facture n'est pas ré-attribué après annulation : la séquence reste monotone, ce qui est conforme à la réglementation comptable.
- Un tampon et une signature électronique seraient nécessaires pour une facture opposable en droit ; hors périmètre du projet BTS.

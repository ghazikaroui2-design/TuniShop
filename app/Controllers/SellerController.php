<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Product;
use App\Models\Shop;
use App\Services\AuditService;
use App\Services\InputSanitizer;

/** Espace vendeur (FR-16, FR-18 à FR-21) — protégé par role:vendeur. */
final class SellerController extends Controller
{
    private function currentShopId(): int
    {
        $shop = (new Shop($this->database))->where(['owner_id' => $this->session->userId()], 1)[0] ?? null;

        return $shop === null ? 0 : (int) $shop['id'];
    }

    public function dashboard(Request $request): Response
    {
        $shopId = $this->currentShopId();
        $start = date('Y-m-01');
        $end = date('Y-m-t');

        $stats = $shopId > 0
            ? $this->database->fetch('CALL sp_get_seller_stats(?, ?, ?)', [$shopId, $start, $end])
            : null;

        return $this->render('dashboard/index', [
            'pageTitle'       => 'Tableau de bord vendeur',
            'pageDescription' => 'Suivez les ventes, le stock et les commandes de votre boutique TuniShop.',
            'breadcrumb'      => ['Espace vendeur' => null],
            'context'         => 'Vendeur',
            'boContext'       => 'seller',
            'activeMenu'      => 'dashboard',
            'stats'           => $stats ?? [],
            'rows'            => (new Product($this->database))->lowStock(),
            'columns'         => ['product_name', 'shop_name', 'stock_qty', 'low_stock_threshold'],
            'listTitle'       => 'Produits en stock faible',
        ]);
    }

    public function products(Request $request): Response
    {
        $rows = (new Product($this->database))->all(50, 0, 'id DESC');

        return $this->render('dashboard/index', [
            'pageTitle'       => 'Mes produits',
            'pageDescription' => 'Gérez le catalogue et les niveaux de stock de votre boutique.',
            'breadcrumb'      => ['Espace vendeur' => '/vendeur', 'Produits' => null],
            'context'         => 'Vendeur',
            'boContext'       => 'seller',
            'activeMenu'      => 'produits',
            'stats'           => [],
            'rows'            => $rows,
            'columns'         => ['id', 'name', 'price_ttc', 'stock_qty', 'status'],
            'listTitle'       => 'Catalogue de la boutique',
        ]);
    }

    public function orders(Request $request): Response
    {
        return $this->render('dashboard/index', [
            'pageTitle'       => 'Mes commandes',
            'pageDescription' => 'Consultez les commandes passées auprès de votre atelier.',
            'breadcrumb'      => ['Espace vendeur' => '/vendeur', 'Commandes' => null],
            'context'         => 'Vendeur',
            'boContext'       => 'seller',
            'activeMenu'      => 'commandes',
            'stats'           => [],
            'rows'            => $this->database->fetchAll('SELECT * FROM v_orders_detail LIMIT 50'),
            'columns'         => ['order_number', 'placed_at', 'first_name', 'item_count', 'total_ttc', 'status'],
            'listTitle'       => 'Commandes récentes',
        ]);
    }

    /** Écran 8 — Présentation et coordonnées de la boutique. */
    public function shopForm(Request $request): Response
    {
        $shop = (new Shop($this->database))->where(['owner_id' => $this->session->userId()], 1)[0] ?? null;
        if ($shop === null) {
            throw new NotFoundException('Aucune boutique n’est associée à ce compte vendeur.');
        }

        return $this->render('seller/shop', [
            'pageTitle'       => 'Ma boutique',
            'pageDescription' => 'Renseignez les coordonnées et la présentation de votre atelier à Sidi Bou Saïd.',
            'breadcrumb'      => ['Espace vendeur' => '/vendeur', 'Ma boutique' => null],
            'context'         => 'Vendeur',
            'boContext'       => 'seller',
            'activeMenu'      => 'boutique',
            'shop'            => $shop,
            'csrf'            => $this->session->csrfToken(),
        ]);
    }

    /** Enregistrement des modifications (CSRF appliqué par le routeur). */
    public function shopUpdate(Request $request): Response
    {
        $shop = (new Shop($this->database))->where(['owner_id' => $this->session->userId()], 1)[0] ?? null;
        if ($shop === null) {
            throw new NotFoundException('Aucune boutique n’est associée à ce compte vendeur.');
        }

        $name = InputSanitizer::label($request, 'name', 120);
        $address = InputSanitizer::label($request, 'address', 255);
        $neighborhood = InputSanitizer::label($request, 'neighborhood', 100);
        $phone = InputSanitizer::label($request, 'phone', 30);
        $email = InputSanitizer::email($request, 'email');
        $description = InputSanitizer::string($request, 'description', 2000);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Le nom de l’atelier est obligatoire.';
        }
        if ($address === '') {
            $errors['address'] = 'L’adresse est obligatoire.';
        }
        if ($neighborhood === '') {
            $errors['neighborhood'] = 'Le quartier est obligatoire.';
        }
        if ($phone === '') {
            $errors['phone'] = 'Un téléphone de contact est obligatoire.';
        }
        if ($email === '') {
            $errors['email'] = 'Une adresse email valide est obligatoire.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors, 'Boutique invalide.');
        }

        $data = [
            'name'         => $name,
            'address'      => $address,
            'neighborhood' => $neighborhood,
            'phone'        => $phone,
            'email'        => $email,
            'description'  => $description !== '' ? $description : null,
        ];
        if ($name !== (string) $shop['name']) {
            // Le suffixe garantit l'unicité de la clé uq_shops_slug.
            $slug = mb_strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name) ?? '', '-'));
            $data['slug'] = ($slug !== '' ? $slug : 'boutique') . '-' . (int) $shop['id'];
        }

        (new Shop($this->database))->update((int) $shop['id'], $data);
        AuditService::log('SHOP_UPDATED', 'shops', (string) $shop['id'], $name);
        $this->session->flash('success', 'Les informations de votre boutique ont été enregistrées.');

        return $this->redirect('/vendeur/boutique');
    }
}

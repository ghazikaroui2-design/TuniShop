<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\View;
use App\Exceptions\NotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

/**
 * Génération des factures PDF (FR-26, RG-08).
 *
 * La numérotation F-YYYY-NNNN est produite par le trigger SQL trg_orders_au
 * au moment du passage en « payée ». Ce service ne fait que mettre en forme
 * et enregistrer le PDF ; il ne recalcule aucun montant.
 */
final class InvoiceService
{
    public function __construct(
        private readonly Database $database,
        private readonly View $view,
        private readonly string $storageDir,
    ) {
    }

    /** @return array<string, mixed> Données complètes de la facture */
    public function data(int $orderId): array
    {
        $order = $this->database->fetch(
            'SELECT o.*, u.first_name, u.last_name, u.email,'
            . ' i.id AS invoice_id, i.invoice_number, i.issued_at, i.pdf_path,'
            . ' i.subtotal_ht AS inv_subtotal_ht, i.vat_amount AS inv_vat_amount,'
            . ' i.shipping_fee AS inv_shipping_fee, i.discount_amount AS inv_discount_amount,'
            . ' i.total_ttc AS inv_total_ttc'
            . ' FROM orders o'
            . ' LEFT JOIN users u ON u.id = o.user_id'
            . ' LEFT JOIN invoices i ON i.order_id = o.id'
            . ' WHERE o.id = ? LIMIT 1',
            [$orderId]
        );

        if ($order === null) {
            throw new NotFoundException('Commande introuvable.');
        }
        if (empty($order['invoice_number'])) {
            throw new NotFoundException('Aucune facture n’a été générée pour cette commande.');
        }

        $items = $this->database->fetchAll(
            'SELECT oi.product_name, oi.product_slug, oi.unit_price_ttc,'
            . ' oi.quantity, oi.line_total_ttc, s.name AS shop_name,'
            . ' ROUND(oi.line_total_ttc / 1.19, 2) AS line_total_ht'
            . ' FROM order_items oi JOIN shops s ON s.id = oi.shop_id'
            . ' WHERE oi.order_id = ? ORDER BY oi.id',
            [$orderId]
        );

        return ['order' => $order, 'items' => $items];
    }

    /** HTML de la facture, sans layout applicatif. */
    public function renderHtml(int $orderId): string
    {
        return $this->view->render('invoices/pdf', $this->data($orderId), null);
    }

    /** Binaire PDF (ne touchant pas au système de fichiers). */
    public function renderPdf(int $orderId): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->renderHtml($orderId), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Écrit le PDF dans public/factures/ et renseigne invoices.pdf_path.
     *
     * @return string Chemin absolu du fichier généré
     */
    public function save(int $orderId): string
    {
        $data = $this->data($orderId);
        $number = (string) $data['order']['invoice_number'];
        $filename = str_replace([' ', '/', '\\'], '_', $number) . '.pdf';

        if (!is_dir($this->storageDir)
            && !mkdir($this->storageDir, 0775, true)
            && !is_dir($this->storageDir)
        ) {
            throw new RuntimeException('Impossible de créer le dossier des factures.');
        }

        $path = rtrim($this->storageDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
        if (file_put_contents($path, $this->renderPdf($orderId)) === false) {
            throw new RuntimeException('Échec de l’écriture de la facture ' . $number . '.');
        }

        $this->database->execute(
            'UPDATE invoices SET pdf_path = ? WHERE order_id = ?',
            ['factures/' . $filename, $orderId]
        );

        return $path;
    }

    /** Nom de fichier proposé au téléchargement. */
    public function filename(int $orderId): string
    {
        $number = (string) ($this->data($orderId)['order']['invoice_number'] ?? 'facture');

        return str_replace([' ', '/', '\\'], '_', $number) . '.pdf';
    }
}

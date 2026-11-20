<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Facture <?= $this->e($order['invoice_number']) ?></title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; color: #16324a; font-size: 10px; margin: 0; }
    h1 { font-size: 20px; margin: 0 0 4px; color: #1d5d8c; }
    h2 { font-size: 12px; margin: 18px 0 6px; color: #1d5d8c; }
    .head { border-bottom: 3px solid #d9a441; padding-bottom: 12px; margin-bottom: 16px; }
    .muted { color: #5c7488; }
    .row { display: flex; justify-content: space-between; gap: 20px; }
    .col { width: 48%; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { padding: 6px 4px; border-bottom: 1px solid #dce6ee; text-align: left; }
    th { background: #eef2f5; font-size: 9px; text-transform: uppercase; color: #5c7488; }
    .right { text-align: right; }
    .totals { margin-left: auto; width: 55%; margin-top: 12px; }
    .totals td { border: 0; padding: 3px 4px; }
    .totals .grand { font-size: 13px; font-weight: bold; color: #1d5d8c; border-top: 2px solid #1d5d8c; }
    .badge { display: inline-block; background: #eaf7f0; color: #2e9e6b; padding: 3px 8px; border-radius: 10px; font-weight: bold; }
    .footer { margin-top: 26px; border-top: 1px solid #dce6ee; padding-top: 8px; font-size: 8px; color: #5c7488; }
    .notice { background: #fff8e8; border-left: 3px solid #d9a441; padding: 8px; margin-top: 12px; }
  </style>
</head>
<body>

<div class="head">
  <div class="row">
    <div>
      <h1>TuniShop</h1>
      <div class="muted">Marketplace de l’artisanat tunisien — Sidi Bou Saïd</div>
    </div>
    <div class="right">
      <strong>FACTURE</strong><br>
      <span style="font-size:14px;font-weight:bold"><?= $this->e($order['invoice_number']) ?></span><br>
      <span class="muted">Émise le <?= $this->e(date('d/m/Y', strtotime((string) $order['issued_at']))) ?></span>
    </div>
  </div>
</div>

<div class="row">
  <div class="col">
    <h2>Facturé à</h2>
    <?php if (!empty($order['user_id'])) : ?>
      <strong><?= $this->e(trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''))) ?></strong><br>
      <span class="muted"><?= $this->e($order['email'] ?? '') ?></span>
    <?php else : ?>
      <strong><?= $this->e($order['guest_email'] ?? 'Client invité') ?></strong>
    <?php endif; ?>
    <?php if ($order['delivery_mode'] === 'domicile') : ?>
      <br><?= $this->e($order['shipping_line1'] ?? 'Adresse communiquée lors de la commande') ?>
      <br><?= $this->e(($order['shipping_postal_code'] ?? '') . ' ' . ($order['shipping_city'] ?? '')) ?>
    <?php endif; ?>
  </div>
  <div class="col">
    <h2>Commande</h2>
    <strong><?= $this->e($order['order_number']) ?></strong><br>
    <span class="muted">Passée le <?= $this->e(date('d/m/Y', strtotime((string) $order['placed_at']))) ?></span><br>
    <span class="muted">Livraison : <?= $order['delivery_mode'] === 'retrait' ? 'Retrait à l’atelier' : 'À domicile' ?></span><br>
    <span class="badge">Payée</span>
  </div>
</div>

<h2>Détail des articles</h2>
<table>
  <thead>
    <tr>
      <th>Produit / Artisan</th>
      <th class="right">PU TTC</th>
      <th class="right">Qté</th>
      <th class="right">Total TTC</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $item) : ?>
      <tr>
        <td>
          <strong><?= $this->e($item['product_name']) ?></strong><br>
          <span class="muted"><?= $this->e($item['shop_name']) ?></span>
        </td>
        <td class="right"><?= number_format((float) $item['unit_price_ttc'], 2, ',', ' ') ?> DT</td>
        <td class="right"><?= (int) $item['quantity'] ?></td>
        <td class="right"><?= number_format((float) $item['line_total_ttc'], 2, ',', ' ') ?> DT</td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<table class="totals">
  <tr><td>Sous-total HT</td><td class="right"><?= number_format((float) $order['inv_subtotal_ht'], 2, ',', ' ') ?> DT</td></tr>
  <tr><td>TVA 19 % incluse</td><td class="right"><?= number_format((float) $order['inv_vat_amount'], 2, ',', ' ') ?> DT</td></tr>
  <tr><td>Frais de livraison</td><td class="right"><?= number_format((float) $order['inv_shipping_fee'], 2, ',', ' ') ?> DT</td></tr>
  <?php if ((float) $order['inv_discount_amount'] > 0) : ?>
    <tr><td>Remise (<?= $this->e($order['promo_code_id'] ? 'code promo' : '—') ?>)</td>
        <td class="right">− <?= number_format((float) $order['inv_discount_amount'], 2, ',', ' ') ?> DT</td></tr>
  <?php endif; ?>
  <tr class="grand"><td>TOTAL TTC</td><td class="right"><?= number_format((float) $order['inv_total_ttc'], 2, ',', ' ') ?> DT</td></tr>
</table>

<div class="notice">
  <strong>Paiement simulé (sandbox)</strong> — Référence de transaction :
  <code><?= $this->e($order['transaction_ref'] ?? '—') ?></code><br>
  Aucune donnée bancaire réelle n’a été collectée ni stockée par TuniShop (RG-07).
</div>

<div class="footer">
  TuniShop · Sidi Bou Saïd · TVA 19 % incluse dans les prix affichés ·
  Facture générée électroniquement — numérotation F-YYYY-NNNN (RG-08).
</div>

</body>
</html>

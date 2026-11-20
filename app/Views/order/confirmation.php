<h1>Commande <?= $this->e($order['order_number']) ?></h1>
<div class="alert ok">✅ Votre commande est confirmée. Un paiement simulé a été enregistré.</div>

<div class="cart-layout">
  <section class="card">
    <h2>Articles</h2>
    <?php foreach ($items as $item) : ?>
      <p><?= $this->e($item['product_name']) ?> × <?= (int) $item['quantity'] ?> —
        <?= number_format((float) $item['line_total_ttc'], 2, ',', ' ') ?> DT</p>
    <?php endforeach; ?>
    <p class="total">Total TTC : <?= number_format((float) $order['total_ttc'], 2, ',', ' ') ?> DT</p>
    <p class="small muted">Référence transaction : <code><?= $this->e($order['transaction_ref'] ?? '') ?></code></p>
  </section>

  <aside class="card">
    <h2>Suivi</h2>
    <p>Statut actuel : <span class="pill"><?= $this->e($order['status']) ?></span></p>
    <?php if ($invoice !== null) : ?>
      <p>Facture <strong><?= $this->e($invoice['invoice_number']) ?></strong> générée le <?= $this->e($invoice['issued_at']) ?>.</p>
      <a class="btn" href="/commande/<?= (int) $order['id'] ?>/facture.pdf">📄 Télécharger la facture PDF</a>
      <p class="muted small">Fichier enregistré dans <code>public/factures/</code> (non versionné).</p>
    <?php endif; ?>
    <h3>Historique</h3>
    <ul>
      <?php foreach ($history as $entry) : ?>
        <li><?= $this->e($entry['from_status'] ?? '—') ?> → <?= $this->e($entry['to_status']) ?>
          <span class="muted small"><?= $this->e($entry['created_at']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </aside>
</div>

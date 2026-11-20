<div class="page-head">
  <div>
    <h1>Commande <?= $this->e($order['order_number']) ?></h1>
    <p class="muted">Merci pour votre commande ! Voici son récapitulatif et son suivi.</p>
  </div>
  <span class="pill ok">✓ Paiement simulé enregistré</span>
</div>

<div class="cart-layout">
  <section class="card" aria-labelledby="items-title">
    <h2 id="items-title">Articles</h2>
    <?php foreach ($items as $item) : ?>
      <p class="line"><span><?= $this->e($item['product_name']) ?> × <?= (int) $item['quantity'] ?></span>
        <strong><?= number_format((float) $item['line_total_ttc'], 2, ',', ' ') ?> DT</strong>
      </p>
    <?php endforeach; ?>
    <p class="total">Total TTC : <?= number_format((float) $order['total_ttc'], 2, ',', ' ') ?> DT</p>
    <p class="small muted">Référence transaction : <code><?= $this->e($order['transaction_ref'] ?? '') ?></code></p>
    <p><a class="btn secondary" href="/compte">Voir mes commandes</a></p>
  </section>

  <aside class="card" aria-labelledby="tracking-title">
    <h2 id="tracking-title">Suivi de commande</h2>
    <p>Statut actuel : <span class="pill info"><?= $this->e($order['status']) ?></span></p>
    <?php if ($invoice !== null) : ?>
      <p>Facture <strong><?= $this->e($invoice['invoice_number']) ?></strong> générée le <?= $this->e($invoice['issued_at']) ?>.</p>
      <a class="btn large" href="/commande/<?= (int) $order['id'] ?>/facture.pdf">📄 Télécharger la facture PDF</a>
      <p class="muted small">Le fichier est généré à la demande et conservé hors du dépôt Git.</p>
    <?php endif; ?>

    <h3>Historique</h3>
    <?php if ($history === []) : ?>
      <p class="muted small">Aucun changement de statut pour le moment.</p>
    <?php else : ?>
      <ul>
        <?php foreach ($history as $entry) : ?>
          <li class="small"><?= $this->e($entry['from_status'] ?? '—') ?> → <?= $this->e($entry['to_status']) ?>
            <span class="muted"><?= $this->e($entry['created_at']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </aside>
</div>

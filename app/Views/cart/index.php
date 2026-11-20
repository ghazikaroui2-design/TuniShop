<h1>Votre panier</h1>

<?php if ($items === []) : ?>
  <div class="alert">Votre panier est vide. <a href="/catalogue">Découvrir le catalogue</a></div>
<?php else : ?>
  <div class="cart-layout">
    <div class="card">
      <?php foreach ($items as $item) : ?>
        <div class="cart-line">
          <div class="thumb">📦</div>
          <div>
            <h3><?= $this->e($item['product_name']) ?></h3>
            <p class="muted small"><?= number_format((float) $item['unit_price_ttc'], 2, ',', ' ') ?> DT × <?= (int) $item['quantity'] ?></p>
          </div>
          <form method="post" action="/panier/supprimer">
            <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
            <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
            <button class="link" type="submit">Supprimer</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

    <aside class="card recap">
      <h2>Récapitulatif</h2>
      <p>Sous-total TTC : <strong><?= number_format((float) $totals['subtotal_ttc'], 2, ',', ' ') ?> DT</strong></p>
      <p class="small muted">Dont HT <?= number_format((float) $totals['subtotal_ht'], 2, ',', ' ') ?> DT · TVA <?= number_format((float) $totals['vat_amount'], 2, ',', ' ') ?> DT</p>
      <p>Livraison : <strong><?= number_format((float) $totals['shipping_fee'], 2, ',', ' ') ?> DT</strong></p>
      <?php if ((float) $totals['remaining_for_free_shipping'] > 0) : ?>
        <p class="small muted">Plus que <?= number_format((float) $totals['remaining_for_free_shipping'], 2, ',', ' ') ?> DT pour la livraison offerte.</p>
      <?php endif; ?>
      <p class="total">TOTAL TTC : <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT</p>
      <a class="btn" href="/commande">Passer commande →</a>
      <p class="small muted">🔒 Paiement simulé — aucun débit réel.</p>
    </aside>
  </div>
<?php endif; ?>

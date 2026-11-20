<div class="page-head">
  <div>
    <h1>Votre panier</h1>
    <p class="muted">Vos créations préférées, prêtes à être commandées.</p>
  </div>
</div>

<?php if ($items === []) : ?>
  <div class="empty-state card">
    <p class="empty-icon" aria-hidden="true">🛒</p>
    <h2>Votre panier est vide</h2>
    <p class="muted">Parcourez le catalogue pour découvrir les artisans de Sidi Bou Saïd.</p>
    <a class="btn" href="/catalogue">Découvrir le catalogue →</a>
  </div>
<?php else : ?>
  <div class="cart-layout">
    <section class="card" aria-label="Articles du panier">
      <?php foreach ($items as $item) : ?>
        <div class="cart-line">
          <div class="thumb small" role="img" aria-label="Illustration de <?= $this->e($item['product_name']) ?>">
            <span aria-hidden="true">🧺</span>
          </div>
          <div>
            <h3><?= $this->e($item['product_name']) ?></h3>
            <p class="muted small"><?= number_format((float) $item['unit_price_ttc'], 2, ',', ' ') ?> DT l’unité</p>
            <form method="post" action="/panier/ajouter" class="inline" data-qty>
              <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
              <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
              <div class="qty">
                <button type="button" data-qty-step="-1" aria-label="Diminuer la quantité de <?= $this->e($item['product_name']) ?>">−</button>
                <label class="sr-only" for="qty-<?= (int) $item['product_id'] ?>">Quantité de <?= $this->e($item['product_name']) ?></label>
                <input id="qty-<?= (int) $item['product_id'] ?>" type="number" name="quantity"
                       value="<?= (int) $item['quantity'] ?>" min="1" max="99">
                <button type="button" data-qty-step="1" aria-label="Augmenter la quantité de <?= $this->e($item['product_name']) ?>">+</button>
              </div>
              <button class="btn small secondary" type="submit">Modifier</button>
            </form>
          </div>
          <div class="cart-actions">
            <p class="price"><?= number_format(isset($item['line_total_ttc']) ? (float) $item['line_total_ttc'] : (float) $item['unit_price_ttc'] * (int) $item['quantity'], 2, ',', ' ') ?> DT</p>
            <form method="post" action="/panier/supprimer" data-confirm="Retirer cet article du panier ?">
              <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
              <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
              <button class="link" type="submit" aria-label="Supprimer <?= $this->e($item['product_name']) ?> du panier">Supprimer</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
      <p class="muted small"><a href="/catalogue">← Continuer mes achats</a></p>
    </section>

    <aside class="card recap" aria-labelledby="recap-title">
      <h2 id="recap-title">Récapitulatif</h2>
      <p class="line"><span>Sous-total TTC</span><strong><?= number_format((float) $totals['subtotal_ttc'], 2, ',', ' ') ?> DT</strong></p>
      <p class="small muted">Dont HT <?= number_format((float) $totals['subtotal_ht'], 2, ',', ' ') ?> DT · TVA 19 % <?= number_format((float) $totals['vat_amount'], 2, ',', ' ') ?> DT</p>
      <p class="line"><span>Livraison</span><strong><?= number_format((float) $totals['shipping_fee'], 2, ',', ' ') ?> DT</strong></p>
      <?php if ((float) $totals['remaining_for_free_shipping'] > 0) : ?>
        <p class="small muted">Plus que <?= number_format((float) $totals['remaining_for_free_shipping'], 2, ',', ' ') ?> DT pour la livraison offerte.</p>
      <?php endif; ?>
      <p class="total">TOTAL TTC : <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT</p>
      <a class="btn large" href="/commande">Passer commande →</a>
      <p class="small muted">🔒 Paiement simulé — aucun débit réel.</p>
    </aside>
  </div>
<?php endif; ?>

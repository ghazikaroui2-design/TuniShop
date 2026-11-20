<h1>Finaliser ma commande</h1>
<ol class="steps"><li>① Panier ✓</li><li>② Livraison ✓</li><li>③ Paiement</li></ol>

<form method="post" action="/commande" class="checkout">
  <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">

  <div class="card">
    <h2>Mode de livraison</h2>
    <label><input type="radio" name="delivery_mode" value="domicile" checked> Livraison à domicile — 8,00 DT (offerte dès 100 DT)</label>
    <label><input type="radio" name="delivery_mode" value="retrait"> Retrait à l’atelier, Sidi Bou Saïd — gratuit</label>
  </div>

  <div class="card">
    <h2>Paiement sécurisé (simulation)</h2>
    <div class="alert">⚠ Mode sandbox : carte de test <strong>4242 4242 4242 4242</strong>, date future, CVC <strong>123</strong>. Aucune donnée bancaire n’est stockée.</div>
    <p class="muted small">Le numéro saisi ici n’est qu’une aide visuelle : seule une référence <code>SBX-…</code> est enregistrée.</p>
  </div>

  <aside class="card recap">
    <h2>Votre commande</h2>
    <?php foreach ($items as $item) : ?>
      <p class="small"><?= $this->e($item['product_name']) ?> × <?= (int) $item['quantity'] ?></p>
    <?php endforeach; ?>
    <p>Sous-total HT : <?= number_format((float) $totals['subtotal_ht'], 2, ',', ' ') ?> DT</p>
    <p>TVA 19 % : <?= number_format((float) $totals['vat_amount'], 2, ',', ' ') ?> DT</p>
    <p>Livraison : <?= number_format((float) $totals['shipping_fee'], 2, ',', ' ') ?> DT</p>
    <p class="total">TOTAL TTC : <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT</p>
    <button class="btn" type="submit">Payer <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT →</button>
  </aside>
</form>

<h1>Finaliser ma commande</h1>

<ol class="steps">
  <li class="<?= $step >= 1 ? 'active' : '' ?>">① Panier ✓</li>
  <li class="<?= $step >= 2 ? 'active' : '' ?>">② Livraison <?= $step >= 2 ? '✓' : '' ?></li>
  <li class="<?= $step >= 3 ? 'active' : '' ?>">③ Paiement</li>
</ol>

<?php if ($step === 1) : ?>
  <form method="post" action="/commande" class="checkout">
    <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
    <input type="hidden" name="etape" value="1">

    <div class="card">
      <h2>Mode de livraison</h2>
      <label class="radio-line">
        <input type="radio" name="delivery_mode" value="domicile"
               <?= ($checkout['delivery_mode'] ?? 'domicile') === 'domicile' ? 'checked' : '' ?>>
        <span><strong>Livraison à domicile</strong> — 8,00 DT (offerte dès 100 DT d’achat)</span>
      </label>
      <label class="radio-line">
        <input type="radio" name="delivery_mode" value="retrait"
               <?= ($checkout['delivery_mode'] ?? '') === 'retrait' ? 'checked' : '' ?>>
        <span><strong>Retrait à l’atelier</strong>, Sidi Bou Saïd — gratuit</span>
      </label>
    </div>

    <?php if ($addresses !== []) : ?>
      <div class="card" id="adresses">
        <h2>Adresse de livraison</h2>
        <label for="address_id">Choisir une adresse enregistrée</label>
        <select id="address_id" name="address_id" class="input">
          <?php foreach ($addresses as $address) : ?>
            <option value="<?= (int) $address['id'] ?>"
                    <?= (int) ($checkout['address_id'] ?? 0) === (int) $address['id'] ? 'selected' : '' ?>>
              <?= $this->e($address['label']) ?> — <?= $this->e($address['line1']) ?>,
              <?= $this->e($address['postal_code'] . ' ' . $address['city']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="muted small">Une adresse est requise pour la livraison à domicile.</p>
      </div>
    <?php endif; ?>

    <aside class="card recap">
      <h2>Votre commande</h2>
      <?php foreach ($items as $item) : ?>
        <p class="small"><?= $this->e($item['product_name']) ?> × <?= (int) $item['quantity'] ?></p>
      <?php endforeach; ?>
      <p>Sous-total HT : <?= number_format((float) $totals['subtotal_ht'], 2, ',', ' ') ?> DT</p>
      <p>TVA 19 % incluse : <?= number_format((float) $totals['vat_amount'], 2, ',', ' ') ?> DT</p>
      <p class="small muted">Sous-total TTC : <?= number_format((float) $totals['subtotal_ttc'], 2, ',', ' ') ?> DT</p>
      <p>Livraison estimée : <?= number_format((float) $totals['shipping_fee'], 2, ',', ' ') ?> DT</p>
      <p class="total">TOTAL TTC : <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT</p>
      <button class="btn" type="submit">Continuer vers le paiement →</button>
    </aside>
  </form>

<?php else : ?>
  <form method="post" action="/commande" class="checkout">
    <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
    <input type="hidden" name="etape" value="2">

    <div class="card">
      <h2>Paiement sécurisé (simulation)</h2>
      <div class="alert">
        ⚠️ <strong>Mode sandbox</strong> : utilisez la carte de test
        <strong>4242 4242 4242 4242</strong>, une date future et le CVC <strong>123</strong>.
        Aucun débit réel n’est effectué.
      </div>
      <p class="muted small">
        Pour garantir la RG-07, les champs ci-dessous n’ont pas d’attribut <code>name</code> :
        le numéro de carte n’est <strong>jamais transmis au serveur</strong>. Seul le
        code promo et votre confirmation sont envoyés.
      </p>
      <div class="fake-card">
        <label>Numéro de carte
          <input type="text" inputmode="numeric" placeholder="4242 4242 4242 4242" autocomplete="off">
        </label>
        <div class="row">
          <label>Expiration <input type="text" placeholder="12/30" autocomplete="off"></label>
          <label>CVC <input type="text" placeholder="123" autocomplete="off"></label>
        </div>
      </div>
      <label class="checkbox-line">
        <input type="checkbox" name="confirm_sandbox" value="1" required>
        <span>Je confirme utiliser la carte de test TuniShop (paiement simulé).</span>
      </label>
      <label for="promo_code">Code promo (facultatif)</label>
      <input id="promo_code" class="input" name="promo_code" placeholder="ARTISAN5">
      <p class="muted small">Essayez <code>ARTISAN5</code> (−5 DT dès 30 DT) ou <code>BIENVENUE10</code> (−10 % dès 50 DT).</p>
    </div>

    <aside class="card recap">
      <h2>Récapitulatif</h2>
      <p class="small">Livraison :
        <strong><?= ($checkout['delivery_mode'] ?? 'domicile') === 'retrait' ? 'Retrait à l’atelier' : 'À domicile' ?></strong>
      </p>
      <p>Sous-total HT : <?= number_format((float) $totals['subtotal_ht'], 2, ',', ' ') ?> DT</p>
      <p>TVA 19 % incluse : <?= number_format((float) $totals['vat_amount'], 2, ',', ' ') ?> DT</p>
      <p>Livraison : <?= number_format((float) $totals['shipping_fee'], 2, ',', ' ') ?> DT</p>
      <p class="total">TOTAL TTC : <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT</p>
      <button class="btn" type="submit">Payer <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT →</button>
      <a class="muted small" href="/commande">← Modifier la livraison</a>
      <p class="small muted">🔒 Aucune donnée bancaire n’est stockée (RG-07).</p>
    </aside>
  </form>
<?php endif; ?>

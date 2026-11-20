<h1>Finaliser ma commande</h1>

<ol class="steps" aria-label="Progression de la commande">
  <li class="is-done"><span aria-hidden="true">✓</span> Panier</li>
  <li class="<?= $step === 1 ? '' : 'is-done' ?>" <?= $step === 1 ? 'aria-current="step"' : '' ?>>
    <span aria-hidden="true"><?= $step === 1 ? '②' : '✓' ?></span> Livraison
  </li>
  <li class="<?= $step === 2 ? 'is-done' : '' ?>" <?= $step === 2 ? 'aria-current="step"' : '' ?>>
    <span aria-hidden="true">③</span> Paiement
  </li>
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

      <?php if ($addresses !== []) : ?>
        <div class="field">
          <label for="address_id">Adresse de livraison enregistrée</label>
          <select id="address_id" name="address_id">
            <?php foreach ($addresses as $address) : ?>
              <option value="<?= (int) $address['id'] ?>"
                      <?= (int) ($checkout['address_id'] ?? 0) === (int) $address['id'] ? 'selected' : '' ?>>
                <?= $this->e($address['label']) ?> — <?= $this->e($address['line1']) ?>,
                <?= $this->e($address['postal_code'] . ' ' . $address['city']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <span class="hint">Une adresse est requise pour la livraison à domicile et doit vous appartenir.</span>
        </div>
      <?php endif; ?>
    </div>

    <aside class="card recap" aria-labelledby="recap-title">
      <h2 id="recap-title">Votre commande</h2>
      <?php foreach ($items as $item) : ?>
        <p class="small line"><span><?= $this->e($item['product_name']) ?> × <?= (int) $item['quantity'] ?></span></p>
      <?php endforeach; ?>
      <p class="line"><span>Sous-total HT</span><strong><?= number_format((float) $totals['subtotal_ht'], 2, ',', ' ') ?> DT</strong></p>
      <p class="line"><span>TVA 19 % incluse</span><strong><?= number_format((float) $totals['vat_amount'], 2, ',', ' ') ?> DT</strong></p>
      <p class="small muted">Sous-total TTC : <?= number_format((float) $totals['subtotal_ttc'], 2, ',', ' ') ?> DT</p>
      <p class="line"><span>Livraison estimée</span><strong><?= number_format((float) $totals['shipping_fee'], 2, ',', ' ') ?> DT</strong></p>
      <p class="total">TOTAL TTC : <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT</p>
      <button class="btn large" type="submit">Continuer vers le paiement →</button>
    </aside>
  </form>

<?php else : ?>
  <form method="post" action="/commande" class="checkout">
    <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
    <input type="hidden" name="etape" value="2">

    <div class="card">
      <h2>Paiement sécurisé (simulation)</h2>
      <div class="alert" role="note">
        ⚠️ <strong>Mode sandbox</strong> : utilisez la carte de test
        <strong>4242 4242 4242 4242</strong>, une date future et le CVC <strong>123</strong>.
        Aucun débit réel n’est effectué.
      </div>
      <p class="muted small">
        Pour garantir la RG-07, les champs ci-dessous n’ont pas d’attribut <code>name</code> :
        le numéro de carte n’est <strong>jamais transmis au serveur</strong>. Seuls le code
        promo et votre confirmation sont envoyés.
      </p>
      <div class="fake-card">
        <label for="card-number">Numéro de carte</label>
        <input id="card-number" type="text" inputmode="numeric" placeholder="4242 4242 4242 4242"
               autocomplete="off" data-card-input aria-describedby="card-hint">
        <div class="row">
          <div>
            <label for="card-expiry">Expiration</label>
            <input id="card-expiry" type="text" inputmode="numeric" placeholder="12/30" autocomplete="off" data-expiry-input>
          </div>
          <div>
            <label for="card-cvc">CVC</label>
            <input id="card-cvc" type="text" inputmode="numeric" placeholder="123" autocomplete="off" data-cvc-input>
          </div>
        </div>
        <p class="card-hint" id="card-hint" data-card-hint aria-live="polite"></p>
      </div>
      <label class="checkbox-line">
        <input type="checkbox" name="confirm_sandbox" value="1" required>
        <span>Je confirme utiliser la carte de test TuniShop (paiement simulé).</span>
      </label>
      <div class="field">
        <label for="promo_code">Code promo (facultatif)</label>
        <input id="promo_code" class="input" name="promo_code" placeholder="ARTISAN5">
        <span class="hint">Essayez <code>ARTISAN5</code> (−5 DT dès 30 DT) ou <code>BIENVENUE10</code> (−10 % dès 50 DT).</span>
      </div>
    </div>

    <aside class="card recap" aria-labelledby="recap-title">
      <h2 id="recap-title">Récapitulatif</h2>
      <p class="line"><span>Livraison</span><strong><?= ($checkout['delivery_mode'] ?? 'domicile') === 'retrait' ? 'Retrait à l’atelier' : 'À domicile' ?></strong></p>
      <p class="line"><span>Sous-total HT</span><strong><?= number_format((float) $totals['subtotal_ht'], 2, ',', ' ') ?> DT</strong></p>
      <p class="line"><span>TVA 19 % incluse</span><strong><?= number_format((float) $totals['vat_amount'], 2, ',', ' ') ?> DT</strong></p>
      <p class="line"><span>Livraison</span><strong><?= number_format((float) $totals['shipping_fee'], 2, ',', ' ') ?> DT</strong></p>
      <p class="total">TOTAL TTC : <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT</p>
      <button class="btn large" type="submit">Payer <?= number_format((float) $totals['total_ttc'], 2, ',', ' ') ?> DT →</button>
      <p><a class="muted small" href="/commande">← Modifier la livraison</a></p>
      <p class="small muted">🔒 Aucune donnée bancaire n’est stockée (RG-07).</p>
    </aside>
  </form>
<?php endif; ?>

<?= $seo->jsonLdTag($seo->product($product, '/produit/' . rawurlencode((string) $product['slug']), $images)) ?>
<article class="product-detail" aria-labelledby="product-title">
  <div>
    <?php if ($images !== []) : ?>
      <?php $primary = $images[0]; ?>
      <?php if (!empty($primary['exists'])) : ?>
        <div class="thumb big">
          <img src="<?= $this->e($primary['src']) ?>" alt="<?= $this->e($primary['alt_text']) ?>">
        </div>
      <?php else : ?>
        <div class="thumb big" role="img" aria-label="Illustration du produit <?= $this->e($product['name']) ?>">
          <span aria-hidden="true">🏺</span>
        </div>
      <?php endif; ?>
      <?php if (count($images) > 1) : ?>
        <div class="thumbs">
          <?php foreach (array_slice($images, 0, 3) as $image) : ?>
            <?php if (!empty($image['exists'])) : ?>
              <img src="<?= $this->e($image['src']) ?>" alt="<?= $this->e($image['alt_text']) ?>">
            <?php else : ?>
              <div class="thumb small" role="img" aria-label="<?= $this->e($image['alt_text']) ?>"><span aria-hidden="true">🎨</span></div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php else : ?>
      <div class="thumb big" role="img" aria-label="Illustration du produit <?= $this->e($product['name']) ?>">
        <span aria-hidden="true">🏺</span>
      </div>
    <?php endif; ?>
  </div>

  <div>
    <p class="muted small"><a href="/catalogue">← Retour au catalogue</a></p>
    <span class="pill"><?= $this->e($product['category_name'] ?? 'Artisanat') ?></span>
    <h1 id="product-title"><?= $this->e($product['name']) ?></h1>
    <p class="muted"><?= $this->e($product['shop_name']) ?> · <?= $this->e($product['neighborhood']) ?></p>
    <p class="price big"><?= number_format((float) $product['price_ttc'], 2, ',', ' ') ?> DT
      <span class="muted small">TTC · TVA 19 % incluse</span>
    </p>

    <div class="product-meta" aria-label="Points clés">
      <?php if ((int) $product['stock_qty'] > 0) : ?>
        <span class="pill ok">● <?= (int) $product['stock_qty'] ?> en stock</span>
      <?php else : ?>
        <span class="pill err">● Rupture de stock</span>
      <?php endif; ?>
      <span class="pill info">✓ Vendeur validé</span>
      <span class="pill">🚚 Expédié sous 48 h</span>
      <span class="pill">🏠 Retrait gratuit à l’atelier</span>
    </div>

    <?php if ((int) $product['stock_qty'] > 0) : ?>
      <form method="post" action="/panier/ajouter" class="add-cart" id="add-to-cart-form">
        <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
        <label class="sr-only" for="product-quantity">Quantité</label>
        <div class="qty" data-qty>
          <button type="button" data-qty-step="-1" aria-label="Diminuer la quantité">−</button>
          <input id="product-quantity" type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock_qty'] ?>">
          <button type="button" data-qty-step="1" aria-label="Augmenter la quantité">+</button>
        </div>
        <button class="btn large" type="submit">Ajouter au panier</button>
      </form>
    <?php else : ?>
      <div class="alert" role="status">Produit momentanément épuisé.</div>
    <?php endif; ?>
  </div>
</article>

<section class="card" aria-label="Informations sur le produit">
  <div class="tabs" role="tablist" data-tabs>
    <button type="button" role="tab" id="tab-description" aria-controls="panel-description" aria-selected="true">Description</button>
    <button type="button" role="tab" id="tab-avis" aria-controls="panel-avis" aria-selected="false" tabindex="-1">Avis</button>
    <button type="button" role="tab" id="tab-livraison" aria-controls="panel-livraison" aria-selected="false" tabindex="-1">Livraison</button>
  </div>

  <div class="tabpanel" role="tabpanel" id="panel-description" aria-labelledby="tab-description">
    <p><?= nl2br($this->e($product['description'] ?? $product['short_description'] ?? '')) ?></p>
  </div>
  <div class="tabpanel" role="tabpanel" id="panel-avis" aria-labelledby="tab-avis" hidden>
    <p class="muted">Les avis sont publiés après modération et réservés aux clients ayant reçu une commande livrée (RG-05).</p>
  </div>
  <div class="tabpanel" role="tabpanel" id="panel-livraison" aria-labelledby="tab-livraison" hidden>
    <p>Livraison à domicile à Sidi Bou Saïd et en Tunisie (8,00 DT, offerte dès 100 DT d’achat) ou retrait gratuit à l’atelier sous 48 h.</p>
  </div>
</section>

<?php if ((int) $product['stock_qty'] > 0) : ?>
  <div class="mobile-sticky-cta">
    <button class="btn large" type="submit" form="add-to-cart-form">Ajouter au panier</button>
  </div>
<?php endif; ?>

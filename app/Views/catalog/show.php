<article class="product-detail">
  <div class="thumb big">📦</div>
  <div>
    <a class="muted" href="/catalogue">← Catalogue</a>
    <h1><?= $this->e($product['name']) ?></h1>
    <p class="muted"><?= $this->e($product['shop_name']) ?> · <?= $this->e($product['neighborhood']) ?></p>
    <p class="price big"><?= number_format((float) $product['price_ttc'], 2, ',', ' ') ?> DT <span class="muted small">TTC · TVA 19 % incluse</span></p>
    <p><?= nl2br($this->e($product['description'] ?? $product['short_description'] ?? '')) ?></p>
    <p class="muted">Stock disponible : <strong><?= (int) $product['stock_qty'] ?></strong></p>

    <?php if ((int) $product['stock_qty'] > 0) : ?>
      <form method="post" action="/panier/ajouter" class="add-cart">
        <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
        <input type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock_qty'] ?>">
        <button class="btn" type="submit">Ajouter au panier</button>
      </form>
    <?php else : ?>
      <div class="alert">Produit momentanément épuisé.</div>
    <?php endif; ?>
  </div>
</article>

<?php if ($images !== []) : ?>
  <h2>Galerie</h2>
  <div class="grid thumbs">
    <?php foreach ($images as $image) : ?>
      <img src="/uploads/<?= $this->e($image['image_path']) ?>" alt="<?= $this->e($image['alt_text']) ?>">
    <?php endforeach; ?>
  </div>
<?php endif; ?>

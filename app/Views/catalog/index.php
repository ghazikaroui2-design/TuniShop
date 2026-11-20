<h1>Catalogue</h1>

<form class="filters" method="get" action="/catalogue">
  <input type="search" name="q" placeholder="Rechercher…" value="<?= $this->e($filters['q'] ?? '') ?>">
  <select name="category">
    <option value="">Toutes les catégories</option>
    <?php foreach ($categories as $category) : ?>
      <option value="<?= $this->e($category['slug']) ?>" <?= ($filters['category'] ?? '') === $category['slug'] ? 'selected' : '' ?>>
        <?= $this->e($category['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <input type="number" name="max_price" min="0" step="1" placeholder="Prix max (DT)" value="<?= $this->e($filters['max_price'] ?? '') ?>">
  <button class="btn" type="submit">Filtrer</button>
</form>

<?php if ($products === []) : ?>
  <div class="alert">Aucun produit ne correspond à ces critères.</div>
<?php else : ?>
  <p class="muted"><?= count($products) ?> produit(s) disponible(s)</p>
  <div class="grid">
    <?php foreach ($products as $product) : ?>
      <article class="card product">
        <div class="thumb">📦</div>
        <span class="pill"><?= $this->e($product['category_name']) ?></span>
        <h3><a href="/produit/<?= $this->e($product['slug']) ?>"><?= $this->e($product['name']) ?></a></h3>
        <p class="muted small"><?= $this->e($product['shop_name']) ?> · <?= $this->e($product['neighborhood']) ?></p>
        <p class="price"><?= number_format((float) $product['price_ttc'], 2, ',', ' ') ?> DT</p>
        <p class="muted small">★ <?= $this->e($product['avg_rating']) ?> (<?= (int) $product['review_count'] ?>) · Stock <?= (int) $product['stock_qty'] ?></p>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

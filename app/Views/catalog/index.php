<div class="page-head">
  <div>
    <h1>Catalogue des artisans</h1>
    <p class="muted">Créations faites main à Sidi Bou Saïd et alentours.</p>
  </div>
  <p class="pill"><?= count($products) ?> produit<?= count($products) > 1 ? 's' : '' ?> disponible<?= count($products) > 1 ? 's' : '' ?></p>
</div>

<button class="btn secondary filter-toggle" type="button" data-filter-toggle aria-controls="filter-panel" aria-expanded="true">
  ⚙ Filtrer et trier
</button>

<form class="card filter-panel" id="filter-panel" method="get" action="/catalogue">
  <h2 class="small">Affiner la sélection</h2>
  <div class="filters">
    <div class="field">
      <label for="filter-q">Rechercher</label>
      <input id="filter-q" type="search" name="q" placeholder="Vase, tapis, parfum…" value="<?= $this->e($filters['q'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="filter-category">Catégorie</label>
      <select id="filter-category" name="category">
        <option value="">Toutes les catégories</option>
        <?php foreach ($categories as $category) : ?>
          <option value="<?= $this->e($category['slug']) ?>" <?= ($filters['category'] ?? '') === $category['slug'] ? 'selected' : '' ?>>
            <?= $this->e($category['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="filter-price">Prix maximum (DT)</label>
      <input id="filter-price" type="number" name="max_price" min="0" step="1" placeholder="200" value="<?= $this->e($filters['max_price'] ?? '') ?>">
    </div>
    <button class="btn" type="submit">Filtrer</button>
    <a class="btn secondary" href="/catalogue">Réinitialiser</a>
  </div>
</form>

<?php if ($products === []) : ?>
  <div class="empty-state card">
    <p class="empty-icon" aria-hidden="true">🔎</p>
    <h2>Aucun produit ne correspond à ces critères</h2>
    <p class="muted">Essayez un autre mot-clé ou une catégorie différente.</p>
    <a class="btn" href="/catalogue">Réinitialiser les filtres</a>
  </div>
<?php else : ?>
  <div class="grid cols-4">
    <?php foreach ($products as $product) : ?>
      <article class="card product">
        <div class="thumb" role="img" aria-label="Illustration du produit <?= $this->e($product['name']) ?>">
          <span aria-hidden="true">🧶</span>
        </div>
        <div class="body">
          <span class="pill"><?= $this->e($product['category_name']) ?></span>
          <h3><a href="/produit/<?= $this->e($product['slug']) ?>"><?= $this->e($product['name']) ?></a></h3>
          <p class="muted small"><?= $this->e($product['shop_name']) ?> · <?= $this->e($product['neighborhood']) ?></p>
          <p class="muted small">★ <?= $this->e($product['avg_rating']) ?> (<?= (int) $product['review_count'] ?>) · Stock <?= (int) $product['stock_qty'] ?></p>
          <div class="price-line">
            <span class="price"><?= number_format((float) $product['price_ttc'], 2, ',', ' ') ?> DT</span>
            <a class="btn small secondary" href="/produit/<?= $this->e($product['slug']) ?>">Voir →</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

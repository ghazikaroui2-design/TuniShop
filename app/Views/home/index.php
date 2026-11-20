<section class="hero">
  <span class="pill">100 % local · Sidi Bou Saïd</span>
  <h1>L’artisanat tunisien, à portée de clic.</h1>
  <p>Céramique bleue et blanche, tapis tissés, parfums et bijoux : découvrez les créations des artisans du village.</p>
  <a class="btn" href="/catalogue">Découvrir le catalogue →</a>
</section>

<section>
  <h2>Nos catégories</h2>
  <div class="grid">
    <?php foreach ($categories as $category) : ?>
      <a class="card category" href="/catalogue?category=<?= $this->e($category['slug']) ?>">
        <strong><?= $this->e($category['name']) ?></strong>
        <span class="muted"><?= $this->e($category['description'] ?? '') ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <h2>Coups de cœur</h2>
  <div class="grid">
    <?php foreach ($featured as $product) : ?>
      <article class="card product">
        <div class="thumb">📦</div>
        <h3><a href="/produit/<?= $this->e($product['slug']) ?>"><?= $this->e($product['name']) ?></a></h3>
        <p class="muted small"><?= $this->e($product['shop_name']) ?></p>
        <p class="price"><?= number_format((float) $product['price_ttc'], 2, ',', ' ') ?> DT</p>
      </article>
    <?php endforeach; ?>
  </div>
</section>

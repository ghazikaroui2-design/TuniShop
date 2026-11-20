<section class="hero" aria-labelledby="hero-title">
  <div>
    <span class="pill">100 % local · Fait main · Sidi Bou Saïd</span>
    <h1 id="hero-title">L’artisanat tunisien, à portée de clic.</h1>
    <p>Céramique bleue et blanche, tapis tissés, parfums et bijoux : découvrez les créations des artisans du village.</p>
    <a class="btn large" href="/catalogue">Découvrir le catalogue →</a>
  </div>
  <div class="thumb" role="img" aria-label="Illustration du village bleu et blanc de Sidi Bou Saïd">
    <span aria-hidden="true">🏘️</span>
  </div>
</section>

<section class="section" aria-labelledby="categories-title">
  <div class="section-head">
    <h2 id="categories-title">Nos catégories</h2>
    <a class="link" href="/catalogue">Tout voir →</a>
  </div>
  <div class="grid cols-3">
    <?php
    $icons = ['céramique' => '🏺', 'tapis' => '▦', 'parfumerie' => '◇', 'bijoux' => '✧', 'décoration' => '◈', 'calligraphie' => '✎'];
    foreach ($categories as $category) :
        $slug = (string) ($category['slug'] ?? '');
        $icon = $icons[$slug] ?? '◍';
    ?>
      <a class="card category" href="/catalogue?category=<?= $this->e($slug) ?>">
        <span class="category-icon" aria-hidden="true"><?= $this->e($icon) ?></span>
        <strong><?= $this->e($category['name']) ?></strong>
        <span class="muted small"><?= $this->e($category['description'] ?? '') ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="section" aria-labelledby="featured-title">
  <div class="section-head">
    <h2 id="featured-title">Coups de cœur des artisans</h2>
    <a class="link" href="/catalogue">Voir tout →</a>
  </div>
  <div class="grid cols-4">
    <?php foreach ($featured as $product) : ?>
      <article class="card product">
        <div class="thumb" role="img" aria-label="Illustration du produit <?= $this->e($product['name']) ?>">
          <span aria-hidden="true">🎨</span>
        </div>
        <div class="body">
          <span class="pill"><?= $this->e($product['category_name'] ?? 'Artisanat') ?></span>
          <h3><a href="/produit/<?= $this->e($product['slug']) ?>"><?= $this->e($product['name']) ?></a></h3>
          <p class="muted small"><?= $this->e($product['shop_name']) ?></p>
          <div class="price-line">
            <span class="price"><?= number_format((float) $product['price_ttc'], 2, ',', ' ') ?> DT</span>
            <a class="btn small secondary" href="/produit/<?= $this->e($product['slug']) ?>">Voir →</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="banner" aria-labelledby="seller-banner-title">
  <div>
    <h2 id="seller-banner-title" class="small">Vous êtes artisan à Sidi Bou Saïd ?</h2>
    <p>Rejoignez la marketplace et vendez vos créations à toute la Tunisie.</p>
  </div>
  <a class="link" href="/inscription">Devenir vendeur →</a>
</section>

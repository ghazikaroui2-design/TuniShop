<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#1d5d8c">
  <title><?= $this->e($pageTitle ?? 'Accueil') ?> — TuniShop</title>
  <meta name="description" content="<?= $this->e($pageDescription ?? 'TuniShop — marketplace locale de l’artisanat tunisien à Sidi Bou Saïd. Céramique, tapis, parfums et bijoux faits main.') ?>">
  <link rel="stylesheet" href="<?= $this->e($baseUrl ?? '') ?>/assets/css/app.css">
  <script src="<?= $this->e($baseUrl ?? '') ?>/assets/js/app.js" defer></script>
</head>
<body data-base-url="<?= $this->e($baseUrl ?? '') ?>">
<a class="skip-link" href="#contenu">Aller au contenu principal</a>

<header class="site-header">
  <div class="header-inner">
    <button class="nav-toggle" type="button" data-nav-toggle aria-controls="primary-nav" aria-expanded="false" aria-label="Ouvrir le menu">☰</button>

    <a class="brand" href="/">🏺 TuniShop<em>Sidi Bou Saïd · fait main</em></a>

    <nav id="primary-nav" class="primary-nav" aria-label="Navigation principale">
      <a href="/">Accueil</a>
      <a href="/catalogue">Catalogue</a>
      <a href="/panier">Panier</a>
      <?php if (!empty($session) && $session->isAuthenticated()) : ?>
        <a href="/compte">Mon compte</a>
      <?php endif; ?>
      <?php if (!empty($session) && in_array($session->role(), ['vendeur', 'admin'], true)) : ?>
        <a href="/vendeur">Espace vendeur</a>
      <?php endif; ?>
      <?php if (!empty($session) && $session->role() === 'admin') : ?>
        <a href="/admin">Administration</a>
      <?php endif; ?>
      <?php if (($appEnv ?? 'local') === 'local') : ?><a href="/sante">Santé</a><?php endif; ?>
    </nav>

    <form class="header-search" method="get" action="/catalogue" role="search">
      <label class="sr-only" for="site-search">Rechercher un produit</label>
      <input id="site-search" type="search" name="q" placeholder="Rechercher un produit…"
             autocomplete="off" data-live-search aria-controls="search-results" aria-expanded="false">
      <ul id="search-results" class="search-results" role="listbox" aria-label="Suggestions de produits" hidden></ul>
    </form>

    <div class="header-actions">
      <?php if (!empty($session) && $session->isAuthenticated()) : ?>
        <a href="/compte" aria-label="Mon compte">👤 <span class="label"><?= $this->e(explode(' ', (string) ($session->user()['first_name'] ?? ''))[0] ?: 'Compte') ?></span></a>
        <form method="post" action="/deconnexion" class="inline">
          <input type="hidden" name="_token" value="<?= $this->e($session->csrfToken()) ?>">
          <button type="submit" aria-label="Se déconnecter">↩ <span class="label">Déconnexion</span></button>
        </form>
      <?php else : ?>
        <a href="/connexion" aria-label="Se connecter">👤 <span class="label">Connexion</span></a>
        <a href="/inscription" class="mobile-only" aria-label="Créer un compte">Inscription</a>
      <?php endif; ?>
      <a href="/panier" aria-label="Voir mon panier">🛒 <span class="label">Panier</span></a>
    </div>
  </div>
</header>

<?php if (!empty($breadcrumb)) : ?>
  <nav class="breadcrumb container" aria-label="Fil d’Ariane">
    <ol>
      <li><a href="/">Accueil</a></li>
      <?php foreach ($breadcrumb as $label => $link) : ?>
        <li><?php if ($link) : ?><a href="<?= $this->e($link) ?>"><?= $this->e($label) ?></a>
            <?php else : ?><span aria-current="page"><?= $this->e($label) ?></span><?php endif; ?></li>
      <?php endforeach; ?>
    </ol>
  </nav>
<?php endif; ?>

<main id="contenu" class="container" tabindex="-1">
  <?php if (!empty($flash['success'])) : ?>
    <div class="alert ok" role="status" data-toast="success"><?= $this->e($flash['success']) ?></div>
  <?php endif; ?>
  <?php if (!empty($flash['error']) || !empty($error)) : ?>
    <div class="alert err" role="alert" data-toast="error"><?= $this->e($flash['error'] ?? $error) ?></div>
  <?php endif; ?>
  <?= $content ?>
</main>

<div id="toast-stack" class="toast-stack" aria-live="polite" aria-atomic="false"></div>

<footer class="site-footer" id="a-propos">
  <div class="footer-grid">
    <div>
      <h2>TuniShop</h2>
      <p>La marketplace des artisans et commerçants de Sidi Bou Saïd. Des objets faits main, 100 % locaux, livrés chez vous ou retirés à l’atelier.</p>
    </div>
    <div>
      <h3>Découvrir</h3>
      <ul>
        <li><a href="/">Accueil</a></li>
        <li><a href="/catalogue">Catalogue</a></li>
        <li><a href="/panier">Mon panier</a></li>
      </ul>
    </div>
    <div>
      <h3>Mon espace</h3>
      <ul>
        <li><a href="/compte">Mes commandes</a></li>
        <li><a href="/vendeur">Espace vendeur</a></li>
        <li><a href="/inscription">Devenir vendeur</a></li>
      </ul>
    </div>
    <div>
      <h3>Contact</h3>
      <ul>
        <li>Sidi Bou Saïd, Tunisie</li>
        <li><a href="mailto:bonjour@tunishop.tn">bonjour@tunishop.tn</a></li>
        <li><a href="/sante">État du service</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 TuniShop · BTS DI 2-1 — projet pédagogique</span>
    <span>Paiement simulé — aucun débit réel, aucune donnée bancaire stockée.</span>
  </div>
</footer>
</body>
</html>

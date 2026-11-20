<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->e($pageTitle ?? 'Accueil') ?> — TuniShop</title>
  <meta name="description" content="TuniShop — marketplace locale de l’artisanat tunisien à Sidi Bou Saïd. Céramique, tapis, parfums et bijoux faits main.">
  <link rel="stylesheet" href="<?= $this->e($baseUrl ?? '') ?>/assets/css/app.css">
</head>
<body>
<header class="site-header">
  <a class="brand" href="/">🏺 TuniShop</a>
  <nav>
    <a href="/catalogue">Catalogue</a>
    <a href="/panier">Panier</a>
    <?php if (($appEnv ?? 'local') === 'local') : ?><a href="/sante">Santé</a><?php endif; ?>
    <?php if (!empty($session) && $session->isAuthenticated()) : ?>
      <a href="/vendeur">Espace vendeur</a>
      <a href="/admin">Admin</a>
      <form method="post" action="/deconnexion" style="display:inline">
        <input type="hidden" name="_token" value="<?= $this->e($session->csrfToken()) ?>">
        <button class="link" type="submit">Déconnexion</button>
      </form>
    <?php else : ?>
      <a href="/connexion">Connexion</a>
    <?php endif; ?>
  </nav>
</header>

<main class="container">
  <?php if (!empty($flash['success'])) : ?><div class="alert ok"><?= $this->e($flash['success']) ?></div><?php endif; ?>
  <?php if (!empty($flash['error']) || !empty($error)) : ?>
    <div class="alert err"><?= $this->e($flash['error'] ?? $error) ?></div>
  <?php endif; ?>
  <?= $content ?>
</main>

<footer class="site-footer">
  <p>TuniShop · Sidi Bou Saïd · Paiement simulé — aucun débit réel.</p>
</footer>
</body>
</html>

<?php
$badge = static function (string $status): string {
    return match (mb_strtolower(str_replace('_', ' ', $status))) {
        'payée', 'expédiée', 'livrée' => 'ok',
        'en attente', 'en préparation' => 'warn',
        'annulée' => 'err',
        default => '',
    };
};
$fullName = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
?>

<div class="page-head">
  <div>
    <h1>Bonjour <?= $this->e($user['first_name'] ?? 'client') ?> 👋</h1>
    <p class="muted">Retrouvez ici l’historique de vos achats et vos coordonnées.</p>
  </div>
  <a class="btn secondary" href="/catalogue">Continuer mes achats →</a>
</div>

<div class="kpis">
  <div class="kpi"><span class="l">Commandes</span><span class="v"><?= count($orders) ?></span></div>
  <div class="kpi"><span class="l">Avis déposés</span><span class="v"><?= $reviewCount ?></span></div>
  <div class="kpi"><span class="l">Total dépensé</span><span class="v"><?= number_format($spent, 2, ',', ' ') ?> DT</span></div>
</div>

<div class="account-layout mt-6">
  <aside class="card account-menu" aria-label="Menu du compte">
    <ul>
      <li><a href="#mes-commandes" aria-current="page">📦 Mes commandes</a></li>
      <li><a href="#mes-avis">⭐ Mes avis</a></li>
      <li><a href="#mes-adresses">📍 Mes adresses</a></li>
      <li><a href="#mon-profil">👤 Mon profil</a></li>
      <li>
        <form method="post" action="/deconnexion">
          <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
          <button class="link" type="submit">🔒 Déconnexion</button>
        </form>
      </li>
    </ul>
  </aside>

  <div>
    <section id="mes-commandes" aria-labelledby="orders-title">
      <div class="section-head"><h2 id="orders-title">Mes commandes</h2></div>
      <?php if ($orders === []) : ?>
        <div class="card empty-state">
          <p class="empty-icon" aria-hidden="true">📦</p>
          <p class="muted">Vous n’avez pas encore passé de commande.</p>
          <a class="btn" href="/catalogue">Découvrir le catalogue →</a>
        </div>
      <?php else : ?>
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th scope="col">N°</th>
                <th scope="col">Date</th>
                <th scope="col">Articles</th>
                <th scope="col">Total</th>
                <th scope="col">Statut</th>
                <th scope="col">Facture</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order) : ?>
                <tr>
                  <td data-label="N°"><a href="/commande/<?= (int) $order['id'] ?>"><?= $this->e($order['order_number']) ?></a></td>
                  <td data-label="Date"><?= $this->e(date('d/m/Y', strtotime((string) $order['placed_at']))) ?></td>
                  <td data-label="Articles"><?= (int) $order['item_count'] ?></td>
                  <td data-label="Total"><?= number_format((float) $order['total_ttc'], 2, ',', ' ') ?> DT</td>
                  <td data-label="Statut"><span class="pill <?= $badge((string) $order['status']) ?>"><?= $this->e(str_replace('_', ' ', (string) $order['status'])) ?></span></td>
                  <td data-label="Facture">
                    <?php if (!empty($order['invoice_number'])) : ?>
                      <a href="/commande/<?= (int) $order['id'] ?>/facture.pdf">PDF ⬇</a>
                    <?php else : ?>
                      <span class="muted small">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <section id="mes-avis" class="card" aria-labelledby="reviews-title" class="mt-6">
      <h2 id="reviews-title">Mes avis</h2>
      <?php if ($delivered === []) : ?>
        <p class="muted small">Vous pourrez laisser un avis sur un produit après réception d’une commande livrée (RG-05).</p>
      <?php else : ?>
        <p class="muted small">Les avis sont publiés après modération. Vous pouvez donner votre avis sur les commandes livrées ci-dessous.</p>
        <ul>
          <?php foreach ($delivered as $order) : ?>
            <li class="small">
              <a href="/commande/<?= (int) $order['id'] ?>"><?= $this->e($order['order_number']) ?></a>
              — <?= (int) $order['item_count'] ?> article<?= (int) $order['item_count'] > 1 ? 's' : '' ?>
              livré<?= (int) $order['item_count'] > 1 ? 's' : '' ?> le <?= $this->e(date('d/m/Y', strtotime((string) $order['placed_at']))) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section id="mes-adresses" class="card" aria-labelledby="addresses-title" class="mt-6">
      <h2 id="addresses-title">Mes adresses de livraison</h2>
      <?php if ($addresses === []) : ?>
        <p class="muted small">Aucune adresse enregistrée. Vous pourrez en ajouter une lors de votre prochaine commande.</p>
      <?php else : ?>
        <ul class="stack">
          <?php foreach ($addresses as $address) : ?>
            <li>
              <strong><?= $this->e($address['label']) ?></strong>
              <?php if (!empty($address['is_default'])) : ?><span class="pill ok">Par défaut</span><?php endif; ?>
              <br><span class="muted small"><?= $this->e($address['line1']) ?>,
                <?= $this->e($address['postal_code'] . ' ' . $address['city']) ?> — <?= $this->e($address['governorate']) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section id="mon-profil" class="card" aria-labelledby="profile-title" class="mt-6">
      <h2 id="profile-title">Mon profil</h2>
      <dl class="grid cols-2">
        <div><dt class="muted small">Nom complet</dt><dd><?= $this->e($fullName !== '' ? $fullName : '—') ?></dd></div>
        <div><dt class="muted small">Email</dt><dd><?= $this->e($user['email'] ?? '—') ?></dd></div>
        <div><dt class="muted small">Téléphone</dt><dd><?= $this->e($user['phone'] ?? '—') ?></dd></div>
        <div><dt class="muted small">Membre depuis</dt><dd><?= $this->e(date('d/m/Y', strtotime((string) ($user['created_at'] ?? date('Y-m-d'))))) ?></dd></div>
      </dl>
    </section>
  </div>
</div>

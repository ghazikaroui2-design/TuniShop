<?php
/**
 * Back-office vendeur / administrateur (écrans 9 à 12 du prototype).
 * Une seule vue paramétrée par le contrôleur : contexte, menu actif, KPI et tableau.
 */
$boContext = $boContext ?? 'seller';
$activeMenu = $activeMenu ?? 'dashboard';
$stats = $stats ?? [];
$rows = $rows ?? [];
$columns = $columns ?? [];
$chart = $chart ?? [];

$menus = [
    'seller' => [
        ['dashboard',  '▤', 'Tableau de bord', '/vendeur'],
        ['boutique',   '◈', 'Ma boutique',     '/vendeur/boutique'],
        ['produits',   '◍', 'Produits & stocks', '/vendeur/produits'],
        ['commandes',  '📦', 'Commandes',      '/vendeur/commandes'],
    ],
    'admin' => [
        ['dashboard',  '▤', 'Tableau de bord', '/admin'],
        ['commandes',  '📦', 'Commandes',      '/admin'],
        ['vendeurs',   '👥', 'Vendeurs',        '/admin/vendeurs'],
        ['produits',   '◍', 'Stock & produits', '/admin'],
    ],
];
$menu = $menus[$boContext] ?? $menus['seller'];

$humanize = static fn (string $value): string => ucfirst(str_replace('_', ' ', $value));

$formatStat = static function (string $key, mixed $value): string {
    if ($value === null) {
        return '—';
    }
    if (!is_numeric($value)) {
        return (string) $value;
    }
    $isMoney = preg_match('/ca|revenu|total|montant|chiffre|ttc|amount|revenue|moyen/i', $key) === 1;

    return $isMoney
        ? number_format((float) $value, 2, ',', ' ') . ' DT'
        : number_format((float) $value, 0, ',', ' ');
};

$badge = static function (string $value): string {
    $normalised = mb_strtolower(trim($value));
    $ok = ['validée', 'validé', 'payée', 'payé', 'publié', 'livrée', 'livré', 'accepté', 'actif', 'active', 'disponible'];
    $warn = ['en_attente', 'en attente', 'en_préparation', 'en préparation', 'expédiée', 'stock faible', 'attente'];
    $err = ['refusée', 'refusé', 'annulée', 'annulé', 'rupture', 'épuisé', 'masqué'];

    if (in_array($normalised, $ok, true)) {
        return 'ok';
    }
    if (in_array($normalised, $warn, true)) {
        return 'warn';
    }
    if (in_array($normalised, $err, true)) {
        return 'err';
    }

    return '';
};

$chartMax = $chart === [] ? 1 : max(1, ...array_map(static fn (array $point): float => (float) ($point['value'] ?? 0), $chart));
?>

<div class="bo-shell">
  <aside class="bo-menu" aria-label="Menu <?= $this->e($context) ?>">
    <div class="bo-brand">TuniShop<small><?= $boContext === 'admin' ? 'ADMINISTRATION' : 'ESPACE VENDEUR' ?></small></div>
    <ul>
      <?php foreach ($menu as [$key, $icon, $label, $link]) : ?>
        <li>
          <a href="<?= $this->e($link) ?>" <?= $key === $activeMenu ? 'aria-current="page"' : '' ?>>
            <span><span aria-hidden="true"><?= $this->e($icon) ?></span> <?= $this->e($label) ?></span>
            <?php if ($key === 'vendeurs' && !empty($pendingCount)) : ?>
              <span class="tag"><?= (int) $pendingCount ?></span>
            <?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li><a href="/">← Voir le site</a></li>
    </ul>
  </aside>

  <div class="bo-content">
    <nav class="bo-tabs" aria-label="Navigation rapide">
      <?php foreach ($menu as [$key, $icon, $label, $link]) : ?>
        <a href="<?= $this->e($link) ?>" <?= $key === $activeMenu ? 'aria-current="page"' : '' ?>>
          <?= $this->e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="bo-head">
      <div>
        <h1><?= $this->e($pageTitle) ?></h1>
        <p class="muted small">Espace <?= $this->e($context) ?> · données en temps réel depuis la base TuniShop.</p>
      </div>
      <?php if ($boContext === 'seller') : ?>
        <a class="btn secondary" href="/produit/vase-bleu-et-blanc">Voir ma vitrine →</a>
      <?php endif; ?>
    </div>

    <?php if ($stats !== []) : ?>
      <div class="grid stats">
        <?php foreach ($stats as $key => $value) : ?>
          <div class="card stat">
            <span class="muted small"><?= $this->e($humanize((string) $key)) ?></span>
            <strong><?= $this->e($formatStat((string) $key, $value)) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($chart !== []) : ?>
      <section class="card" aria-labelledby="chart-title">
        <h2 id="chart-title" class="small">Ventes des 7 derniers jours</h2>
        <div class="chart" role="img" aria-label="Évolution des ventes sur les 7 derniers jours">
          <?php foreach ($chart as $point) : ?>
            <div class="bar" style="height:<?= max(4, (int) round(((float) ($point['value'] ?? 0) / $chartMax) * 100)) ?>%"
                 title="<?= $this->e((string) ($point['label'] ?? '')) ?> : <?= $this->e((string) ($point['value'] ?? 0)) ?>">
              <span aria-hidden="true"><?= $this->e((string) ($point['label'] ?? '')) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="card" aria-labelledby="list-title">
      <div class="section-head">
        <h2 id="list-title"><?= $this->e($listTitle) ?></h2>
        <span class="pill"><?= count($rows) ?> ligne<?= count($rows) > 1 ? 's' : '' ?></span>
      </div>

      <?php if ($rows === []) : ?>
        <div class="empty-state">
          <p class="empty-icon" aria-hidden="true">📋</p>
          <p class="muted">Aucune donnée à afficher pour le moment.</p>
        </div>
      <?php else : ?>
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <?php foreach ($columns as $column) : ?>
                  <th scope="col"><?= $this->e($humanize((string) $column)) ?></th>
                <?php endforeach; ?>
                <?php if (!empty($pending)) : ?><th scope="col">Décision</th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row) : ?>
                <tr>
                  <?php foreach ($columns as $column) : ?>
                    <?php
                    $label = $humanize((string) $column);
                    $value = $row[$column] ?? null;
                    $class = $badge((string) ($value ?? ''));
                    ?>
                    <td data-label="<?= $this->e($label) ?>">
                      <?php if ($class !== '') : ?>
                        <span class="pill <?= $class ?>"><?= $this->e((string) $value) ?></span>
                      <?php elseif ($column === 'stock_qty' && (int) $value <= 3) : ?>
                        <span class="pill warn"><?= (int) $value ?> — stock faible</span>
                      <?php elseif (is_numeric($value) && preg_match('/price|total|ttc|amount/i', (string) $column) === 1) : ?>
                        <?= number_format((float) $value, 2, ',', ' ') ?> DT
                      <?php else : ?>
                        <?= $this->e($value === null || $value === '' ? '—' : (string) $value) ?>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>

                  <?php if (!empty($pending)) : ?>
                    <td data-label="Décision">
                      <form method="post" action="/admin/vendeurs/<?= (int) ($row['id'] ?? 0) ?>/valider" class="stack">
                        <input type="hidden" name="_token" value="<?= $this->e($csrf ?? '') ?>">
                        <label class="sr-only" for="reason-<?= (int) ($row['id'] ?? 0) ?>">Motif du refus (facultatif)</label>
                        <input id="reason-<?= (int) ($row['id'] ?? 0) ?>" type="text" name="reason" maxlength="255"
                               placeholder="Motif si refus" aria-describedby="reason-help-<?= (int) ($row['id'] ?? 0) ?>">
                        <span class="sr-only" id="reason-help-<?= (int) ($row['id'] ?? 0) ?>">Obligatoire en cas de refus.</span>
                        <div class="inline">
                          <button class="btn small" name="decision" value="validée" type="submit">Valider</button>
                          <button class="btn small danger" name="decision" value="refusée" type="submit">Refuser</button>
                        </div>
                      </form>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <?php if ($boContext === 'admin') : ?>
      <p class="small muted">🔒 Chaque validation est horodatée et journalisée (RG-06 / RG-11).</p>
    <?php endif; ?>
  </div>
</div>

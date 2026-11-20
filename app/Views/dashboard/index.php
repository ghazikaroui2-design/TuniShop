<h1><?= $this->e($pageTitle) ?></h1>
<p class="muted">Espace <?= $this->e($context) ?> · données en temps réel depuis la base TuniShop.</p>

<?php if ($stats !== []) : ?>
  <div class="grid stats">
    <?php foreach ($stats as $key => $value) : ?>
      <div class="card stat">
        <span class="muted small"><?= $this->e(str_replace('_', ' ', (string) $key)) ?></span>
        <strong><?= is_numeric($value) ? number_format((float) $value, 2, ',', ' ') : $this->e((string) $value) ?></strong>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<section class="card">
  <h2><?= $this->e($listTitle) ?></h2>
  <?php if ($rows === []) : ?>
    <p class="muted">Aucune donnée.</p>
  <?php else : ?>
    <table>
      <thead><tr>
        <?php foreach ($columns as $column) : ?><th><?= $this->e(str_replace('_', ' ', $column)) ?></th><?php endforeach; ?>
        <?php if (!empty($pending)) : ?><th>Décision</th><?php endif; ?>
      </tr></thead>
      <tbody>
        <?php foreach ($rows as $row) : ?>
          <tr>
            <?php foreach ($columns as $column) : ?>
              <td><?= $this->e($row[$column] ?? '—') ?></td>
            <?php endforeach; ?>
            <?php if (!empty($pending)) : ?>
              <td>
                <form method="post" action="/admin/vendeurs/<?= (int) $row['id'] ?>/valider" class="inline">
                  <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
                  <button class="btn small" name="decision" value="validée" type="submit">Valider</button>
                  <button class="btn small secondary" name="decision" value="refusée" type="submit">Refuser</button>
                </form>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

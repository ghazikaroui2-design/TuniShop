<?php
$status = (string) ($shop['status'] ?? 'en_attente');
$bannerClass = $status === 'validée' ? 'status-banner' : 'status-banner warn';
$bannerText = match ($status) {
    'validée' => '✓ Votre compte vendeur est validé. Votre boutique est visible dans le catalogue.',
    'en_attente' => '◷ Votre boutique est en attente de validation par l’administration (RG-06).',
    default => '✕ Votre boutique a été refusée. Mettez vos coordonnées à jour puis contactez l’administration.',
};
?>

<div class="bo-shell">
  <aside class="bo-menu" aria-label="Menu vendeur">
    <div class="bo-brand">TuniShop<small>ESPACE VENDEUR</small></div>
    <ul>
      <li><a href="/vendeur">▤ Tableau de bord</a></li>
      <li><a href="/vendeur/boutique" aria-current="page">◈ Ma boutique</a></li>
      <li><a href="/vendeur/produits">◍ Produits &amp; stocks</a></li>
      <li><a href="/vendeur/commandes">📦 Commandes</a></li>
      <li><a href="/">← Voir le site</a></li>
    </ul>
  </aside>

  <div class="bo-content">
    <nav class="bo-tabs" aria-label="Navigation rapide">
      <a href="/vendeur">Tableau de bord</a>
      <a href="/vendeur/boutique" aria-current="page">Boutique</a>
      <a href="/vendeur/produits">Produits</a>
      <a href="/vendeur/commandes">Commandes</a>
    </nav>

    <div class="bo-head">
      <div>
        <h1>Ma boutique</h1>
        <p class="muted small">Ces informations apparaissent sur votre vitrine et dans le catalogue.</p>
      </div>
      <button class="btn" type="submit" form="shop-form">Enregistrer les modifications</button>
    </div>

    <div class="<?= $bannerClass ?>" role="status"><?= $this->e($bannerText) ?></div>
    <?php if ($status === 'refusée' && !empty($shop['rejection_reason'])) : ?>
      <div class="alert err" role="alert">Motif du refus : <?= $this->e($shop['rejection_reason']) ?></div>
    <?php endif; ?>

    <form id="shop-form" method="post" action="/vendeur/boutique" class="card">
      <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
      <fieldset class="fieldset-reset">
        <legend class="sr-only">Informations de la boutique</legend>
        <div class="form-grid">
          <div class="field">
            <label for="shop-name">Nom de votre atelier <span aria-hidden="true">*</span></label>
            <input id="shop-name" name="name" type="text" maxlength="120" required
                   value="<?= $this->e($shop['name']) ?>" autocomplete="organization">
            <span class="hint">Ex. Atelier Bleu de Sidi Bou</span>
          </div>
          <div class="field">
            <label for="shop-neighborhood">Quartier / adresse <span aria-hidden="true">*</span></label>
            <input id="shop-neighborhood" name="neighborhood" type="text" maxlength="100" required
                   value="<?= $this->e($shop['neighborhood']) ?>">
            <span class="hint">Ex. Sidi Bou Saïd, Tunis</span>
          </div>
          <div class="field full">
            <label for="shop-address">Adresse complète <span aria-hidden="true">*</span></label>
            <input id="shop-address" name="address" type="text" maxlength="255" required
                   value="<?= $this->e($shop['address']) ?>" autocomplete="street-address">
          </div>
          <div class="field full">
            <label for="shop-description">Description courte</label>
            <textarea id="shop-description" name="description" rows="4" maxlength="2000"
                      aria-describedby="shop-description-help"><?= $this->e($shop['description'] ?? '') ?></textarea>
            <span class="hint" id="shop-description-help">Présentez votre savoir-faire en quelques phrases (2000 caractères maximum).</span>
          </div>
          <div class="field">
            <label for="shop-phone">Téléphone de contact <span aria-hidden="true">*</span></label>
            <input id="shop-phone" name="phone" type="tel" maxlength="30" required
                   value="<?= $this->e($shop['phone']) ?>" autocomplete="tel">
          </div>
          <div class="field">
            <label for="shop-email">Email de contact <span aria-hidden="true">*</span></label>
            <input id="shop-email" name="email" type="email" maxlength="190" required
                   value="<?= $this->e($shop['email']) ?>" autocomplete="email">
          </div>
          <div class="field full">
            <span class="field-label">Logo de la boutique</span>
            <div class="thumb small" role="img" aria-label="Zone de dépôt du logo de la boutique">
              <span aria-hidden="true">🖼️</span>
            </div>
            <span class="hint">L’upload sécurisé du logo sera disponible au prochain sprint. Les champs textuels sont dès à présent modifiables.</span>
          </div>
        </div>
      </fieldset>
      <p class="small muted">Les champs marqués d’un astérisque sont obligatoires.</p>
      <button class="btn" type="submit">Enregistrer les modifications</button>
    </form>
  </div>
</div>

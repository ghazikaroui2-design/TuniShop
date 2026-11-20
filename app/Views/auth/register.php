<div class="form-card">
  <h1>Créer un compte client</h1>
  <p class="muted">Inscription gratuite, réservée aux acheteurs. Les vendeurs sont validés par l’administration (RG-06).</p>

  <form method="post" action="/inscription" class="card form">
    <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
    <div class="field">
      <label for="first_name">Prénom</label>
      <input id="first_name" name="first_name" required autocomplete="given-name">
    </div>
    <div class="field">
      <label for="last_name">Nom</label>
      <input id="last_name" name="last_name" required autocomplete="family-name">
    </div>
    <div class="field">
      <label for="email">Adresse email</label>
      <input id="email" type="email" name="email" required autocomplete="email">
    </div>
    <div class="field">
      <label for="phone">Téléphone (facultatif)</label>
      <input id="phone" type="tel" name="phone" placeholder="+216 XX XXX XXX" autocomplete="tel">
    </div>
    <div class="field">
      <label for="password">Mot de passe</label>
      <input id="password" type="password" name="password" required autocomplete="new-password"
             data-password-meter-input aria-describedby="password-help password-strength">
      <span class="hint" id="password-help">8 caractères minimum, avec majuscule, minuscule, chiffre et symbole.</span>
      <span class="hint" id="password-strength" data-password-meter-label aria-live="polite"></span>
      <span class="password-meter" aria-hidden="true"><span data-password-meter-bar></span></span>
    </div>
    <button class="btn large" type="submit">Créer mon compte</button>
    <p class="muted small">Déjà inscrit ? <a href="/connexion">Se connecter</a></p>
  </form>
</div>

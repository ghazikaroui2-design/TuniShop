<div class="form-card">
  <h1>Connexion</h1>
  <p class="muted">Retrouvez vos commandes, vos adresses et votre panier.</p>

  <div class="alert info" role="note">
    Compte de démonstration : <code>aymen.benali@tunishop.tn</code> / <code>Test1234!</code>
  </div>

  <form method="post" action="/connexion" class="card form">
    <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
    <div class="field">
      <label for="email">Adresse email</label>
      <input id="email" type="email" name="email" required autocomplete="email" placeholder="vous@exemple.tn">
    </div>
    <div class="field">
      <label for="password">Mot de passe</label>
      <input id="password" type="password" name="password" required autocomplete="current-password">
    </div>
    <button class="btn large" type="submit">Se connecter</button>
    <p class="muted small">Pas encore de compte ? <a href="/inscription">S’inscrire</a></p>
  </form>
</div>

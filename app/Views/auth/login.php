<h1>Connexion</h1>
<form method="post" action="/connexion" class="card form">
  <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
  <label for="email">Adresse email</label>
  <input id="email" type="email" name="email" required autocomplete="email" placeholder="aymen.benali@tunishop.tn">
  <label for="password">Mot de passe</label>
  <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Test1234!">
  <button class="btn" type="submit">Se connecter</button>
  <p class="muted small">Compte de démo : aymen.benali@tunishop.tn / Test1234!</p>
  <p class="muted small">Pas encore de compte ? <a href="/inscription">S’inscrire</a></p>
</form>

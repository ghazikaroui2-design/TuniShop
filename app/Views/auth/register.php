<h1>Créer un compte client</h1>
<form method="post" action="/inscription" class="card form">
  <input type="hidden" name="_token" value="<?= $this->e($csrf) ?>">
  <label for="first_name">Prénom</label>
  <input id="first_name" name="first_name" required>
  <label for="last_name">Nom</label>
  <input id="last_name" name="last_name" required>
  <label for="email">Adresse email</label>
  <input id="email" type="email" name="email" required autocomplete="email">
  <label for="phone">Téléphone (facultatif)</label>
  <input id="phone" name="phone" placeholder="+216 XX XXX XXX">
  <label for="password">Mot de passe (8 caractères minimum)</label>
  <input id="password" type="password" name="password" required autocomplete="new-password">
  <button class="btn" type="submit">Créer mon compte</button>
  <p class="muted small">Déjà inscrit ? <a href="/connexion">Se connecter</a></p>
</form>

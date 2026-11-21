# Questions de jury — TuniShop Marketplace

> 25 questions fréquentes, classées par thème. Chaque réponse est courte, factuelle et renvoie vers une preuve documentée ou un test. Règle d'or : **ne jamais improviser un chiffre**.

---

## 1. Gestion de projet & méthode

### Q1. Pourquoi Scrum plutôt que Cycle en V ?
**R.** Le périmètre d'un projet pluridisciplinaire de 10 semaines évolue (retours UX, contraintes sécurité découvertes en S4). Scrum permet de geler le **Must** à S2 (DEC-004) et d'absorber les changements dans les sprints suivants via le backlog. Les revues aux jalons S2/S5/S8 correspondent au calendrier pédagogique. Preuve : [suivi_sprints.md](../01_gestion_projet/suivi_sprints.md) §2 et §3.

### Q2. Comment avez-vous estimé les 183 points ?
**R.** Planning poker sur 58 US, réparties en 10 sprints de 2 semaines, avec vélocité visée de 36,6 points/sprint. La charge est volontairement concentrée sur S2–S4 (121 points). Un écart supérieur à une semaine déclenche le basculement des US Should/Could en réserve V1.1. Preuve : backlog §4 et burndown du suivi des sprints.

### Q3. Quelles sont vos anomalies ouvertes aujourd'hui ?
**R.** Deux majeures : A-01 (noms de l'équipe) et A-02 (remote GitHub). Les autres sont mineures et reportées V1.1 (A-03 images, A-04 logo, A-06/A-07 sécurité). **Aucune bloquante.** A-05 (SEO technique) est clôturée depuis le 21/11. Preuve : [liste_anomalies.md](../01_gestion_projet/liste_anomalies.md).

---

## 2. Architecture & POO

### Q4. Pourquoi un MVC maison plutôt que Laravel ou Symfony ?
**R.** Trois raisons pédagogiques : (1) le programme demande de démontrer la **POO avancée** (classes abstraites, interfaces, namespaces, exceptions, patterns) — un framework masquerait ces mécanismes ; (2) l'équipe maîtrise ainsi chaque couche et peut expliquer le cycle complet d'une requête ; (3) zéro dépendance applicative, donc `composer audit` ne signale que DomPDF. Le routeur, le conteneur de middlewares et le modèle Active Record léger sont documentés dans [architecture_mvc.md](../05_backend/architecture_mvc.md).

### Q5. Quels patterns avez-vous utilisés ?
**R.** Dix au minimum : MVC, Front Controller, Active Record (light), Repository/Model, Service Layer, Dependency Injection, Registry (container de middlewares), Factory (résolution `role:*`), Strategy (moyens de paiement), Template Method (layout + vues). Détail et démonstration : [poo_concepts.md](../05_backend/poo_concepts.md) §4.

### Q6. Comment une requête `/produit/vase-bleu-et-blanc` est-elle traitée ?
**R.** `public/index.php` construit la `Request` → `Application` pose le contexte d'audit → `Router` parcourt les 26 routes, correspond à `GET /produit/{slug}`, injecte `slug` en paramètre nommé → `CatalogController@show` appelle `Product::findBySlug()` → la vue `catalog/show.php` est rendue dans le layout → `Response::securityHeaders()` ajoute CSP et consorts. Preuve : T12 et [architecture_mvc.md](../05_backend/architecture_mvc.md) §3.

### Q7. Comment évitez-vous les requêtes SQL concaténées ?
**R.** Toutes les valeurs passent par des **requêtes préparées** PDO (`execute($sql, $params)`), avec émulation désactivée. Pour les identifiants (noms de tables/colonnes), qui ne peuvent pas être paramétrés, `Database::identifier()` applique une **whitelist regex** `^[A-Za-z_][A-Za-z0-9_]*$`. Test T15 / S15.

---

## 3. Sécurité

### Q8. Pourquoi Argon2id plutôt que `md5` ou même bcrypt ?
**R.** Argon2id est le vainqueur du Password Hashing Competition, résistant aux attaques par GPU et par canal auxiliaire. PHP 8.3 le fournit nativement via `password_hash(..., PASSWORD_ARGON2ID)`. Le projet utilise aussi un hash fictif pour les emails inconnus afin d'égaliser les temps de réponse (anti-énumération). Preuve : [authentification.md](../07_securite/authentification.md) + tests S09, S14.

### Q9. Comment fonctionne votre protection anti-force-brute ?
**R.** `LoginThrottle` autorise 5 tentatives par fenêtre de 15 minutes, puis verrouille pour 15 minutes. Le compteur est incrémenté aussi bien sur email inconnu que sur mot de passe erroné, avec un message générique identique. Les verrous et les réussites sont journalisés (RG-11). Tests S12–S14.

### Q10. Qu'est-ce que le CSRF et comment le gérez-vous ?
**R.** Une attaque CSRF force un utilisateur connecté à soumettre un formulaire à son insu. Chaque session porte un jeton aléatoire inclus dans les formulaires (`_token`) ; `CsrfMiddleware` le compare sur tous les POST et renvoie **419** sinon. Le jeton est **régénéré à la connexion** avec la session (`Session::regenerate()`). Tests T07/T08, S05/S06.

### Q11. Votre RBAC est-il vérifié côté serveur ?
**R.** Oui, exclusivement. Les routes déclarent des middlewares (`auth`, `role:vendeur`, `role:admin`) résolus par une factory. Le menu du layout n'est qu'un confort visuel : si un client tape `/admin` directement, `RoleMiddleware` le refuse et journalise `ACCESS_DENIED`. Test S07 et [rbac.md](../07_securite/rbac.md).

### Q12. Comment empêchez-vous l'IDOR sur les factures ?
**R.** `OrderController::loadOrderForCurrentUser()` vérifie la propriété de la commande avant tout rendu ou téléchargement. Un client qui demande la facture d'un autre reçoit une **404 neutre** — pas une 403 qui confirmerait l'existence de la ressource. Test E15.

### Q13. Votre CSP autorise-t-elle le JavaScript inline ?
**R.** Non. `script-src 'self'` (plus un nonce par requête), avec `default-src 'self'`, `object-src 'none'`, `frame-ancestors 'none'`, `form-action 'self'`. Tout le JS exécutable est dans `public/assets/js/app.js`, chargé en `defer`. Les seules autres balises `<script>` sont des **data blocks** `type="application/ld+json"` générés par PHP et portant le nonce : ils ne s'exécutent pas et sont autorisés nommément, sans `'unsafe-inline'`. Tests F03/F04, S03 et SEO-05/SEO-06/SEO-12. Voir DEC-011 dans [plan_seo.md](../08_seo_marketing/plan_seo.md) §3.1.

### Q14. RG-07 : comment prouvez-vous qu'aucune donnée bancaire n'est stockée ?
**R.** Trois niveaux : (1) les champs carte du checkout n'ont **pas d'attribut `name`** → le navigateur ne les soumet pas ; (2) la table `payments` ne contient aucune colonne bancaire ; (3) le service ne reçoit que `confirm_sandbox=1`. Test E04 vérifie l'absence de `name="card_number"` / `name="cvc"`, E10 vérifie le schéma, T23 vérifie l'audit. Les tests utilisent `4242 4242 4242 4242`.

---

## 4. Base de données & règles métier

### Q15. Que se passe-t-il si un client augmente la quantité au-delà du stock ?
**R.** Deux barrières : `CartService`/`InputSanitizer` bornent l'entrée côté PHP, et un **trigger SQL** lève `SIGNAL SQLSTATE '45000'` si la quantité dépasse `stock_qty`. Test E07 et T23. C'est volontaire : la règle RG-01 reste vraie même si un autre client (script, admin) écrit directement en base.

### Q16. Comment calculez-vous la TVA incluse dans un prix TTC ?
**R.** `HT = TTC / 1,19`, `TVA = TTC − HT`. Pour 86,00 DT : HT 72,27 + TVA 13,73 = 86,00 (avec arrondi au centime sur la TVA pour garantir la somme exacte). Tests T05/T06/T07 et [dictionnaire_donnees.md](../04_bdd/dictionnaire_donnees.md) RG-02.

### Q17. Comment êtes-vous sûr qu'un numéro de facture n'est pas dupliqué ?
**R.** RG-08 utilise une table `sequences` avec clé `(seq_name, seq_year)` et une procédure qui incrémente `next_value` **dans la même transaction** que la commande. Deux paiements concurrents ne peuvent pas obtenir le même `F-2026-NNNN`. Test T16 et [facturation_pdf.md](../06_ecommerce/facturation_pdf.md).

### Q18. Pourquoi avoir mis des règles dans des triggers plutôt que dans le code ?
**R.** Parce que le code applicatif n'est pas le seul point d'entrée : une procédure, un import SQL ou un futur module mobile pourraient contourner un `if` PHP. La base est le dernier garde-fou. Les 11 triggers et 13 procédures sont listés dans [README BDD](../04_bdd/README.md).

---

## 5. Frontend & accessibilité

### Q19. Que se passe-t-il si JavaScript est désactivé ?
**R.** Rien de cassé : les quantités utilisent un formulaire POST, les filtres un GET, les onglets affichent leur panneau par défaut, les toasts sont des messages HTML rendus par PHP. Le JS n'apporte que du confort (incrémentation, recherche instantanée, formatage carte). C'est le principe d'amélioration progressive, documenté dans [comportements_js.md](../03_frontend/comportements_js.md).

### Q20. Comment garantissez-vous l'accessibilité ?
**R.** Skip link, un `h1` par page, `aria-current`, `aria-selected` sur les onglets avec navigation `←`/`→`/`Home`/`End`, `role="status"`/`alert` sur les toasts, libellés associés à chaque champ, tableaux mobiles reconstitués avec `data-label`, focus visible à 3 px, `prefers-reduced-motion`. Cible WCAG 2.1 AA, détaillée dans [accessibilite_responsive.md](../03_frontend/accessibilite_responsive.md). Test F06 vérifie les tokens CSS et les media queries.

---

## 6. Questions pièges / limites assumées

### Q21. Votre paiement est-il réel ?
**R.** Non, et c'est une exigence du cahier des charges (DEC-005). C'est un **sandbox simulé** côté serveur : `PaymentService` reconnaît la carte de test, génère une référence `SBX-…`, crée la facture et met la commande en `payée`. Aucun appel réseau externe, aucune donnée bancaire. L'UI l'affiche explicitement pour éviter toute confusion (risque R-07).

### Q22. Pourquoi n'avez-vous pas versionné les images produits ?
**R.** Choix volontaire : `.gitignore` exclut `public/uploads/*` pour ne pas alourdir le dépôt et éviter de versionner des fichiers binaires. Le contrôleur détecte l'absence du fichier et affiche une illustration textuelle accessible. C'est l'anomalie A-03, reportée en V1.1 avec l'upload sécurisé.

### Q23. Quelle est votre plus grosse dette technique ?
**R.** L'absence de remote GitHub (A-02) et les noms de l'équipe (A-01) : deux points organisationnels, pas techniques. Côté code, la limite principale est l'absence de tests navigateur (Selenium/Playwright) : nos recettes sont structurelles (F01–F12) et SEO (SEO-01–SEO-12), pas visuelles.

### Q24. Si vous recommenciez, que changeriez-vous ?
**R.** J'écrirais les tests SEO et de rendu HTML **en même temps** que le layout — c'est ce que nous avons fait avec `tests/seo.php`, ce qui a permis de clôturer A-05 en une demi-journée ; et je demanderais l'URL du dépôt Git dès le Sprint 1 plutôt qu'à la fin (A-02 reste ouvert).

### Q25. Combien de temps avez-vous passé sur la sécurité ?
**R.** Le durcissement a été intégré dès le socle (S5) puis consolidé en S9 : Argon2id, throttle, CSRF rotatif, RBAC, en-têtes globaux, audit. 18 tests dédiés et un dossier de 6 documents. Le résultat est mesurable : `composer audit` propre et zéro donnée bancaire stockée. Voir [README sécurité](../07_securite/README.md).

---

## Astuce finale

Si une question porte sur un chiffre que vous ne savez pas par cœur, répondez :

> « Je préfère vous renvoyer à la source plutôt que de donner un chiffre approximatif : c'est dans `<document>` et c'est vérifiable par `<commande>`. »

Cette réponse est mieux notée qu'une approximation.

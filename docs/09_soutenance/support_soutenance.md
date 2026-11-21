# Support de soutenance — TuniShop Marketplace

> Diaporama 16:9 — 13 slides — 15 minutes de présentation, 10 minutes de démonstration, 5 minutes de questions.
> Chaque slide ci-dessous contient le **titre à afficher**, les **messages clés** à dire et l'**appui visuel** à montrer.

---

## Slide 1 — Ouverture (1 min)

**Titre :** 🏺 TuniShop — La marketplace de l'artisanat de Sidi Bou Saïd

**_messages clés_**
- « Bonjour, nous présentons TuniShop, une plateforme e-commerce dédiée aux artisans et commerçants de Sidi Bou Saïd. »
- « Projet pluridisciplinaire BTS DI 2-1, réalisé du 16 novembre 2026 au 25 janvier 2027. »
- « 8 matières, 5 sprints, 58 user stories, 106 tests automatisés au vert. »

**Appui :** captured'écran de l'accueil (écran 1) + logo 🏺.

---

## Slide 2 — Le problème et l'opportunité (1 min)

**Titre :** Un savoir-faire local, invisible en ligne

| Constat (CDC §1.2) | Conséquence |
|---|---|
| Artisans uniquement physiques, pas de vitrine en ligne | Aucune vente hors du village |
| Aucun référencement local structurant | Invisible sur « céramique Sidi Bou Saïd » |
| Paiement et facturation manuels | Perte de temps, aucune traçabilité |

**_Messages clés_**
- « Le besoin n'est pas seulement technique : c'est une question de visibilité et de transmission d'un savoir-faire. »
- « Notre réponse : une marketplace locale, simple, sécurisée, qui valorise chaque atelier. »

**Appui :** comparaison avant/après (2 colonnes).

---

## Slide 3 — Objectifs & périmètre (1 min)

**Titre :** 4 objectifs, un périmètre gelé

1. **Découvrir** — catalogue filtrable, 21 produits publiés, 6 coups de cœur.
2. **Acheter** — panier persistant, checkout 3 étapes, paiement simulé, facture PDF.
3. **Gérer** — boutique vendeur, stocks, commandes, validation par l'administrateur.
4. **Durcir** — Argon2id, RBAC, CSRF, CSP, audit des actions sensibles.

**_Messages clés_**
- « Périmètre Must gelé à la fin du Sprint 2 (DEC-004) : 58 US priorisées en MoSCoW sur 183 points. »
- « Le paiement est **simulé** par exigence du cahier des charges (DEC-005) : aucune donnée bancaire transmise. »

**Appui :** extrait de la matrice MoSCoW du backlog.

---

## Slide 4 — Organisation Agile (1 min)

**Titre :** 5 sprints, 5 rôles, des jalons respectés

| Sprint | Objectif | Points | Livraison |
|---|---|---:|---|
| S1 | Cadrage, UX, Git | 27 | ✅ |
| S2 | Design + SQL | 40 | ✅ anticipé |
| S3 | MVC + responsive | 41 | ✅ anticipé |
| S4 | Achat + sécurité | 45 | ✅ anticipé |
| S5 | Audit, SEO, soutenance | 30 | 🔵 en cours |

**_Messages clés_**
- « Daily, planning, revue, rétrospective : tous les rituels sont documentés avec des modèles de comptes rendus. »
- « La courbe réelle est en avance sur le burndown prévisionnel : les modules des sprints 2 à 4 ont été livrés avant le 21/11. »
- « Équipe de 5 : Ghazi (PO), Yessine (UX), Siwar (front), Maram (back/e-commerce), Islem (BDD/sécurité/SEO). »

**Appui :** burndown du [suivi des sprints](../01_gestion_projet/suivi_sprints.md).

---

## Slide 5 — Architecture (1,5 min)

**Titre :** Un MVC maison, clair et testable

```
Request → Router (26 routes, middlewares) → Controller
                                              ↓
                          Service (métier + PDO préparé)
                                              ↓
                          Model / MySQL InnoDB (20 tables)
                                              ↓
                          View + layout → HTML / CSS / JS externe
```

**_Messages clés_**
- « Un seul point d'entrée HTTP : `public/index.php`. Le routeur injecte les paramètres nommés et applique le pipeline de middlewares. »
- « Les services portent la logique transactionnelle : panier, commande, paiement, facture, audit. »
- « Les vues PHP sont échappées centralement par `View::e()`, ce qui bloque les XSS à la source. »

**Appui :** [architecture_mvc.md](../05_backend/architecture_mvc.md) + [diagrammes UML](../05_backend/diagrammes_uml.md).

---

## Slide 6 — Base de données (1 min)

**Titre :** 20 tables, des règles métier *dans* la base

| Élément | Rôle |
|---|---|
| 11 triggers | Stock, calcul TVA, facturation, transitions de statut, audit RG-11 |
| 13 procédures | Checkout transactionnel, paiement sandbox, statistiques |
| 6 vues | `v_catalogue`, `v_cart_totals`, `v_orders_detail`, `v_low_stock`… |
| 25 tests SQL | RG-01 à RG-11 vérifiés, garde-fous `SIGNAL SQLSTATE 45000` |

**_Messages clés_**
- « Les règles métier ne sont pas seulement dans le code : elles sont garanties par la base, donc impossibles à contourner. »
- « Exemple : une quantité supérieure au stock est refusée par un trigger, pas seulement par un `if` PHP. »

**Appui :** extrait de `database/tests.sql` (T22 à T25).

---

## Slide 7 — Parcours client (démo — 4 min)

**Titre :** De la découverte à la facture PDF

**Script de démonstration (à exécuter en direct) :**

1. `/` → « Coups de cœur des artisans » — cliquer sur **Vase bleu et blanc**.
2. Fiche produit → onglets **Description / Avis (12) / Livraison** (navigation clavier `←` `→`).
3. Quantité **2** via `+` → **Ajouter au panier** (toast de confirmation).
4. `/panier` → modifier la quantité, observer HT / TVA 19 % / livraison 8 DT / **TOTAL TTC**.
5. `/commande` → étape 1 livraison, étape 2 paiement.
6. Saisir `4242 4242 4242 4242` + code promo `ARTISAN5` → **Payer 89,00 DT**.
7. `/commande/{id}` → confirmation + **Télécharger la facture PDF**.

**_Messages clés_**
- « Regardez le montant : le code promo est calculé côté serveur, jamais dans le navigateur. »
- « Les champs de carte n'ont **pas d'attribut `name`** : ils ne sont jamais soumis. Seul `confirm_sandbox=1` part au serveur. »

**Compte :** `aymen.benali@tunishop.tn` / `Test1234!`

---

## Slide 8 — Parcours vendeur & admin (démo — 3 min)

**Titre :** Une boutique gérée, une validation contrôlée

**Vendeur** (`fatma.bensalah@tunishop.tn`) :
1. `/vendeur` → KPI + alertes stock (badges « stock faible »).
2. `/vendeur/boutique` → modifier adresse, quartier, description → **Enregistrer**.
3. `/vendeur/produits` et `/vendeur/commandes` → tableaux avec `data-label` sur mobile.

**Admin** (`ghazi.karoui@tunishop.tn`) :
1. `/admin` → graphique 7 jours, KPI `sp_get_dashboard_stats`.
2. `/admin/vendeurs` → 3 boutiques en attente (RG-06).
3. Bouton **Valider / Refuser** avec motif obligatoire en cas de refus → action journalisée RG-11.

**_Messages clés_**
- « Une seule vue `dashboard/index.php` sert les 4 écrans de back-office : paramétrée par le contrôleur, elle évite la duplication. »
- « Le refus demande un motif : la décision est traçable et explicable au vendeur. »

---

## Slide 9 — Intégration Frontend (1,5 min)

**Titre :** 12 écrans, un design system, zéro script inline

| Dispositif | Résultat |
|---|---|
| Tokens CSS « Bleu de Sidi Bou » | une seule source de vérité couleurs / espacements |
| 3 breakpoints | 320 / 640 / 1024 px+, reflow sans défilement horizontal |
| WCAG 2.1 AA | skip link, focus visible, ARIA, tableaux `data-label` |
| JS externe unique | 12 hooks `data-*`, chargé en `defer` |
| CSP `script-src 'self'` + nonce | aucun script exécutable inline, aucun attribut `on*` |

**_Messages clés_**
- « L'amélioration progressive : coupez JavaScript, tous les formulaires et liens fonctionnent encore. »
- « Les images absentes ne cassent pas la page : un `role="img"` + `aria-label` prend le relais. »

**Appui :** bascule 320 / 640 / 1024 px dans le navigateur.

---

## Slide 10 — Sécurité (1,5 min)

**Titre :** Défense en profondeur, preuve à l'appui

| Contrôle | Preuve |
|---|---|
| Argon2id + politique de mot de passe | test S09–S11 |
| 5 tentatives / 15 min + anti-énumération | S12–S14 |
| CSRF rotatif + RBAC déclaratif | S05–S07 |
| CSP + nosniff + anti-clickjacking sur **toutes** les réponses | S01–S03 |
| Aucun mot de passe dans les logs d'audit | S17 |
| Aucune donnée bancaire stockée | E10 + T23 |

**_Messages clés_**
- « 18 tests de sécurité, tous verts. La sécurité n'est pas une option de fin de projet : elle est dans le socle dès le premier commit. »
- « Limites assumées et documentées : pas de 2FA ni de limite de débit globale — anomalies A-06 et A-07, reportées en V1.1. »

**Appui :** `docs/07_securite/owasp_top10.md`.

---

## Slide 11 — SEO & acquisition (1 min)

**Titre :** Référencer l'artisanat local

- ✅ 10 critères sur 12 conformes (URLs, title/description, Hn, alt, lang + canonical, JSON-LD, sitemap, robots).
- ✅ P0 livré : canonical absolu, JSON-LD `Product`/`Organization`/`WebSite`/`BreadcrumbList`, `sitemap.xml` (23 URL), `robots.txt`, `X-Robots-Tag` — 12 tests SEO verts.
- 🎯 KPI (P1) : impressions organiques, position sur « artisanat Sidi Bou Saïd », CTR, taux de conversion.

**_Messages clés_**
- « Le JSON-LD est généré par PHP et autorisé par un nonce CSP : Google le lit dans le HTML, sans JavaScript et sans `'unsafe-inline'`. »
- « Le référencement local est notre avantage concurrentiel : la concurrence n'a pas de structure SEO. »

**Appui :** [plan_seo.md](../08_seo_marketing/plan_seo.md).

---

## Slide 12 — Recette & qualité (1 min)

**Titre :** 106 tests, 0 échec

| Suite | Tests |
|---|---:|
| Socle MVC | 24 |
| E-commerce | 15 |
| Sécurité | 18 |
| Frontend | 12 |
| SEO | 12 |
| SQL | 25 |
| **Total** | **106 PASS / 0 FAIL** ✅ |

**_Messages clés_**
- « Chaque module livré sauf le dernier a sa propre suite de recette exécutable en une commande. »
- « `composer audit` ne signale aucune vulnérabilité ; 67 fichiers PHP lintés sans erreur. »
- « Aucune anomalie bloquante ou majeure ouverte : 7 corrigées, 5 reportées V1.1, toutes documentées et priorisées. »

**Appui :** sortie de `composer test:all` + [liste_anomalies.md](../01_gestion_projet/liste_anomalies.md).

---

## Slide 13 — Conclusion & perspectives (1 min)

**Titre :** Ce que nous livrons, ce que nous ferions ensuite

**Livré :** 8 matières · 12 écrans · 26 routes · 20 tables · 106 tests · 9 dossiers documentaires · SEO P0 complet.

**V1.1 / P1 (reporté, non bloquant) :**
- Upload des visuels et du logo boutique (A-03, A-04).
- 2FA vendeurs/admin et limite de débit (A-06, A-07).
- Open Graph, enrichissement des descriptions et suivi KPI (P1 SEO).
- Remote GitHub + tags `v0.1` / `v0.5` / `v1.0` poussés — A-02 clôturée.

**_Message final_**
- « TuniShop est une démonstration complète et sécurisée d'une marketplace locale. Les choix techniques — MVC maison, règles dans la base, sécurité dès le socle, JS externe — ont été faits pour que le projet reste compréhensible, maintenable et évolutif. Merci. »

---

## Annexe A — Checklist matériel (à vérifier 30 min avant)

- [ ] XAMPP démarré (Apache + MySQL), base `tunishop` importée.
- [ ] `php -S localhost:8000 -t public` lancé **ou** Apache configuré avec `DocumentRoot = public/`.
- [ ] `composer audit` et `composer test:all` exécutés et affichés en sauvegarde.
- [ ] Comptes démo testés : admin, vendeur, client (`Test1234!`).
- [ ] Carte sandbox `4242 4242 4242 4242` et codes `ARTISAN5` / `BIENVENUE10` notés sur un post-it.
- [ ] Navigateurs : Chrome (démo) + Firefox (contrôle responsive).
- [ ] PDF de la facture de démonstration déjà généré dans `public/factures/` (plan B si DomPDF échoue).
- [ ] Captures d'écran des 12 écrans dans `docs/02_ux_ui/` (plan B si le serveur tombe).
- [ ] Diaporama exporté en PDF *et* conservé en `.pptx`/`.odp`.

## Annexe B — Plan B en cas de problème technique

| Incident | Réaction |
|---|---|
| Serveur PHP indisponible | Ouvrir `docs/02_ux_ui/prototype_tunishop.html` (12 écrans cliquables, hors ligne). |
| Base MySQL inaccessible | Montrer les sorties de `composer test:all` enregistrées + `database/tests.sql`. |
| DomPDF échoue | Ouvrir le PDF déjà généré dans `public/factures/`. |
| Connexion démo refusée (verrou 5 tentatives) | Attendre 15 min ou utiliser un autre compte démo ; expliquer le throttle comme une preuve de sécurité. |
| Question technique sans réponse | « Je note la question, je la documente dans `questions_jury.md` et je reviens vers vous par écrit. » |

## Annexe C — Attitude recommandée

- Regarder le jury, pas l'écran.
- Si vous ne savez pas : le dire, puis montrer comment vous le vérifieriez (test, log, doc).
- Ne jamais improviser un chiffre : renvoyer vers la source documentée.
- Terminer dans le temps imparti : la conclusion (slide 12) est plus importante qu'un détail de code.

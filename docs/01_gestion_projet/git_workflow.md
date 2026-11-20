# Workflow Git — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 |
| Statut local | ⚠️ Git et Composer non installés sur la machine au 20/11/2026 |
| Objectif | Coordonner 4 développeurs sur 10 semaines sans conflit et livrer un dépôt propre le 24/01/2027 |

---

## 1. Prérequis (à faire en S1)

1. Installer **Git** : <https://git-scm.com/download/win> (choisir « Git Bash » + « Add to PATH »).
2. Installer **Composer** : <https://getcomposer.org/download/> (autoload PSR-4 + DomPDF).
3. Vérifier :
   ```bash
   git --version
   composer --version
   ```
4. Configurer l'identité (une seule fois) :
   ```bash
   git config --global user.name "Prenom NOM"
   git config --global user.email "prenom.nom@exemple.tn"
   git config --global core.autocrlf false
   git config --global init.defaultBranch main
   ```

---

## 2. Initialisation du dépôt

```bash
cd C:\Users\USER\OneDrive\Desktop\TuniShop
git init
git add .
git commit -m "chore: initialisation du depot et livrables gestion de projet"

# Lier le dépôt distant (à créer sur GitHub/GitLab)
git remote add origin https://github.com/<organisation>/tunishop.git
git branch -M main
git push -u origin main

# Créer la branche d'intégration
git checkout -b develop
git push -u origin develop
```

> Le dossier est synchronisé avec OneDrive : éviter de déplacer le dépôt pendant que Git travaille, et vérifier que `.gitignore` exclut bien les fichiers temporaires Office (`~$*`).

---

## 3. Modèle de branches

| Branche | Rôle | Protection |
|---|---|---|
| `main` | Version stable, démontrable (tags `v0.1`, `v0.5`, `v1.0`) | ❌ Aucun commit direct — uniquement via PR depuis `develop` |
| `develop` | Intégration continue de toutes les fonctionnalités | ❌ Aucun commit direct — uniquement via PR |
| `feature/US-xxx-court` | Une user story = une branche | ✅ Commit libre, PR vers `develop` |
| `fix/xxx` | Correction de bug ou anomalie de recette | ✅ PR vers `develop` |
| `docs/xxx` | Documentation (cahier des charges, manuel, SEO) | ✅ PR vers `develop` |
| `hotfix/xxx` | Correction urgente sur `main` (jury) | ✅ PR vers `main` **et** `develop` |

**Nommage :** `feature/US-035-panier-persistant`, `fix/US-038-carte-invalide`, `docs/US-057-manuel-utilisateur`.

### Règles d'or

1. **Jamais** de `git push` direct sur `main` ou `develop`.
2. Une branche = **une** user story (ou un bug), durée de vie ≤ 2 jours idéalement.
3. Commits **atomiques** : un commit = une modification cohérente.
4. Avant chaque PR : `git pull --rebase origin develop` puis tester la fonctionnalité.
5. Merge dans `develop` uniquement après **revue par un autre membre** et passage de la **Definition of Done** (voir backlog §6).

---

## 4. Convention de commits (Conventional Commits)

Format : `<type>(<portée>): <description à l'impératif>`

| Type | Usage | Exemple |
|---|---|---|
| `feat` | Nouvelle fonctionnalité | `feat(panier): ajout de la modification des quantités` |
| `fix` | Correction de bug | `fix(stock): blocage quand la quantité demandée est supérieure au stock` |
| `docs` | Documentation uniquement | `docs(bdd): ajout du dictionnaire de données` |
| `style` | Mise en forme, CSS, sans changement métier | `style(front): correction des breakpoints tablette` |
| `refactor` | Réécriture sans changement fonctionnel | `refactor(mvc): extraction de CartService` |
| `perf` | Amélioration des performances | `perf(sql): index sur products.category_id` |
| `test` | Ajout/modification de tests | `test(recette): scénario de paiement invalide` |
| `chore` | Outillage, config, dépendances | `chore: installation de Composer et DomPDF` |
| `security` | Correction de sécurité | `security(csrf): jeton ajouté au formulaire de commande` |

**Description :** minuscule, pas de point final, ≤ 72 caractères.

---

## 5. Exemple de session de travail

```bash
# 1. Se mettre à jour
git checkout develop
git pull origin develop

# 2. Créer sa branche
git checkout -b feature/US-035-panier-persistant

# 3. Travailler… puis enregistrer
git status
git add app/Services/CartService.php public/assets/js/cart.js
git commit -m "feat(panier): persistance du panier en session et BDD"

# 4. Se synchroniser avant la PR
git fetch origin
git rebase origin/develop

# 5. Publier la branche
git push -u origin feature/US-035-panier-persistant

# 6. Ouvrir une Pull Request sur GitHub : feature/US-035 → develop
#    Décrire : US concernée, critères d'acceptation validés, captures si UI
```

---

## 6. Tags et versions

| Tag | Date prévue | Contenu |
|---|---|---|
| `v0.1` | 20/12/2026 (Validation 2) | Maquettes + base SQL + socle MVC |
| `v0.5` | 10/01/2027 (Préjury) | Application fonctionnelle (parcours client + back-office) |
| `v1.0` | 24/01/2027 (Validation 3) | Version finale livrée au jury |

```bash
git tag -a v0.5 -m "Préjury : parcours client et back-office fonctionnels"
git push origin v0.5
```

---

## 7. Résolution des conflits

```bash
git pull --rebase origin develop
# Si conflit : ouvrir le fichier, résoudre les marqueurs <<<<<<< ======= >>>>>>>, puis :
git add <fichier>
git rebase --continue
# ou, si abandon :
git rebase --abort
```

**Prévention :** ne jamais modifier le même fichier longtemps sans tirer (`pull`), découper le code en services/classes distincts, et communiquer dans le daily.

---

## 8. Checklist avant remise finale (24/01/2027)

- [ ] Toutes les branches `feature/*` et `fix/*` sont fusionnées dans `develop`, puis `develop` dans `main`.
- [ ] `git status` propre sur `main` (aucun fichier non suivi oublié).
- [ ] `.gitignore` vérifié : ni `vendor/`, ni `.env`, ni `public/uploads/*`, ni fichiers `~$*`.
- [ ] Tags `v0.1`, `v0.5`, `v1.0` présents.
- [ ] `database/schema.sql`, `seed.sql`, `triggers.sql`, `procedures.sql` présents et exécutables.
- [ ] Le README indique la commande de démarrage et l'URL locale de la démo.
- [ ] Les 9 dossiers `docs/` sont remplis, dont le rapport d'audit et le support de soutenance.
- [ ] Un `git log --oneline` lisible prouve l'historique Agile (commits par US).

---

## 9. Commandes utiles

```bash
git log --oneline --graph --all          # Historique visuel
git diff --stat                          # Résumé des modifications
git stash && git stash pop               # Mettre de côté puis reprendre
git branch -d feature/US-xxx-court       # Supprimer une branche fusionnée
git restore <fichier>                    # Annuler les modifications locales
git reset --soft HEAD~1                  # Défaire le dernier commit en gardant les changements
```

# Intégration Frontend — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 |
| Périmètre | 12 écrans du prototype `docs/02_ux_ui/prototype_tunishop.html` |
| Stack | HTML5 sémantique, CSS custom properties, JavaScript ES5-compatible (aucune dépendance) |
| Cible | 320 px / 640 px / 1024 px et plus — WCAG 2.1 AA |
| Contrainte forte | CSP `script-src 'self'` : aucun `<script>` inline, aucun attribut `on*` |
| Recette | [12 scénarios F01–F12](recette_frontend.md) — `composer test:frontend` |

---

## 1. Livrables

| Fichier | Rôle |
|---|---|
| `public/assets/css/app.css` | Design system complet (tokens, composants, responsive, accessibilité, print) |
| `public/assets/js/app.js` | Couche d'amélioration progressive, chargée en `defer` |
| `app/Views/layouts/main.php` | Shell commun : skip link, header, recherche, fil d'Ariane, toasts, footer |
| `app/Views/{home,catalog,cart,checkout,order,auth,account,seller,dashboard}/*.php` | Les 12 écrans intégrés |
| `app/Controllers/{Account,Seller,Catalog,Cart,Order,Admin}Controller.php` | Données, breadcrumbs et descriptions de page |
| `config/routes.php` | 26 routes dont `GET /compte`, `GET|POST /vendeur/boutique` |
| `tests/frontend.php` | Recette automatisée F01–F12 |

## 2. Principes d'intégration

1. **Une source de vérité** : le prototype UX/UI (UI kit v1.0) est traduit en tokens CSS. Aucune couleur ni espacement codé en dur dans les vues.
2. **Progression** : le HTML fonctionne sans JavaScript (formulaires POST, liens, tableaux). Le JS n'apporte que du confort (quantités, suggestions, onglets).
3. **Sécurité par conception** : tout le JS est externe ; les données sont insérées avec `$this->e()` (échappement centralisé) ; les champs bancaires n'ont pas d'attribut `name` (RG-07).
4. **Accessibilité** : navigation clavier complète, `aria-current`, `aria-selected`, `role="alert"`/`status`, libellés explicites, tableaux mobiles avec `data-label`.
5. **Pas d'image cassée** : les visuels de démonstration ne sont pas versionnés. Les contrôleurs détectent l'existence du fichier et affichent une illustration textuelle à la place.

## 3. Vue d'ensemble du rendu

```
Request → Router → Contrôleur (données + contexte de vue)
                    ↓
                  View::render() → vue partielle + layout
                    ↓
              HTML sémantique + CSS externe + JS différé
                    ↓
              Response::securityHeaders() (CSP, nosniff, ...)
```

Le layout reçoit `session` depuis `Application` (données partagées), ce qui permet d'afficher le bon état de connexion, le rôle et le jeton CSRF du formulaire de déconnexion.

## 4. Démarrage

```powershell
# Base de données (une fois)
& 'C:\xampp\mysql\bin\mysql.exe' -u root -e "SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/database/schema.sql"

# Serveur intégré (document root = public/)
& 'C:\xampp\php\php.exe' -S localhost:8000 -t public

# Recette complète
& 'C:\Users\USER\AppData\Local\Composer\composer.bat' test:all
& 'C:\xampp\mysql\bin\mysql.exe' -u root -e "SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/database/tests.sql"
```

> ℹ️ Les liens internes utilisent des chemins absolus (`/catalogue`, `/compte`, …) : le document root du serveur doit pointer sur `public/`. C'est la configuration recommandée et testée.

## 5. État du projet

- ✅ 12 écrans intégrés et câblés sur la base réelle.
- ✅ Design system responsive et accessible.
- ✅ JS externe unique, sans dépendance ni appel réseau externe.
- ✅ 106 tests verts au total (81 PHP : 24 socle + 15 e-commerce + 18 sécurité + 12 frontend + 12 SEO, + 25 SQL).
- ✅ Noms des 5 membres validés dans `docs/01_gestion_projet/suivi_sprints.md` (US-005, A-01 clôturée).
- ✅ Dépôt GitHub poussé (`develop`, `main`, tags `v0.1`/`v0.5`/`v1.0` — A-02 clôturée).

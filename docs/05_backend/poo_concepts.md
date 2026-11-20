# TuniShop — Concepts POO et principes SOLID

> Ce document justifie les choix de conception du socle PHP 8.3 livré pour l'**US-025**.
> Il est destiné à la soutenance : chaque concept est illustré par une classe réellement présente dans `app/`.

---

## 1. Les quatre piliers de la POO

### 1.1 Encapsulation

Les données sont protégées derrière des méthodes et des visibilités explicites.

```php
// app/Core/Response.php
final class Response
{
    public function __construct(
        public readonly string $body = '',
        public readonly int $status = 200,
        public readonly array $headers = [],
    ) {}

    public static function json(array $data, int $status = 200): self { /* ... */ }
    public function send(): void { /* ... */ }
}
```

- `private` pour les détails internes (`Database::$pdo`, `Router::$middlewares`, `Session::$cliStore`) ;
- `protected` dans `Controller` pour que les contrôleurs puissent rendre une vue sans exposer la base ;
- `public readonly` pour les valeurs immuables (`Request::$method`, `Response::$status`).

**Bénéfice** : aucune partie du code ne peut modifier le statut HTTP après la création de la réponse ; les invariants sont garantis par les fabriques (`json()`, `redirect()`…).

### 1.2 Abstraction

Deux niveaux d'abstraction coexistent :

| Abstraction | Cache ce que… | Expose ce que… |
|---|---|---|
| `Controller` (classe abstraite) | le rendu, le JSON, la redirection | `render()`, `json()`, `redirect()` |
| `Model` (classe abstraite) | la construction des SQL CRUD | `find()`, `all()`, `create()`, `update()`… |
| `MiddlewareInterface` | l'ordre d'exécution | `handle(Request, next): Response` |
| `HttpException` | le format HTML d'erreur | `status()`, `toResponse()` |

Le contrôleur ne sait **pas** comment la vue est chargée ; le service ne sait **pas** comment la PDO est ouverte.

### 1.3 Héritage

```
Model (abstract)
├── Product      → catalogue, v_low_stock, images
├── Category     → catégories actives
├── Shop         → boutiques en attente / validées
├── Cart         → panier, lignes, v_cart_totals
├── Order        → commandes, facture, historique
└── User         → authentification Argon2id

Controller (abstract)
└── Home, Catalog, Cart, Auth, Order, Seller, Admin, Api, System

HttpException (abstract)
└── NotFoundException (404), ForbiddenException (403), ValidationException (422)
```

L'héritage est réservé aux **"est-un"** vrais : un `Product` est un `Model`, un `ForbiddenException` est une `HttpException`.
Les relations **"utilise-un"** passent par l'injection (un `CartService` *utilise* un `Cart`, il n'en hérite pas).

### 1.4 Polymorphisme

Le pipeline de middlewares est polymorphique par l'interface : `Router` stocke des `MiddlewareInterface` sans connaître leur classe concrète.

```php
foreach (array_reverse($specs) as $spec) {
    $middleware = $this->resolveMiddleware($spec); // Auth | Role | Csrf
    $next = fn (Request $r): Response => $middleware->handle($r, $next);
}
```

De même, `callHandler()` invoque indistinctement `HomeController::index`, `ApiController::products` ou `AdminController::validateShop` : tous respectent la signature `Response action(Request $request, ...$params)`.

---

## 2. Principes SOLID appliqués

| Principe | Traduction dans TuniShop | Exemple concret |
|---|---|---|
| **S** — Responsabilité unique | Chaque classe a une seule raison de changer | `PaymentService` gère la simulation ; `OrderService` gère la commande ; `AuditService` gère le journal |
| **O** — Ouvert / fermé | Ajouter un middleware ou une route sans modifier le routeur | Nouvelle règle `role:moderateur` : une ligne dans `routes.php`, aucune modification de `Router` |
| **L** — Substitution de Liskov | Toute sous-classe de `HttpException` remplace son parent | `NotFoundException` et `ForbiddenException` sont capturées par le même `catch (HttpException $e)` dans `index.php` |
| **I** — Ségrégation des interfaces | Interface minimale | `MiddlewareInterface` ne contient que `handle()` ; pas de méthodes inutilisées |
| **D** — Inversion de dépendances | Les dépendances concrètes sont choisies dans `Application`, jamais dans les classes métier | `CartService` reçoit `Database`, `Session` et `Cart` par constructeur |

### Illustration du principe D

```php
// app/Core/Application.php — composition root
$this->database = new Database($config['database']);
$this->session  = new Session($config['app']['session']);
$this->view     = new View($config['views'], [...]);
$this->router   = new Router($routes, $this->database, $this->session, $this->view);
```

`CartService` dépend de types abstraits ou injectés ; il n'appelle **jamais** `new Database()` lui-même. Le test `tests/run.php` peut donc lui passer la base locale sans modifier une ligne du service.

---

## 3. Patterns de conception utilisés

| Pattern | Où | Pourquoi |
|---|---|---|
| **Front Controller** | `public/index.php` | Point d'entrée unique : audit, sécurité et erreurs centralisés |
| **MVC** | `Controllers` / `Models` / `Views` | Séparation présentation / logique / données |
| **Service Layer** | `app/Services/` | Règles métier réutilisables, indépendantes des contrôleurs HTTP |
| **Active Record léger** | `app/Models/Model.php` | CRUD générique sécurisé, simple à démontrer en BTS |
| **Dependency Injection** | constructeurs + `Application` | Testabilité et faible couplage |
| **Chaîne de responsabilité** | middlewares | Auth → RBAC → CSRF → contrôleur |
| **Fabrique (Factory)** | `Response::json()`, `html()`, `redirect()` | Construire des réponses valides sans état intermédiaire |
| **Template Method** | `Controller::render()` | Le contrôleur fournit les données, la vue gère le balisage |
| **Registry statique délimité** | `AuditService::configure()` | Contexte d'audit accessible depuis les services sans le propager dans chaque signature |
| **Whitelist / Value Object** | `Database::identifier()` | Empêche l'injection SQL sur les noms de colonnes |

---

## 4. Fonctionnalités PHP 8.3 mises à profit

| Fonctionnalité | Emploi |
|---|---|
| `declare(strict_types=1)` | Tous les fichiers : détection immédiate des erreurs de type |
| Propriétés `readonly` | `Request`, `Response`, `Session`, `Database` → objets immuables |
| **Named arguments** | `Router::callHandler()` : `$controller->{$action}(...$args)` avec `slug`, `id`… |
| Types union / `mixed` | `Request::input(): mixed`, `routeParam(): mixed` |
| `mb_strtolower`, `mb_strlen` | Validation d'email et de mot de passe en UTF-8 |
| `password_hash(PASSWORD_ARGON2ID)` | RG-09 — hachage moderne des mots de passe |
| `hash_equals()` | Comparaison à temps constant du jeton CSRF |
| `JSON_THROW_ON_ERROR` | `Response::json()` : aucune réponse JSON silencieusement tronquée |
| `str_contains`, `str_starts_with` | Routage et détection des chemins API |
| `??`, `?:`, opérateur `+` sur tableaux | Valeurs par défaut et priorité des arguments nommés |
| `final` sur les classes | Empêche la modification non contrôlée des composants critiques |

---

## 5. Pourquoi ne pas tout mettre dans les contrôleurs ?

Un choix pédagogique important pour la soutenance :

| Approche « contrôleur épais » | Choix TuniShop |
|---|---|
| SQL, TVA, hachage, redirections et HTML mélangés | Services + modèles + vues séparés |
| Difficile à tester sans serveur HTTP | `tests/run.php` teste services et modèles en CLI |
| Règles dupliquées entre écrans | `CartService::totals()` unique source applicative |
| Audit oublié sur certains chemins | Contexte posé dans `Application::handle()` pour **toutes** les requêtes |

---

## 6. Limites assumées et évolutions

| Limite | Évolution prévue |
|---|---|
| Moteur de vues maison (pas de Twig/Blade) | Suffisant pour 12 écrans ; migration possible via une interface `ViewRenderer` |
| Active Record léger | Passer à un vrai Repository + Unit of Work si le domaine grandit |
| `AuditService` statique | Remplacer par un service injecté une fois le conteneur IoC généralisé |
| Génération PDF absente | Module E-commerce (Sprint 8) avec DomPDF/TCPDF |
| Modération des avis non exposée | Interface admin (Sprint 7) branchée sur `sp_moderate_review()` |

---

## 7. Démonstration en soutenance (5 minutes)

1. `composer test` → **23 PASS / 0 FAIL** : prouver que l'architecture est exécutable.
2. Ouvrir `config/routes.php` : montrer les middlewares déclaratifs.
3. Ouvrir `app/Core/Application.php` : montrer l'injection des dépendances et le contexte RG-11.
4. Ouvrir `diagrammes_uml.md` : enchaîner cas d'utilisation → classes → séquence checkout → états commande.
5. Ouvrir `app/Services/PaymentService.php` : souligner qu'**aucune donnée bancaire n'est transmise** (RG-07).

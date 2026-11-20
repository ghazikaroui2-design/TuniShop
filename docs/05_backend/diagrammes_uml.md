# TuniShop — Diagrammes UML

> Diagrammes en notation **Mermaid** (compatibles GitHub, VS Code, MkDocs et la plupart des éditeurs Markdown).
> Ils modélisent le socle MVC livré dans `app/` et s'appuient sur le [modèle de données](../04_bdd/mcd_mld.md).

---

## 1. Diagramme de cas d'utilisation

```mermaid
flowchart LR
    subgraph Acteurs
        V[Visiteur]
        C[Client]
        S[Vendeur]
        A[Administrateur]
    end

    subgraph System["TuniShop"]
        UC1(Consulter le catalogue)
        UC2(Rechercher / filtrer)
        UC3(Voir une fiche produit)
        UC4(Gérer son panier)
        UC5(Créer un compte / se connecter)
        UC6(Passer commande)
        UC7(Payer en sandbox)
        UC8(Consulter ses commandes / facture)
        UC9(Deposer un avis)
        UC10(Gérer sa boutique et ses stocks)
        UC11(Valider une boutique)
        UC12(Modérer un avis)
        UC13(Consulter le tableau de bord)
    end

    V --> UC1
    V --> UC2
    V --> UC3
    V --> UC4
    V --> UC5
    C --> UC6
    C --> UC7
    C --> UC8
    C --> UC9
    S --> UC10
    S --> UC13
    A --> UC11
    A --> UC12
    A --> UC13

    UC6 ..> UC4 : <<include>>
    UC6 ..> UC7 : <<include>>
    UC9 ..> UC8 : <<include>> (achat vérifié — RG-05)
    UC11 ..> UC10 : <<extend>> (débloque la publication — RG-06)
```

---

## 2. Diagramme de classes (socle MVC)

```mermaid
classDiagram
    direction LR

    class Request {
        +string method
        +string path
        +array query
        +array post
        +array server
        +array routeParams
        +fromGlobals() Request
        +input(key, default) mixed
        +header(name) string
        +ip() string
        +userAgent() string
        +routeParam(name) mixed
    }

    class Response {
        +string body
        +int status
        +array headers
        +html(body, status)$ Response
        +json(data, status)$ Response
        +redirect(url)$ Response
        +send() void
    }

    class Application {
        +Database database
        +Session session
        +View view
        +Router router
        +handle(Request) Response
        -bootstrapMiddlewares() void
    }

    class Router {
        -array routes
        -Database database
        -Session session
        -View view
        -array middlewares
        +dispatch(Request) Response
        +addMiddleware(name, mw) void
        +setMiddlewareFactory(fn) void
        -match(pattern, path) array
        -callHandler(handler, request) Response
    }

    class MiddlewareInterface {
        <<interface>>
        +handle(Request, next) Response
    }

    class AuthMiddleware {
        -Session session
        +handle(Request, next) Response
    }
    class RoleMiddleware {
        -Session session
        -array allowedRoles
        +handle(Request, next) Response
    }
    class CsrfMiddleware {
        -Session session
        +handle(Request, next) Response
    }

    class Controller {
        <<abstract>>
        #Database database
        #Session session
        #View view
        #render(template, data) Response
        #json(data) Response
        #redirect(path) Response
    }

    class HomeController
    class CatalogController
    class CartController
    class AuthController
    class OrderController
    class SellerController
    class AdminController
    class ApiController
    class SystemController

    class Model {
        <<abstract>>
        #string table
        #string primaryKey
        +find(id) array
        +all(limit, offset, orderBy) array
        +where(criteria) array
        +create(data) int
        +update(id, data) bool
        +delete(id) bool
        +count() int
    }
    class Product {
        +catalogue(filters) array
        +findBySlug(slug) array
        +lowStock() array
        +images(id) array
    }
    class Category
    class Shop {
        +pending() array
        +validated() array
    }
    class Cart {
        +findForUser(id) array
        +findByToken(token) array
        +items(id) array
        +totals(id) array
    }
    class Order {
        +forUser(id) array
        +findByNumber(number) array
        +items(id) array
        +invoice(id) array
        +history(id) array
    }
    class User {
        +findForAuth(email) array
        +verify(email, pwd) array
        +createClient(...) int
    }

    class CartService {
        +currentCartId() int
        +add(productId, qty) void
        +remove(cartId, productId) void
        +totals(cartId) array
    }
    class OrderService {
        +place(input) array
    }
    class PaymentService {
        +isValidTestCard(number) bool
        +simulate(orderId) string
    }
    class ShopService {
        +validate(id, decision, reason) void
    }
    class AuditService {
        +configure(db, user, ip, ua)$ void
        +log(action, entity, id, details)$ void
    }

    class Database {
        -PDO pdo
        +pdo() PDO
        +fetch(sql, params) array
        +fetchAll(sql, params) array
        +execute(sql, params) int
        +call(proc, args, out) array
        +identifier(name) string
        +setAuditContext(user, ip, ua) void
    }
    class Session {
        +get(key) mixed
        +set(key, value) void
        +user() array
        +userId() int
        +role() string
        +regenerate() void
        +csrfToken() string
        +validateCsrf(token) bool
    }
    class View {
        +render(template, data, layout) string
        +e(value)$ string
    }
    class HttpException {
        <<abstract>>
        +status() int
        +toResponse() Response
    }
    class NotFoundException
    class ForbiddenException
    class ValidationException

    Application --> Router : crée et injecte
    Application --> Database : contexte audit
    Application --> AuditService : configure
    Router --> MiddlewareInterface : pipeline
    MiddlewareInterface <|.. AuthMiddleware
    MiddlewareInterface <|.. RoleMiddleware
    MiddlewareInterface <|.. CsrfMiddleware
    Router --> Controller : instancie
    Controller <|-- HomeController
    Controller <|-- CatalogController
    Controller <|-- CartController
    Controller <|-- AuthController
    Controller <|-- OrderController
    Controller <|-- SellerController
    Controller <|-- AdminController
    Controller <|-- ApiController
    Controller <|-- SystemController
    Controller --> View : rend
    Controller ..> Response : retourne
    Controller --> Session : lit le rôle / flash

    HomeController --> Product
    HomeController --> Category
    CatalogController --> Product
    CartController --> CartService
    AuthController --> User
    OrderController --> OrderService
    OrderController --> PaymentService
    OrderController --> Order
    SellerController --> Product
    SellerController --> Shop
    AdminController --> Shop
    AdminController --> ShopService

    CartService --> Cart
    CartService --> Database
    OrderService --> CartService
    OrderService --> Database
    OrderService --> AuditService
    PaymentService --> Database
    ShopService --> Database

    Model <|-- Product
    Model <|-- Category
    Model <|-- Shop
    Model <|-- Cart
    Model <|-- Order
    Model <|-- User
    Model --> Database : PDO

    HttpException <|-- NotFoundException
    HttpException <|-- ForbiddenException
    HttpException <|-- ValidationException
    HttpException ..> Response : toResponse()
```

---

## 3. Diagrammes de séquence

### 3.1 Ajout au panier (RG-01, RG-02/03, RG-10)

```mermaid
sequenceDiagram
    actor U as Visiteur
    participant R as Router
    participant MW as CsrfMiddleware
    participant CC as CartController
    participant CS as CartService
    participant DB as Database / PDO
    participant SP as sp_add_to_cart + triggers

    U->>R: POST /panier/ajouter (_token, product_id, quantity)
    R->>MW: handle(request, next)
    MW->>MW: hash_equals(_token, session token)
    MW->>CC: next(request)
    CC->>CS: add(productId, quantity)
    CS->>CS: currentCartId() (invité → guest_token, expires_at +30j)
    CS->>DB: CALL sp_add_to_cart(cart, product, qty)
    DB->>SP: exécution transactionnelle
    SP-->>DB: OK ou SQLSTATE 45000 (stock insuffisant)
    DB-->>CS: résultat
    CS-->>CC: void
    CC->>CC: session.flash(success / error)
    CC-->>R: Response::redirect('/panier')
    R-->>U: 302 → /panier (total HT/TVA/livraison via v_cart_totals)
```

### 3.2 Commande et paiement simulé (RG-04, RG-07, RG-08, RG-11)

```mermaid
sequenceDiagram
    actor C as Client connecté
    participant R as Router
    participant MW as Auth + CSRF
    participant OC as OrderController
    participant OS as OrderService
    participant APP as Application
    participant AUD as AuditService
    participant DB as Database
    participant SP as sp_place_order
    participant PAY as PaymentService
    participant TRG as trg_orders_au / trg_order_items_ai

    C->>R: POST /commande (delivery_mode, _token)
    R->>APP: handle(request)
    APP->>DB: setAuditContext(user_id, ip, ua)
    APP->>AUD: configure(db, user, ip, ua)
    R->>MW: auth puis csrf
    MW->>OC: next(request)
    OC->>OS: place(input)
    OS->>DB: CALL sp_place_order(..., OUT order_id, OUT number)
    DB->>SP: transaction : verrous, TVA, livraison, promotion
    SP->>TRG: insertion order_items → décrément stock
    SP-->>DB: p_order_id, p_order_number
    DB-->>OS: résultats OUT
    OS->>AUD: log('ORDER_PLACED', 'orders', id, number)
    AUD->>DB: CALL sp_log_action(...)
    OS-->>OC: order_id / number
    OC->>PAY: simulate(order_id)
    PAY->>DB: CALL sp_simulate_payment(order_id, OUT ref)
    DB-->>PAY: SBX-YYYY-NNNNNN
    Note over PAY,DB: Aucun numéro de carte transmis ni stocké (RG-07)
    PAY-->>OC: transaction_ref
    OC-->>R: 302 /commande/{id}
    R-->>C: confirmation + facture F-YYYY-NNNN (RG-08)
```

### 3.3 Connexion (RG-09, RG-11)

```mermaid
sequenceDiagram
    actor U as Utilisateur
    participant AC as AuthController
    participant UM as User (Model)
    participant DB as Database
    participant S as Session
    participant AUD as AuditService

    U->>AC: POST /connexion (_token, email, password)
    AC->>UM: verify(email, password)
    UM->>DB: SELECT users JOIN roles WHERE email = ?
    DB-->>UM: password_hash, is_active, role
    UM->>UM: password_verify() (Argon2id/bcrypt)
    alt identifiants invalides
        UM-->>AC: null
        AC->>AUD: log('LOGIN_FAILED', 'users', null, email)
        AC-->>U: 302 /connexion + flash error
    else identifiants valides
        UM-->>AC: user (id, role, prénom…)
        AC->>S: regenerate() (anti fixation)
        AC->>S: setUser([...])
        AC->>DB: UPDATE users SET last_login_at = NOW()
        AC->>AUD: log('LOGIN_SUCCESS', ...)
        AC-->>U: 302 / + flash success
    end
```

### 3.4 Validation d'une boutique (RG-06)

```mermaid
sequenceDiagram
    actor A as Admin
    participant R as Router
    participant MW as Auth + Role(admin) + CSRF
    participant AC as AdminController
    participant SS as ShopService
    participant DB as Database
    participant SP as sp_validate_shop + trg_shops_bu

    A->>R: POST /admin/vendeurs/{id}/valider (_token, decision)
    R->>MW: auth → role:admin → csrf
    alt rôle insuffisant
        MW-->>A: 403 ForbiddenException
    else jeton invalide
        MW-->>A: 419 JSON
    else autorisé
        MW->>AC: next(request, id:int)
        AC->>SS: validate(id, 'validée'|'refusée', reason)
        SS->>DB: CALL sp_validate_shop(id, admin_id, decision, reason)
        DB->>SP: met à jour shops.status + audit
        SP-->>DB: OK
        DB-->>SS: résultat
        SS-->>AC: void
        AC-->>A: 302 /admin/vendeurs + flash
    end
    Note over SP: Tant que la boutique est en attente, aucun produit n'est publiable (RG-06)
```

---

## 4. Diagramme de composants (déploiement logique)

```mermaid
flowchart TB
    subgraph Client["Navigateur"]
        UI["HTML / CSS responsive<br/>12 écrans"]
    end

    subgraph Serveur["Serveur PHP 8.3"]
        FE["public/index.php<br/>Front Controller"]
        CORE["app/Core<br/>Application · Router · Request · Response · Session · View"]
        MWC["app/Middlewares<br/>Auth · Role · CSRF"]
        CTRL["app/Controllers<br/>9 contrôleurs"]
        SVC["app/Services<br/>Cart · Order · Payment · Shop · Audit"]
        MOD["app/Models<br/>7 modèles Active Record"]
        VUES["app/Views<br/>layout + 8 gabarits"]
    end

    subgraph Donnees["Couche données"]
        PDO["PDO MySQL"]
        SQL["20 tables · 6 vues<br/>7 fonctions · 13 procédures · 11 triggers"]
    end

    UI -->|HTTP| FE
    FE --> CORE
    CORE --> MWC
    CORE --> CTRL
    CTRL --> SVC
    CTRL --> VUES
    SVC --> MOD
    MOD --> PDO
    PDO --> SQL
    VUES -->|HTML échappé| UI
```

---

## 5. Diagramme d'états — cycle de vie d'une commande (RG-04)

```mermaid
stateDiagram-v2
    [*] --> en_attente : sp_place_order()
    en_attente --> payée : sp_simulate_payment() / trg_orders_au génère F-YYYY-NNNN
    payée --> en_préparation : vendeur
    en_préparation --> expédiée : vendeur
    expédiée --> livrée : client / vendeur
    en_attente --> annulée : annulation (stock restauré — T12)
    payée --> annulée : remboursement sandbox
    livrée --> [*]

    note right of payée
        Seules les transitions autorisées
        par fn_is_valid_transition()
        sont acceptées (sp_update_order_status).
        Chaque changement est tracé dans
        order_status_history (RG-11).
    end note
```

### Diagramme d'états — cycle de vie d'une boutique (RG-06)

```mermaid
stateDiagram-v2
    [*] --> en_attente : inscription vendeur (trg_shops_bi force le statut)
    en_attente --> validée : sp_validate_shop() par un admin
    en_attente --> refusée : sp_validate_shop() (motif)
    refusée --> en_attente : nouvelle soumission
    validée --> suspendue : admin
    suspendue --> validée : admin
    validée --> [*]

    note right of en_attente
        Aucun produit de cette boutique
        ne peut être publié tant que
        le statut n'est pas 'validée'.
    end note
```

---

## 6. Correspondance avec les user stories

| Diagramme | User stories |
|---|---|
| Cas d'utilisation | US-019 à US-025, US-031 à US-044 |
| Classes | US-025, US-026, US-027 |
| Séquence ajout panier | US-035, US-036 |
| Séquence commande / paiement | US-037, US-038, US-047 |
| Séquence connexion | US-040, US-053 |
| Séquence validation vendeur | US-041, US-042 |
| Composants | US-025, NFR-01 |
| États commande | US-039, RG-04 |
| États boutique | US-041, RG-06 |

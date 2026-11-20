# Base de données TuniShop — Scripts et procédure d'import

## 1. Contenu du dossier

| Fichier | Description |
|---|---|
| `schema.sql` | Création de la base `tunishop`, 20 tables, contraintes, index et 6 vues |
| `procedures.sql` | 7 fonctions + 13 procédures stockées |
| `triggers.sql` | 11 triggers d'intégrité métier |
| `seed.sql` | Jeu d'essai (US-018) — rechargeable après `schema.sql` |
| `tests.sql` | Scénarios de vérification SQL (US-023, US-049) |

> ⚠️ **Ordre d'import obligatoire** : `schema.sql` → `procedures.sql` → `triggers.sql` → `seed.sql` → (`tests.sql`).

---

## 2. Import en ligne de commande (XAMPP / MySQL 8)

Les chemins ci-dessous sont ceux de la machine de développement. Ouvrir un terminal dans le dossier du projet :

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' --default-character-set=utf8mb4 -u root -e "SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/database/schema.sql"
& 'C:\xampp\mysql\bin\mysql.exe' --default-character-set=utf8mb4 -u root -e "SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/database/procedures.sql"
& 'C:\xampp\mysql\bin\mysql.exe' --default-character-set=utf8mb4 -u root -e "SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/database/triggers.sql"
& 'C:\xampp\mysql\bin\mysql.exe' --default-character-set=utf8mb4 -u root -e "SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/database/seed.sql"
```

Avec un mot de passe root : ajouter `-p` puis le saisir lorsque l'invite apparaît.

### Import via phpMyAdmin

1. Ouvrir <http://localhost/phpmyadmin>.
2. Onglet **Importer** → choisir les fichiers **un par un**, dans l'ordre indiqué.
3. Vérifier que l'interclassement est bien `utf8mb4_unicode_ci`.

---

## 3. Vérification rapide après import

```sql
USE tunishop;
SELECT COUNT(*) AS tables_count FROM information_schema.tables
 WHERE table_schema = 'tunishop';                    -- 20
SELECT COUNT(*) AS products FROM products;           -- 24
SELECT COUNT(*) AS published FROM v_catalogue;       -- 21
SELECT COUNT(*) AS shops_pending FROM shops WHERE status='en_attente'; -- 3
SELECT COUNT(*) AS orders FROM orders;               -- 10
SELECT COUNT(*) AS invoices FROM invoices;           -- 9 (toutes sauf CMD en attente)
CALL sp_get_dashboard_stats('2026-11-01','2026-11-30');
SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/database/tests.sql;
```

> Le nombre de factures dépend des commandes réellement payées dans le jeu d'essai (9 commandes payées, 1 en attente).

---

## 4. Comptes de démonstration

Mot de passe commun : **`Test1234!`**

| Rôle | Email | Accès |
|---|---|---|
| Administrateur | `ghazi.karoui@tunishop.tn` | Validation vendeurs, dashboard global |
| Client | `aymen.benali@tunishop.tn` | 2 commandes, avis, factures |
| Vendeur validé | `fatma.bensalah@tunishop.tn` | Boutique « Atelier Bleu de Sidi Bou » |
| Vendeur en attente | `rania.belhadj@tunishop.tn` | Démonstration RG-06 |

> Le hachage de l'administrateur est en **bcrypt**, les autres en **Argon2id** : cela permet de vérifier les deux mécanismes de `password_verify()` (RG-09).

---

## 5. Sauvegarde et restauration (NFR-09)

```powershell
# Export complet (structure + données + routines + triggers)
& 'C:\xampp\mysql\bin\mysqldump.exe' -u root --routines --triggers --events --default-character-set=utf8mb4 tunishop > tunishop_backup.sql

# Restauration
& 'C:\xampp\mysql\bin\mysql.exe' -u root --default-character-set=utf8mb4 -e "SOURCE C:/Users/USER/OneDrive/Desktop/TuniShop/tunishop_backup.sql"
```

Le fichier d'export doit être livré avec le dossier technique (US-056).

---

## 6. Utilisation depuis le back-end PHP (PDO)

```php
// Connexion
$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=tunishop;charset=utf8mb4',
    'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::ATTR_EMULATE_PREPARES => false]
);

// Avant chaque requête sensible, transmettre le contexte d'audit (RG-11)
$stmt = $pdo->prepare('SET @current_user_id = ?, @client_ip = ?, @client_ua = ?');
$stmt->execute([$_SESSION['user_id'] ?? null, $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);

// Checkout : transaction côté SQL + requêtes préparées côté PHP
$stmt = $pdo->prepare('CALL sp_place_order(:cart, :user, :email, :mode, :addr, :promo, @oid, @ono)');
$stmt->execute([...]);
[$orderId, $orderNumber] = $pdo->query('SELECT @oid AS id, @ono AS number')->fetch(PDO::FETCH_NUM);
```

---

## 7. Dépannage

| Symptôme | Cause probable | Solution |
|---|---|---|
| `FUNCTION fn_next_sequence does not exist` | `procedures.sql` non importé | Réimporter dans l'ordre |
| `TABLE tunishop.sequences doesn't exist` | `schema.sql` non importé | Réimporter `schema.sql` d'abord |
| `RG-06 : publication impossible` | Boutique pas encore validée | Appeler `sp_validate_shop(id, admin, 'validée', NULL)` |
| `RG-01 : stock insuffisant` | Stock < quantité demandée | Réapprovisionner : `UPDATE products SET stock_qty=20 WHERE id=…` |
| `Can't update table ... already used` | Function non-déterministe | Vérifier `NOT DETERMINISTIC MODIFIES SQL DATA` sur `fn_next_sequence` |
| Caractères accentés illisibles | Charset client incorrect | Toujours `--default-character-set=utf8mb4` |

---

## 8. Réinitialisation complète

Réimporter simplement `schema.sql` (il contient les `DROP` dans le bon ordre), puis `procedures.sql`, `triggers.sql` et `seed.sql`.

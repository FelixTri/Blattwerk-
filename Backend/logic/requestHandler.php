<?php // Schnittstelle für wichtige Backend-Funktionen (AJAX-Zugriffe aus dem Frontend)
session_start();

require_once __DIR__ . '/../helpers/dbaccess.php';
require_once __DIR__ . '/../models/Product.class.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) { // Aktionen basierend auf dem Parameter 'action'

    // Produkt in den Warenkorb legen (Session basiert)
    case 'addToCart':
        $productId = $_POST['productId'] ?? null;
        if ($productId) {
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
            echo json_encode(['success' => true, 'cartCount' => array_sum($_SESSION['cart'])]);
        } else {
            echo json_encode(['error' => 'Keine Produkt-ID übergeben']);
        }
        break;

    // Aktuelle Anzahl im Warenkorb (für Icon-Anzeige)
    case 'getCartCount':
        echo json_encode(['count' => array_sum($_SESSION['cart'] ?? [])]);
        break;

    // Gesamten Warenkorb zurückgeben (inkl. Produktdaten)
    case 'getCart':
        $cart = $_SESSION['cart'] ?? [];
        $productModel = new Product();
        $items = $productModel->getProductsByIds(array_keys($cart));
        foreach ($items as &$item) {
            $item['quantity'] = $cart[$item['id']];
            $item['subtotal'] = $item['quantity'] * $item['price'];
        }
        echo json_encode($items);
        break;

    // Produkt aus dem Warenkorb entfernen
    case 'removeFromCart':
        $productId = $_POST['productId'] ?? null;
        if ($productId && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            echo json_encode(['success' => true, 'cartCount' => array_sum($_SESSION['cart'])]);
        } else {
            echo json_encode(['error' => 'Produkt nicht im Warenkorb']);
        }
        break;

    // Menge im Warenkorb anpassen
    case 'updateQuantity':
        $productId = $_POST['productId'] ?? null;
        $quantity  = (int) ($_POST['quantity'] ?? 0);
        if ($productId && $quantity > 0) {
            $_SESSION['cart'][$productId] = $quantity;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Ungültige Menge']);
        }
        break;

        // Nutzer-Sessioninformationen abrufen (für Login-Status & Auto-Login per Cookie)
        case 'getSessionInfo':
            try {
                // Auto-Login per Cookie (falls gewünscht)
                if (!isset($_SESSION['user_id']) &&
                    isset($_COOKIE['user_id'], $_COOKIE['user_hash'])
                ) {
                    $pdo  = DbAccess::connect();
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$_COOKIE['user_id']]);
                    $u0 = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($u0 && hash('sha256', $u0['password']) === $_COOKIE['user_hash']) {
                        $_SESSION['user_id'] = $u0['id'];
                        $_SESSION['role']    = $u0['role'];
                    }
                }
            
                if (isset($_SESSION['user_id'])) {
                    $pdo  = DbAccess::connect();
                    $stmt = $pdo->prepare("
                        SELECT
                        id,
                        username,
                        role,
                        payment_info,
                        salutation,
                        address,
                        postal_code,
                        city,
                        email
                        FROM users
                        WHERE id = ?
                    ");
                    $stmt->execute([ $_SESSION['user_id'] ]);
                    $u = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
                    echo json_encode([
                        'user_id'      => $u['id']             ?? null,
                        'username'     => $u['username']       ?? '',
                        'role'         => $u['role']           ?? 'guest',
                        'payment_info' => $u['payment_info']   ?? '',
                        'salutation'   => $u['salutation']     ?? '',
                        'address'      => $u['address']        ?? '',
                        'postal_code'  => $u['postal_code']    ?? '',
                        'city'         => $u['city']           ?? '',
                        'email'        => $u['email']          ?? ''
                    ]);
                } else {
                    echo json_encode([
                        'user_id'      => null,
                        'username'     => '',
                        'role'         => 'guest',
                        'payment_info' => '',
                        'salutation'   => '',
                        'address'      => '',
                        'postal_code'  => '',
                        'city'         => '',
                        'email'        => ''
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => 'Serverfehler: ' . $e->getMessage()]);
            }

            break;

    // Kategorien auslesen (für Produktformulare)
    case 'getCategories':
        $pdo  = DbAccess::connect();
        $stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // Neues Produkt anlegen (Adminbereich)
    case 'createProduct':
        $name        = $_POST['name']        ?? '';
        $description = $_POST['description'] ?? '';
        $price       = $_POST['price']       ?? 0;
        $category_id = $_POST['category_id'] ?? null;

        $imageFilename = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp     = $_FILES['image']['tmp_name'];
            $orig    = basename($_FILES['image']['name']);
            $imageFilename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $orig);
            move_uploaded_file($tmp, __DIR__ . '/../productpictures/' . $imageFilename);
        }

        $pdo  = DbAccess::connect();
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, image, category_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $description, $price, $imageFilename, $category_id]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    // Produkt aktualisieren (Adminbereich)
    case 'updateProduct':
        $id          = (int) ($_POST['id'] ?? 0);
        $name        = $_POST['name']        ?? '';
        $description = $_POST['description'] ?? '';
        $price       = $_POST['price']       ?? 0;
        $category_id = $_POST['category_id'] ?? null;

        $pdo = DbAccess::connect();

        // Falls neues Bild hochgeladen wurde, altes löschen
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $old = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $old->execute([$id]);
            $row = $old->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['image']) {
                @unlink(__DIR__ . '/../productpictures/' . $row['image']);
            }
            $tmp     = $_FILES['image']['tmp_name'];
            $orig    = basename($_FILES['image']['name']);
            $newName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $orig);
            move_uploaded_file($tmp, __DIR__ . '/../productpictures/' . $newName);

            $sql    = "
                UPDATE products
                   SET name = ?, description = ?, price = ?, image = ?, category_id = ?
                 WHERE id = ?
            ";
            $params = [$name, $description, $price, $newName, $category_id, $id];
        } else {
            $sql    = "
                UPDATE products
                   SET name = ?, description = ?, price = ?, category_id = ?
                 WHERE id = ?
            ";
            $params = [$name, $description, $price, $category_id, $id];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
        break;

    // Produkt löschen (inkl. Bild)
    case 'deleteProduct':
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'Ungültige Produkt-ID']);
            break;
        }
        $pdo = DbAccess::connect();
        $old = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $old->execute([$id]);
        $row = $old->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['image']) {
            @unlink(__DIR__ . '/../productpictures/' . $row['image']);
        }
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    // Alle Produkte inkl. Kategorie anzeigen
    case 'getProducts':
        $pdo = DbAccess::connect();
        $sql = "
            SELECT
                p.id, p.name, p.description, p.price, p.image, p.category_id,
                c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.name
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // Einzelnes Produkt laden
    case 'getProduct':
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'Ungültige Produkt-ID']);
            break;
        }
        $pdo = DbAccess::connect();
        $sql = "
            SELECT
                p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($prod ?: ['error' => 'Produkt nicht gefunden']);
        break;

    // Kundenliste für Adminbereich
    case 'getCustomers':
        $pdo = DbAccess::connect();
        $stmt = $pdo->prepare("
            SELECT
                id,
                CONCAT(salutation,' ',first_name,' ',last_name) AS name,
                email,
                role,
                active
              FROM users
              ORDER BY role DESC, last_name, first_name
            ");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

    // Bestellungen eines Kunden abrufen (Admin)
    case 'getCustomerOrders':
        $userId = (int)($_GET['userId'] ?? 0);
        if ($userId <= 0) {
            echo json_encode([]); 
            break;
        }

        $pdo = DbAccess::connect();
        $stmt = $pdo->prepare("
        SELECT
            o.id             AS order_id,
            o.created_at     AS date,
            SUM(oi.quantity * p.price) AS total
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p     ON p.id = oi.product_id
        WHERE o.user_id = ?
        GROUP BY o.id, o.created_at
        ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // Artikel einer Bestellung anzeigen (Admin)
    case 'getOrderItems':
        $orderId = (int)($_GET['orderId'] ?? 0);
        if ($orderId <= 0) {
            echo json_encode([]);
            break;
        }

        $pdo = DbAccess::connect();
        $stmt = $pdo->prepare("
        SELECT
            oi.product_id,
            p.name        AS product_name,
            oi.quantity,
            p.price       AS unit_price
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // Nutzer aktiv/inaktiv schalten (Admin)
    case 'toggleUserActive':
        $userId = (int)($_POST['userId'] ?? 0);
        $newAct = (int)($_POST['active'] ?? 0);
        if ($userId <= 0) {
            echo json_encode(['error' => 'Ungültige User-ID']);
            break;
        }
        $pdo = DbAccess::connect();
        $stmt = $pdo->prepare("UPDATE users SET active = ? WHERE id = ?");
        $stmt->execute([$newAct, $userId]);
        echo json_encode(['success' => true, 'active' => $newAct]);
        break;

    // Fallback bei unbekannter Aktion
    default:
        echo json_encode(['error' => 'Ungültige Aktion']);
}

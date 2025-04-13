<?php
session_start(); // Wichtig für den Warenkorb + Login
require_once __DIR__ . '/../config/dbaccess.php';
require_once __DIR__ . '/../models/product.class.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'addToCart':
        $productId = $_POST['productId'] ?? null;
        if ($productId) {
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
            echo json_encode(['success' => true, 'cartCount' => array_sum($_SESSION['cart'])]);
        } else {
            echo json_encode(['error' => 'Keine Produkt-ID übergeben']);
        }
        break;

    case 'getCartCount':
        echo json_encode(['count' => array_sum($_SESSION['cart'] ?? [])]);
        break;

    case 'getCart':
        $cart = $_SESSION['cart'] ?? [];
        $product = new Product();
        $items = $product->getProductsByIds(array_keys($cart));

        foreach ($items as &$item) {
            $item['quantity'] = $cart[$item['id']] ?? 1;
        }

        echo json_encode($items);
        break;

    case 'removeFromCart':
        $productId = $_POST['productId'] ?? null;
        if ($productId && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Produkt nicht im Warenkorb']);
        }
        break;

    case 'updateQuantity':
        $productId = $_POST['productId'] ?? null;
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if ($productId && $quantity > 0) {
            $_SESSION['cart'][$productId] = $quantity;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Ungültige Menge']);
        }
        break;

    case 'getSessionInfo':
        // Falls Session nicht gesetzt, aber gültige Cookies vorhanden sind:
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'], $_COOKIE['user_hash'])) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_COOKIE['user_id']]);
            $user = $stmt->fetch();

            if ($user && hash('sha256', $user['password']) === $_COOKIE['user_hash']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
            }
        }

        echo json_encode([
            "user_id" => $_SESSION['user_id'] ?? null,
            "role" => $_SESSION['role'] ?? "guest"
        ]);
        break;

    default:
        echo json_encode(['error' => 'Ungültige Aktion']);
}

<?php
session_start(); // Wichtig für den Warenkorb
require_once __DIR__ . '/../config/dbaccess.php';
require_once __DIR__ . '/../models/product.class.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'addToCart':
        session_start();
        $productId = $_POST['productId'] ?? null;
        if ($productId) {
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
            echo json_encode(['success' => true, 'cartCount' => array_sum($_SESSION['cart'])]);
        } else {
            echo json_encode(['error' => 'Keine Produkt-ID übergeben']);
        }
        break;


    case 'getCartCount':
        session_start();
        echo json_encode(['count' => array_sum($_SESSION['cart'] ?? [])]);
        break;
            
            
    case 'getCart':
        $cart = $_SESSION['cart'] ?? [];
        $product = new Product();
        $items = $product->getProductsByIds(array_keys($cart));

        // Mengen ergänzen
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

    case 'getCartCount':
        echo json_encode(['count' => array_sum($_SESSION['cart'] ?? [])]);
        break;

    default:
        echo json_encode(['error' => 'Ungültige Aktion']);
}

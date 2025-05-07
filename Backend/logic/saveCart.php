<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../helpers/dbaccess.php'; // Neu: Verbindung über Klasse

// Nur eingeloggte Nutzer dürfen den Warenkorb speichern
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Warenkorb-Daten lesen
$cartJson = file_get_contents('php://input');
$cart = json_decode($cartJson, true);

if (!is_array($cart)) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges Warenkorb-Format.']);
    exit;
}

try {
    $pdo = DbAccess::connect(); // NEU

    // Bestehenden Warenkorb löschen
    $pdo->prepare("DELETE FROM saved_cart WHERE user_id = ?")->execute([$userId]);

    // Neuen Warenkorb speichern
    $stmt = $pdo->prepare("
        INSERT INTO saved_cart (user_id, product_id, quantity)
        VALUES (?, ?, ?)
    ");

    foreach ($cart as $item) {
        $productId = (int)($item['id'] ?? 0);
        $quantity  = (int)($item['quantity'] ?? 0);

        if ($productId > 0 && $quantity > 0) {
            $stmt->execute([$userId, $productId, $quantity]);
        }
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
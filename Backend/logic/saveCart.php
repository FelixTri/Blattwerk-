<?php
session_start();
header('Content-Type: application/json');

// Prüfen, ob der User eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$userId = $_SESSION['user_id'];

// DB-Zugangsdaten
$dbHost = 'localhost';
$dbName = 'blattwerk_shop';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()
    ]);
    exit;
}

// JSON aus dem Body lesen
$cartData = json_decode(file_get_contents("php://input"), true);

if (!is_array($cartData)) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges Cart-Format']);
    exit;
}

try {
    // Zuerst: alten Warenkorb des Users leeren
    $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$userId]);

    // Dann: neue Daten einfügen
    $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");

    foreach ($cartData as $item) {
        $productId = (int)($item['id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);

        if ($productId > 0 && $quantity > 0) {
            $stmt->execute([$userId, $productId, $quantity]);
        }
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern: ' . $e->getMessage()]);
}
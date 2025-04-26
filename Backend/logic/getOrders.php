<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

$host   = 'localhost';
$dbName = 'blattwerk_shop';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Bestellungen laden
    $sql = "
        SELECT
            o.id,
            o.created_at,
            COALESCE(i.invoice_number, '') AS invoice_number
        FROM orders o
        LEFT JOIN invoices i ON i.order_id = o.id
        WHERE o.user_id = ?
        ORDER BY o.created_at ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Datenbank-Fehler: ' . $e->getMessage()
    ]);
    exit;
}
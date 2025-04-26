<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}
if (!isset($_GET['orderId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'orderId fehlt']);
    exit;
}

$orderId = (int) $_GET['orderId'];
$userId  = (int) $_SESSION['user_id'];

$host   = 'localhost';
$dbName = 'blattwerk_shop';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Bestellung laden
    $sqlOrder = "
        SELECT
            o.id,
            o.created_at,
            COALESCE(i.invoice_number, '') AS invoice_number
        FROM orders o
        LEFT JOIN invoices i ON i.order_id = o.id
        WHERE o.id = ? AND o.user_id = ?
    ";
    $stmtOrder = $pdo->prepare($sqlOrder);
    $stmtOrder->execute([$orderId, $userId]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Bestellung nicht gefunden']);
        exit;
    }

    // Positionen laden
    $sqlItems = "
        SELECT
            p.id AS product_id,
            p.name,
            p.price,
            oi.quantity
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ";
    $stmtItems = $pdo->prepare($sqlItems);
    $stmtItems->execute([$orderId]);
    $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'order' => $order]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Datenbank-Fehler: ' . $e->getMessage()
    ]);
    exit;
}


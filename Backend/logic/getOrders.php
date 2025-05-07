<?php
session_start();

require_once __DIR__ . '/../helpers/dbaccess.php';

header('Content-Type: application/json');

// Zugriff nur für eingeloggte Nutzer
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

try {
    $pdo = DbAccess::connect();

    $stmt = $pdo->prepare("
        SELECT
            o.id             AS id,
            o.created_at     AS created_at,
            SUM(oi.quantity * p.price) AS total
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p     ON p.id = oi.product_id
        WHERE o.user_id = ?
        GROUP BY o.id, o.created_at
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'orders' => $orders]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Serverfehler: ' . $e->getMessage()]);
}
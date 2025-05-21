<?php
// Bestelldetails abrufen
// Datei wird aufgerufen, wenn die Bestelldetails abgerufen werden sollen
session_start();

require_once __DIR__ . '/../helpers/dbaccess.php'; // DB-Zugriff

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$orderId = (int)($_GET['orderId'] ?? 0);
if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Bestell-ID']);
    exit;
}

try {
    $pdo = DbAccess::connect();

    // Sicherheitscheck: gehört Bestellung dem eingeloggten User?
    $check = $pdo->prepare("
        SELECT o.*, v.code AS voucher_code, v.amount AS voucher_original_amount
        FROM orders o
        LEFT JOIN vouchers v ON o.voucher_id = v.id
        WHERE o.id = ? AND o.user_id = ?
    ");
        
    $check->execute([$orderId, $_SESSION['user_id']]);
    $order = $check->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Bestellung nicht gefunden']);
        exit;
    }

    $paymentUsed = $order['payment_used'] ?? null;

    // Bestelldetails holen
    $stmt = $pdo->prepare("
        SELECT
            oi.product_id,
            p.name AS name,
            oi.quantity,
            p.price
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gutscheindaten abrufen (falls vorhanden)
    $voucherInfo = null;
    if (!empty($order['voucher_code'])) {
        $voucherStmt = $pdo->prepare("SELECT code, amount FROM vouchers WHERE code = ?");
        $voucherStmt->execute([$order['voucher_code']]);
        $voucherInfo = $voucherStmt->fetch(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'success' => true,
        'order' => [
            'id'         => $orderId,
            'created_at' => $order['created_at'],
            'items'      => $items,
            'voucher'    => isset($order['voucher_code']) ? [
                'code'   => $order['voucher_code'],
                'amount' => $order['voucher_original_amount']
            ] : null
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Serverfehler: ' . $e->getMessage()]);
}
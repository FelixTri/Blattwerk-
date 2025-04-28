<?php
session_start();
header('Content-Type: application/json');

// Nur eingeloggte User
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

// Request parsen
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['items']) || !is_array($input['items'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Ungültige Bestelldaten']);
    exit;
}

// DB-Verbindung
require_once __DIR__ . '/../config/dbaccess.php';
$pdo = DbAccess::connect();

// 1) Original payment_info auslesen
$stmtPay = $pdo->prepare("SELECT payment_info FROM users WHERE id = ?");
$stmtPay->execute([$_SESSION['user_id']]);
$raw = $stmtPay->fetchColumn();

// 2) Zahlungsmethoden dekodieren
$methods = json_decode($raw, true);
if (!is_array($methods)) {
    $pi = trim((string)$raw);
    if ($pi !== '') {
        $digits = preg_replace('/\D/', '', $pi);
        $last4  = substr($digits, -4) ?: substr($pi, -4);
        $methods = [[
            'id'     => 'stored',
            'type'   => 'Gespeichert',
            'last4'  => $last4,
            'label'  => $pi
        ]];
    } else {
        $methods = [];
    }
}

// 3) Keine Zahlungsmethoden vorhanden
if (empty($methods)) {
    http_response_code(400);
    echo json_encode([
        'success'=>false,
        'error'=>'no_payment_info',
        'message'=>'Bitte legen Sie zuerst eine Zahlungsmethode in Ihrem Konto an.'
    ]);
    exit;
}

// 4) payment_method vs. gift_code auswerten
$giftCode      = trim($input['gift_code'] ?? '');
$paymentMethod = trim($input['payment_method'] ?? '');

if ($giftCode !== '') {
    $usedPayment = 'GUTSCHEIN:'.strtoupper(preg_replace('/[^A-Za-z0-9]/','',$giftCode));
} elseif ($paymentMethod !== '') {
    $validIds = array_column($methods, 'id');
    if (!in_array($paymentMethod, $validIds, true)) {
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'Ungültige Zahlungsmethode']);
        exit;
    }
    $usedPayment = $paymentMethod;
} else {
    http_response_code(400);
    echo json_encode([
        'success'=>false,
        'error'=>'missing_payment',
        'message'=>'Bitte wählen Sie eine Zahlungsmethode oder geben Sie einen Gutscheincode ein.'
    ]);
    exit;
}

// 5) Bestellsumme berechnen
$totalAmount = 0;
foreach ($input['items'] as $it) {
    $qty = (int)($it['quantity'] ?? 0);
    $price = (float)($it['price'] ?? 0);
    if ($qty > 0 && $price > 0) {
        $totalAmount += $qty * $price;
    }
}

try {
    $pdo->beginTransaction();

    // 6) Gutschein prüfen und aktualisieren (nur innerhalb der Transaktion!)
    if ($giftCode !== '') {
        $stmtGift = $pdo->prepare("SELECT amount FROM vouchers WHERE code = ? AND is_active = 1 FOR UPDATE");
        $stmtGift->execute([$giftCode]);
        $voucher = $stmtGift->fetch(PDO::FETCH_ASSOC);

        if (!$voucher) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ungültiger oder abgelaufener Gutschein.']);
            exit;
        }

        $voucherAmount = (float) $voucher['amount'];

        if ($voucherAmount < $totalAmount) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Gutscheinbetrag reicht nicht aus.']);
            exit;
        }

        $restbetrag = $voucherAmount - $totalAmount;

$stmtUpd = $pdo->prepare("UPDATE vouchers SET amount = ?, is_active = ? WHERE code = ?");
$success = $stmtUpd->execute([
    $restbetrag,
    ($restbetrag > 0 ? 1 : 0),
    $giftCode
]);

if (!$success) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Aktualisieren des Gutscheins.'
    ]);
    exit;
}
    $totalAmount = 0;

    }

    // 7) Bestellung speichern
    $stmtO = $pdo->prepare("
        INSERT INTO orders (user_id, payment_used, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmtO->execute([$_SESSION['user_id'], $usedPayment]);
    $orderId = $pdo->lastInsertId();

    // 8) Bestellte Produkte speichern
    $stmtI = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity)
        VALUES (?, ?, ?)
    ");
    foreach ($input['items'] as $it) {
        $pid = (int)($it['id'] ?? 0);
        $qty = (int)($it['quantity'] ?? 0);
        if ($pid > 0 && $qty > 0) {
            $stmtI->execute([$orderId, $pid, $qty]);
        }
    }

    $pdo->commit();

    echo json_encode(['success'=>true,'orderId'=>$orderId]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'message'=>'Fehler beim Speichern: '.$e->getMessage()
    ]);
}
?>
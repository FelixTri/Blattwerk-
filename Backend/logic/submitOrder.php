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

// 2) Versuch JSON‐Decode
$methods = json_decode($raw, true);

// 3) Fallback: falls kein Array, aber ein Nicht-Leer-String
if (!is_array($methods)) {
    $pi = trim((string)$raw);
    if ($pi !== '') {
        // letzte 4 Ziffern extrahieren
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

// 4) Keine Methoden?
if (empty($methods)) {
    http_response_code(400);
    echo json_encode([
        'success'=>false,
        'error'=>'no_payment_info',
        'message'=>'Bitte legen Sie zuerst eine Zahlungsmethode in Ihrem Konto an.'
    ]);
    exit;
}

// 5) payment_method vs. gift_code auswerten
$giftCode      = trim($input['gift_code'] ?? '');
$paymentMethod = trim($input['payment_method'] ?? '');

if ($giftCode !== '') {
    $usedPayment = 'GUTSCHEIN:'.strtoupper(preg_replace('/[^A-Za-z0-9]/','',$giftCode));
} elseif ($paymentMethod !== '') {
    // Prüfen, ob ID existiert
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

// 6) Bestellung speichern (Spalte payment_used in orders vorausgesetzt)
try {
    $pdo->beginTransaction();
    // Falls deine orders-Tabelle payment_used enthält, nimm den Kommentar raus:
    $stmtO = $pdo->prepare("
        INSERT INTO orders (user_id, payment_used, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmtO->execute([$_SESSION['user_id'], $usedPayment]);
    $orderId = $pdo->lastInsertId();

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
<?php
// Bestellung absenden und speichern
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['items']) || !is_array($input['items'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Ungültige Bestelldaten']);
    exit;
}

require_once __DIR__ . '/../helpers/dbaccess.php';
$pdo = DbAccess::connect();

// Zahlungsmethoden laden
$stmtPay = $pdo->prepare("SELECT payment_info FROM users WHERE id = ?");
$stmtPay->execute([$_SESSION['user_id']]);
$raw = $stmtPay->fetchColumn();

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

if (empty($methods)) {
    http_response_code(400);
    echo json_encode([
        'success'=>false,
        'error'=>'no_payment_info',
        'message'=>'Bitte legen Sie zuerst eine Zahlungsmethode in Ihrem Konto an.'
    ]);
    exit;
}

$giftCode = trim($input['gift_code'] ?? '');
$paymentMethod = trim($input['payment_method'] ?? '');
$customPayment = trim($input['custom_payment'] ?? '');
$usedPayment = '';
$validIds = array_column($methods, 'id');

// Gutschein-Notiz vorbereiten
$voucherNote = '';
if (!empty($giftCode)) {
    $cleanCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $giftCode));
    $voucherNote = 'GUTSCHEIN:' . $cleanCode;
    $usedPayment .= $voucherNote;
}

// Zahlungsmethode hinzufügen
if (!empty($paymentMethod)) {
    if (!in_array($paymentMethod, $validIds, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ungültige Zahlungsmethode']);
        exit;
    }
    $usedPayment .= ($usedPayment !== '' ? ' + ' : '') . $paymentMethod;
} elseif (!empty($customPayment)) {
    $usedPayment .= ($usedPayment !== '' ? ' + ' : '') . 'CUSTOM:' . $customPayment;
}

if ($usedPayment === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'missing_payment',
        'message' => 'Bitte wählen Sie eine Zahlungsmethode oder geben Sie einen Gutscheincode ein.'
    ]);
    exit;
}

// Gesamtsumme berechnen
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

    // Gutschein verrechnen
    if (!empty($giftCode)) {
        $stmtGift = $pdo->prepare("SELECT id, amount FROM vouchers WHERE code = ? AND is_active = 1 FOR UPDATE");        $stmtGift->execute([$giftCode]);
        $voucher = $stmtGift->fetch(PDO::FETCH_ASSOC);

        if (!$voucher) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ungültiger oder abgelaufener Gutschein.']);
            exit;
        }

        $voucherId = $voucher['id'];
        $voucherAmount = (float) $voucher['amount'];
        $restbetrag = 0;

        if ($voucherAmount >= $totalAmount) {
            $restbetrag = $voucherAmount - $totalAmount;
            $totalAmount = 0;
        } else {
            $totalAmount -= $voucherAmount;
            if (empty($paymentMethod) && empty($customPayment)) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Gutschein reicht nicht aus. Bitte ergänzende Zahlungsmethode auswählen.'
                ]);
                exit;
            }
        }

        // Gutschein aktualisieren
        $stmtUpd = $pdo->prepare("UPDATE vouchers SET amount = ?, is_active = ? WHERE code = ?");
        $stmtUpd->execute([
            $restbetrag,
            ($restbetrag > 0 ? 1 : 0),
            $giftCode
        ]);
    }

    // Bestellung speichern
    $stmtO = $pdo->prepare("
        INSERT INTO orders (user_id, payment_used, voucher_id, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmtO->execute([$_SESSION['user_id'], $usedPayment, $voucherId ?? null]);
    $orderId = $pdo->lastInsertId();

    // Artikel speichern
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

    echo json_encode(['success' => true, 'orderId' => $orderId]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Speichern: ' . $e->getMessage()
    ]);
}
?>
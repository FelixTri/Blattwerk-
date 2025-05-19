<?php // Bestellung absenden und speichern
// Datei wird aufgerufen, wenn eine Bestellung aufgegeben wird
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
require_once __DIR__ . '/../helpers/dbaccess.php';
$pdo = DbAccess::connect();

// Original payment_info auslesen
$stmtPay = $pdo->prepare("SELECT payment_info FROM users WHERE id = ?");
$stmtPay->execute([$_SESSION['user_id']]);
$raw = $stmtPay->fetchColumn();

// Zahlungsmethoden dekodieren
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

// Keine Zahlungsmethoden vorhanden
if (empty($methods)) {
    http_response_code(400);
    echo json_encode([
        'success'=>false,
        'error'=>'no_payment_info',
        'message'=>'Bitte legen Sie zuerst eine Zahlungsmethode in Ihrem Konto an.'
    ]);
    exit;
}

// payment_method vs. gift_code auswerten
$giftCode      = trim($input['gift_code'] ?? '');
$paymentMethod = trim($input['payment_method'] ?? '');
$customPayment  = trim($input['custom_payment'] ?? '');

$usedPayment = '';
$validIds = array_column($methods, 'id');

// Gutschein-Teil vorbereiten
if (!empty($giftCode)) {
    $cleanCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $giftCode));
    $usedPayment .= 'GUTSCHEIN:' . $cleanCode;
}

// Zahlungsmethoden-Teil nur prüfen, wenn angegeben
if (!empty($paymentMethod)) {
    if (!in_array($paymentMethod, $validIds, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ungültige Zahlungsmethode']);
        exit;
    }
    if ($usedPayment !== '') {
        $usedPayment .= ' + ';
    }
    $usedPayment .= $paymentMethod;
} elseif (!empty($customPayment)) {
    if ($usedPayment !== '') {
        $usedPayment .= ' + ';
    }
    $usedPayment .= 'CUSTOM:' . $customPayment;
}

// Sicherstellen, dass mindestens eine Zahlart vorhanden ist
if ($usedPayment === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'missing_payment',
        'message' => 'Bitte wählen Sie eine Zahlungsmethode oder geben Sie einen Gutscheincode ein.'
    ]);
    exit;
}

// Bestellsumme berechnen
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

    // Gutschein prüfen und ggf. Teilbetrag anwenden
if (!empty($giftCode)) {
    // Debug: Gutschein anzeigen
    error_log("→ Gutscheinprüfung: Code = '$giftCode'");

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
    $restbetrag = 0;

    if ($voucherAmount >= $totalAmount) {
        // Gutschein deckt alles ab
        $restbetrag = $voucherAmount - $totalAmount;
        $totalAmount = 0;
        error_log("→ Gutschein deckt Bestellung vollständig ab. Neuer Restbetrag: $restbetrag");
    } else {
        // Gutschein deckt nur Teilbetrag
        $totalAmount -= $voucherAmount;
        error_log("→ Gutschein deckt Teilbetrag ($voucherAmount), Rest zu zahlen: $totalAmount");

        // Prüfe, ob Zahlungsmethode vorhanden ist
        if (empty($paymentMethod)) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Gutschein reicht nicht aus. Bitte ergänzende Zahlungsmethode auswählen.'
            ]);
            exit;
        }
    }

    $sql = "UPDATE vouchers SET amount = ?, is_active = ? WHERE code = ?";
$stmtUpd = $pdo->prepare($sql);

if (!$stmtUpd) {
    throw new Exception("Fehler beim Vorbereiten des Updates: " . implode(", ", $pdo->errorInfo()));
}

$success = $stmtUpd->execute([
    $restbetrag,
    ($restbetrag > 0 ? 1 : 0),
    $giftCode
]);

    if (!$success) {
        error_log("→ FEHLER beim Gutschein-Update: $giftCode → $restbetrag");
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fehler beim Aktualisieren des Gutscheins.'
        ]);
        exit;
    }

    error_log("→ Gutschein erfolgreich aktualisiert: $giftCode → $restbetrag");
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

error_log("Update Gutschein mit: $restbetrag / " . ($restbetrag > 0 ? 1 : 0) . " / $giftCode");
?>
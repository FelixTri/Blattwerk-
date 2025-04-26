<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}
$userId = (int) $_SESSION['user_id'];

// 2) Payload parsen
$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)
    || !isset($input['items'])
    || !is_array($input['items'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige Bestelldaten']);
    exit;
}
$items        = $input['items'];
$giftCode     = trim((string)($input['gift_code'] ?? ''));
$paymentMethod = trim((string)($input['payment_method'] ?? ''));

$dbHost = 'localhost';
$dbName = 'blattwerk_shop';
$dbUser = 'root';
$dbPass = '';
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- Prüfen, ob der User Zahlungsdaten hat ---
    $stmt = $pdo->prepare("SELECT payment_info FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $payJson = $stmt->fetchColumn();
    $methods = json_decode($payJson, true) ?: [];

    if (empty($methods)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'no_payment_info',
            'message' => 'Bitte legen Sie zuerst eine Zahlungsmethode in Ihrem Konto an.'
        ]);
        exit;
    }

    // --- Auswählen: Gift-Code oder Zahlungsmethode ---
    if ($giftCode !== '') {
        // Dummy-Logik für Gutschein
        $usedPayment = 'GUTSCHEIN:' . strtoupper(preg_replace('/[^A-Za-z0-9]/','', $giftCode));
    } elseif ($paymentMethod !== '') {
        // prüfen, ob das gewählte payment_method im Array existiert
        $validIds = array_column($methods, 'id'); 
        if (!in_array($paymentMethod, $validIds, true)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Ungültige Zahlungsmethode'
            ]);
            exit;
        }
        $usedPayment = $paymentMethod;
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'missing_payment',
            'message' => 'Bitte wählen Sie eine Zahlungsmethode oder geben Sie einen Gutscheincode ein.'
        ]);
        exit;
    }

    $pdo->beginTransaction();
    $stmtOrder = $pdo->prepare(
        "INSERT INTO orders (user_id, /*payment_used,*/ created_at) VALUES (?, /*?,*/ NOW())"
    );
   
    $stmtOrder->execute([$userId]);
    $orderId = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)"
    );
    foreach ($items as $it) {
        $pid = (int)($it['id'] ?? 0);
        $qty = (int)($it['quantity'] ?? 0);
        if ($pid > 0 && $qty > 0) {
            $stmtItem->execute([$orderId, $pid, $qty]);
        }
    }
    $pdo->commit();

    echo json_encode(['success' => true, 'orderId' => $orderId]);
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Speichern: ' . $e->getMessage()
    ]);
    exit;
}
<?php // Gutschein erstellen
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once(__DIR__ . '/../helpers/dbaccess.php');

$pdo = DbAccess::connect();

function generateVoucherCode() {
    return str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = isset($_POST["amount"]) ? floatval($_POST["amount"]) : 0;
    $expires = isset($_POST["expires"]) ? trim($_POST["expires"]) : null;

    if ($amount <= 0) {
        echo json_encode(["success" => false, "message" => "Ungültiger Betrag."]);
        exit;
    }

    // Datum validieren
    $expiresAt = null;
    if ($expires !== "") {
        $ts = strtotime($expires);
        if ($ts === false) {
            echo json_encode(["success" => false, "message" => "Ungültiges Ablaufdatum."]);
            exit;
        }
        $expiresAt = date('Y-m-d', $ts);
    }

    $code = generateVoucherCode();

    try {
        // Stelle sicher, dass der Code eindeutig ist
        $stmt = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
        $stmt->execute([$code]);

        while ($stmt->rowCount() > 0) {
            $code = generateVoucherCode();
            $stmt->execute([$code]);
        }

        $stmt = $pdo->prepare("INSERT INTO vouchers (code, amount, is_active, expires_at) VALUES (?, ?, 1, ?)");
        $success = $stmt->execute([$code, $amount, $expiresAt]);

        if ($success) {
            echo json_encode(["success" => true, "code" => $code]);
        } else {
            echo json_encode(["success" => false, "message" => "Datenbankfehler beim Speichern."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
?>
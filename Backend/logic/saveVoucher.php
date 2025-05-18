<?php
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

    if ($amount <= 0) {
        echo json_encode(["success" => false, "message" => "Ungültiger Betrag."]);
        exit;
    }

    $code = generateVoucherCode();

    try {
        $stmt = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
        $stmt->execute([$code]);

        while ($stmt->rowCount() > 0) {
            $code = generateVoucherCode();
            $stmt->execute([$code]);
        }

        $stmt = $pdo->prepare("INSERT INTO vouchers (code, amount, is_active) VALUES (?, ?, 1)");
        $success = $stmt->execute([$code, $amount]);

        if ($success) {
            echo json_encode(["success" => true, "message" => "Gutschein erfolgreich erstellt.", "code" => $code]);
        } else {
            echo json_encode(["success" => false, "message" => "Fehler beim Erstellen des Gutscheins."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "SQL Fehler: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Ungültige Anfrage."]);
}
?>
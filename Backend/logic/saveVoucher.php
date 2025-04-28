<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config/dbaccess.php");

// Verbindung herstellen
$pdo = DbAccess::connect();

// 5-stelligen zufälligen Code generieren
function generateVoucherCode() {
    return str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
}

// Überprüfen, ob die Anfrage ein POST ist
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = isset($_POST["amount"]) ? floatval($_POST["amount"]) : 0;

    if ($amount <= 0) {
        echo json_encode(["success" => false, "message" => "Ungültiger Betrag."]);
        exit;
    }

    $code = generateVoucherCode();

    try {
        // Prüfen, ob der Code schon existiert
        $stmt = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
        $stmt->execute([$code]);

        // Solange Code schon existiert, neuen erstellen
        while ($stmt->rowCount() > 0) {
            $code = generateVoucherCode();
            $stmt->execute([$code]);
        }

        // Gutschein speichern
        $stmt = $pdo->prepare("INSERT INTO vouchers (code, amount) VALUES (?, ?)");
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
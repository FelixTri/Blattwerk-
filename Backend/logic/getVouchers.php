<?php // Gutschein(e) abrufen
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once(__DIR__ . '/../helpers/dbaccess.php');

$pdo = DbAccess::connect();

// Einzelnen Gutschein abrufen
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    try {
        $stmt = $pdo->prepare("
            SELECT code, amount, is_active AS active, expires_at
            FROM vouchers
            WHERE code = :code
            LIMIT 1
        ");
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($voucher) {
            echo json_encode(["success" => true, "voucher" => $voucher]);
        } else {
            echo json_encode(["success" => false, "message" => "Gutschein nicht gefunden."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Fehler bei der Suche: " . $e->getMessage()]);
    }
    exit;
}

// Alle Gutscheine abrufen (inkl. Ablaufdatum)
try {
    $stmt = $pdo->query("
        SELECT id, code, amount, is_active, created_at, expires_at
        FROM vouchers
        ORDER BY created_at DESC
    ");
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($vouchers);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Fehler beim Abrufen: " . $e->getMessage()]);
}
?>
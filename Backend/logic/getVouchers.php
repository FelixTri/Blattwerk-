<?php // Gutscheinliste abrufen
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once(__DIR__ . '/../helpers/dbaccess.php');  // DB-Zugriff

$pdo = DbAccess::connect();

try {
    $stmt = $pdo->query("SELECT id, code, amount, is_active, created_at FROM vouchers ORDER BY created_at DESC"); // SQL-Statement zum Abrufen der Gutscheinliste
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($vouchers);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Fehler beim Abrufen: " . $e->getMessage()]);
}
?>

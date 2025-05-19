
<?php // Gutschein löschen
// Datei wird aufgerufen, wenn ein Gutschein gelöscht werden soll
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once(__DIR__ . '/../helpers/dbaccess.php'); // DB-Zugriff

$pdo = DbAccess::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Gutschein-ID.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Fehler beim Löschen: ' . $e->getMessage()]);
    }
}
?>
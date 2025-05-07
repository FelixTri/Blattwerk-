<?php
require_once("../helpers/dbaccess.php");

ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = DbAccess::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        try {
            // Status holen
            $stmt = $pdo->prepare("SELECT is_active FROM vouchers WHERE id = ?");
            $stmt->execute([$id]);
            $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($voucher) {
                $newStatus = $voucher['is_active'] == 1 ? 0 : 1;

                $update = $pdo->prepare("UPDATE vouchers SET is_active = ? WHERE id = ?");
                $success = $update->execute([$newStatus, $id]);

                echo json_encode(["success" => $success]);
            } else {
                echo json_encode(["success" => false, "message" => "Gutschein nicht gefunden."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Fehler beim Update: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Ungültige ID."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Ungültige Anfrage."]);
}
?>
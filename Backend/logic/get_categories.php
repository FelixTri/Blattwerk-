<?php
require_once(__DIR__ . '/../helpers/dbaccess.php');

header('Content-Type: application/json');
$pdo = DbAccess::connect();

try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode(["error" => "Fehler beim Laden der Kategorien"]);
}
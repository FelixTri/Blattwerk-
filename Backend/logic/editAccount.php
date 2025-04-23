<?php
// Fehlerausgabe aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Header setzen für JSON
header('Content-Type: application/json');
require_once '../config/dbaccess.php';

// Pfad zur Debug-Datei (eine Ebene über dem Backend-Ordner)
$logFile = __DIR__ . "/../../debug_log.txt";
file_put_contents($logFile, "Script gestartet: " . date("H:i:s") . PHP_EOL, FILE_APPEND);

// JSON-Daten empfangen
$data = json_decode(file_get_contents('php://input'), true);
file_put_contents($logFile, "Empfangene Daten: " . print_r($data, true) . PHP_EOL, FILE_APPEND);

// Überprüfen ob Daten vorhanden & gültig
if (!$data || !isset($data['username'])) {
    file_put_contents($logFile, "❌ Ungültige oder leere Daten empfangen." . PHP_EOL, FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Ungültige Daten.']);
    exit;
}

try {
    // Verbindung zur DB herstellen
    $pdo = DbAccess::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL-Update-Statement
    $sql = "UPDATE users SET 
                salutation = :salutation, 
                address = :address, 
                postal_code = :postal_code, 
                city = :city, 
                email = :email, 
                payment_info = :payment_info
            WHERE username = :username";

    $stmt = $pdo->prepare($sql);

    // Statement ausführen
    $stmt->execute([
        ':salutation'    => $data['salutation'] ?? '',
        ':address'       => $data['address'] ?? '',
        ':postal_code'   => $data['postal_code'] ?? '',
        ':city'          => $data['city'] ?? '',
        ':email'         => $data['email'] ?? '',
        ':payment_info'  => $data['payment_info'] ?? '',
        ':username'      => $data['username']
    ]);

    // Erfolg loggen
    file_put_contents($logFile, "✅ Update erfolgreich ausgeführt." . PHP_EOL, FILE_APPEND);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    file_put_contents($logFile, "❌ SQL: " . $sql . PHP_EOL, FILE_APPEND);
    file_put_contents($logFile, "❌ Daten: " . print_r($data, true) . PHP_EOL, FILE_APPEND);
    file_put_contents($logFile, "❌ DB-Fehler: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB-Fehler: ' . $e->getMessage()]);
}
<?php
// Fehlerausgabe deaktivieren, nur JSON zurückliefern
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/dbaccess.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Nicht eingeloggt']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Ungültige Daten']);
    exit;
}

try {
    $pdo = DbAccess::connect();

    // Account updaten, inkl. payment_info
    $stmt = $pdo->prepare("
        UPDATE users
           SET salutation    = :salutation,
               address       = :address,
               postal_code   = :postal_code,
               city          = :city,
               email         = :email,
               payment_info  = :payment_info
         WHERE id = :id
    ");
    $stmt->execute([
        ':salutation'   => $data['salutation']   ?? '',
        ':address'      => $data['address']      ?? '',
        ':postal_code'  => $data['postal_code']  ?? '',
        ':city'         => $data['city']         ?? '',
        ':email'        => $data['email']        ?? '',
        ':payment_info' => $data['payment_info'] ?? '',
        ':id'           => $_SESSION['user_id']
    ]);

    // Frisch gespeicherte Daten zurückliefern
    $stmt2 = $pdo->prepare("
        SELECT 
            id,
            username,
            salutation,
            address,
            postal_code,
            city,
            email,
            payment_info,
            role,
            active
          FROM users
         WHERE id = ?
    ");
    $stmt2->execute([ $_SESSION['user_id'] ]);
    $user = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

    echo json_encode([
        'success' => true,
        'user'    => $user
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Datenbank-Fehler: ' . $e->getMessage()
    ]);
}
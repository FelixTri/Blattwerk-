<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../helpers/dbaccess.php'; // NEU

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Ungültige Registrierungsdaten.'
    ]);
    exit;
}

$salutation   = trim($data['salutation'] ?? '');
$firstName    = trim($data['firstName'] ?? '');
$lastName     = trim($data['lastName'] ?? '');
$address      = trim($data['address'] ?? '');
$postalCode   = trim($data['postalCode'] ?? '');
$city         = trim($data['city'] ?? '');
$email        = trim($data['email'] ?? '');
$username     = trim($data['username'] ?? '');
$password     = trim($data['password'] ?? '');
$paymentInfo  = trim($data['paymentInfo'] ?? '');
$role         = trim($data['role'] ?? 'customer');
$active       = (int)($data['active'] ?? 1);

// Pflichtfelder prüfen
if (
    $salutation === '' || $firstName === '' || $lastName === '' ||
    $address === '' || $postalCode === '' || $city === '' ||
    $email === '' || $username === '' || $password === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Bitte fülle alle Pflichtfelder aus.'
    ]);
    exit;
}

// Passwort hashen
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo = DbAccess::connect(); // NEU

    // Doppelten User prüfen
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->execute([$email, $username]);
    if ($check->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Ein Benutzer mit dieser E-Mail oder diesem Benutzernamen existiert bereits.'
        ]);
        exit;
    }

    // Einfügen
    $stmt = $pdo->prepare("
        INSERT INTO users 
        (salutation, first_name, last_name, address, postal_code, city, email, username, password, payment_info, role, active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $salutation,
        $firstName,
        $lastName,
        $address,
        $postalCode,
        $city,
        $email,
        $username,
        $hashedPassword,
        $paymentInfo,
        $role,
        $active
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
<?php
header('Content-Type: application/json');

<<<<<<< HEAD
// Anpassung an deine Einstellungen
$dbHost = 'localhost';
$dbName = 'blattwerk_shop';
$dbUser = 'root';
$dbPass = '';
=======
// Anpassung an Einstellungen
$dbHost = 'localhost';
$dbName = 'blattwerk_shop';  
$dbUser = 'root';            
$dbPass = '';                
>>>>>>> 6916f219898ed6fafc1e69a2da279c83afe2b45f

try {
    // PDO-Verbindung
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
      'message' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()
    ]);
    exit;
}

// JSON-Daten empfangen
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Keine oder ungültige Daten empfangen.'
    ]);
    exit;
}

// Pflichtfelder prüfen
$requiredFields = ['salutation', 'firstName', 'lastName', 'address', 'postalCode', 'city', 'email', 'username', 'password'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Feld '$field' ist erforderlich."
        ]);
        exit;
    }
}

<<<<<<< HEAD
// Daten übernehmen
=======
//Daten aus dem Array übernehmen
>>>>>>> 6916f219898ed6fafc1e69a2da279c83afe2b45f
$salutation  = trim($data['salutation']);
$firstName   = trim($data['firstName']);
$lastName    = trim($data['lastName']);
$address     = trim($data['address']);
$postalCode  = trim($data['postalCode']);
$city        = trim($data['city']);
$email       = trim($data['email']);
$username    = trim($data['username']);
$password    = $data['password'];
$paymentInfo = isset($data['paymentInfo']) ? trim($data['paymentInfo']) : '';

<<<<<<< HEAD
// 🚀 Standardmäßig Benutzerrolle und aktiv setzen
$role  = 'user';
$active = 1;

// Prüfen, ob E-Mail oder Benutzername bereits existieren
=======
//Prüfen, ob Nutzername oder E-Mail bereits existieren
>>>>>>> 6916f219898ed6fafc1e69a2da279c83afe2b45f
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email OR username = :username');
    $stmt->execute([
        ':email' => $email,
        ':username' => $username
    ]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'E-Mail oder Benutzername bereits vergeben.'
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler bei der Überprüfung: ' . $e->getMessage()
    ]);
    exit;
}

// Passwort hashen
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

<<<<<<< HEAD
// Benutzer speichern
=======
// SQL-Insert
>>>>>>> 6916f219898ed6fafc1e69a2da279c83afe2b45f
try {
    $stmt = $pdo->prepare('
        INSERT INTO users
            (salutation, first_name, last_name, address, postal_code, city, email, username, password, payment_info, role, active)
        VALUES
            (:salutation, :first_name, :last_name, :address, :postal_code, :city, :email, :username, :password, :payment_info, :role, :active)
    ');
    $result = $stmt->execute([
        ':salutation'   => $salutation,
        ':first_name'   => $firstName,
        ':last_name'    => $lastName,
        ':address'      => $address,
        ':postal_code'  => $postalCode,
        ':city'         => $city,
        ':email'        => $email,
        ':username'     => $username,
        ':password'     => $hashedPassword,
        ':payment_info' => $paymentInfo,
        ':role'         => $role,
        ':active'       => $active
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Registrierung erfolgreich.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registrierung fehlgeschlagen.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
    exit;
}
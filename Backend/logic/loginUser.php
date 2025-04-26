<?php
session_start();
header('Content-Type: application/json');

// DB-Verbindung
$dbHost = 'localhost';
$dbName = 'blattwerk_shop';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()
    ]);
    exit;
}

// Request‐Body parsen
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Ungültige Login-Daten empfangen.'
    ]);
    exit;
}

$email    = $data['email']    ?? '';
$password = $data['password'] ?? '';
$remember = $data['remember'] ?? false;

try {
    // User laden (inkl. payment_info!)
    $stmt = $pdo->prepare("
        SELECT 
            id, username, first_name, last_name, email, password, role, active, payment_info
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Auth prüfen
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'E-Mail oder Passwort ist ungültig.'
        ]);
        exit;
    }
    if ((int)$user['active'] === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Dein Benutzerkonto ist deaktiviert.'
        ]);
        exit;
    }

    // Session & optional Remember-Cookies
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];
    if ($remember) {
        setcookie("user_id",   $user['id'],               time() + 86400*30, "/");
        setcookie("user_hash", hash('sha256', $user['password']), time() + 86400*30, "/");
    }

    // Passwort aus Antwort entfernen
    unset($user['password']);

    // Antwort mit payment_info
    echo json_encode([
        'success' => true,
        'user'    => $user
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
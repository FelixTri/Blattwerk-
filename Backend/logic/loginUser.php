<?php
session_start();
header('Content-Type: application/json');

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

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Ungültige Login-Daten empfangen.'
    ]);
    exit;
}

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$remember = $data['remember'] ?? false;

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    if ($remember) {
        setcookie("user_id", $user['id'], time() + (86400 * 30), "/");
        setcookie("user_hash", hash('sha256', $user['password']), time() + (86400 * 30), "/");
    }

    unset($user['password']);

    echo json_encode([
        'success' => true,
        'user' => $user
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
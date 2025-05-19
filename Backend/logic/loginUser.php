<?php // User-Login
// Datei wird aufgerufen, wenn der User sich einloggen möchte
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../helpers/dbaccess.php'; // DB-Zugriff

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
// Login‐Daten validieren
try {
    $pdo = DbAccess::connect();

    // User laden
    $stmt = $pdo->prepare("
        SELECT 
            id, username, first_name, last_name, email, password, role, active, payment_info
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Passwort/Email/User prüfen
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

    // Session & Cookies setzen
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];

    if ($remember) {
        setcookie("user_id",   $user['id'],               time() + 86400*30, "/");
        setcookie("user_hash", hash('sha256', $user['password']), time() + 86400*30, "/");
    }

    // Passwort entfernen
    unset($user['password']);

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
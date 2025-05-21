<?php
session_start();
header('Content-Type: application/json');
require_once(__DIR__ . '/../helpers/dbaccess.php');

$pdo = DbAccess::connect();

// Eingabedaten als JSON lesen
$input = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
  echo json_encode(['success' => false, 'message' => 'Nicht angemeldet.']);
  exit;
}

$requiredFields = ['salutation', 'address', 'postal_code', 'city', 'email', 'password'];
foreach ($requiredFields as $f) {
  if (!isset($input[$f]) || trim($input[$f]) === '') {
    echo json_encode(['success' => false, 'message' => "Feld '$f' fehlt oder ist leer."]);
    exit;
  }
}

// Passwort validieren
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($input['password'], $user['password'])) {
  echo json_encode(['success' => false, 'message' => 'Falsches Passwort.']);
  exit;
}

// Update der Daten
$stmt = $pdo->prepare("
  UPDATE users SET
    salutation = :salutation,
    address = :address,
    postal_code = :postal_code,
    city = :city,
    email = :email,
    payment_info = :payment_info
  WHERE id = :id
");

$success = $stmt->execute([
  'salutation'   => $input['salutation'],
  'address'      => $input['address'],
  'postal_code'  => $input['postal_code'],
  'city'         => $input['city'],
  'email'        => $input['email'],
  'payment_info' => $input['payment_info'] ?? '',
  'id'           => $userId
]);

if ($success) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern.']);
}
<?php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'blattwerk_shop';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_GET['id'] ?? null;
    if (!$id) throw new Exception("Kein Produkt-ID angegeben");

    $stmt = $pdo->prepare("SELECT id, name, description, price FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Produkt nicht gefunden']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Fehler beim Abrufen des Produkts', 'details' => $e->getMessage()]);
}
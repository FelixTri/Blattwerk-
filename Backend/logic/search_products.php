<?php
header('Content-Type: application/json');

$dbHost = 'localhost';
$dbName = 'blattwerk_shop';  
$dbUser = 'root';            
$dbPass = '';              

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $_GET['query'] ?? '';
    $stmt = $pdo->prepare("SELECT id, name, description, image, price FROM products WHERE name LIKE :query OR description LIKE :query");
    $stmt->execute(['query' => "%$query%"]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Verbindung zur Datenbank fehlgeschlagen', 'details' => $e->getMessage()]);
}
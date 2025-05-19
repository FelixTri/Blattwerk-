<?php
require_once __DIR__ . '/../helpers/dbaccess.php';

header('Content-Type: application/json');

try { // Produkt-Suchfunktion
    $pdo = DbAccess::connect(); 

    $query = $_GET['query'] ?? '';
    $stmt = $pdo->prepare("
        SELECT id, name, description, image, price 
        FROM products 
        WHERE name LIKE :query OR description LIKE :query
    ");
    $stmt->execute(['query' => "%$query%"]); //Statement zum Abrufen der Produkte

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Verbindung zur Datenbank fehlgeschlagen',
        'details' => $e->getMessage()
    ]);
}
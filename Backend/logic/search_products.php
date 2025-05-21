<?php
require_once __DIR__ . '/../helpers/dbaccess.php';
header('Content-Type: application/json');

try {
    $pdo = DbAccess::connect(); 

    $query = $_GET['query'] ?? '';
    $category = $_GET['category'] ?? '';

    $sql = "
        SELECT id, name, description, image, price 
        FROM products 
        WHERE (name LIKE :query OR description LIKE :query)
    ";
    $params = [':query' => "%$query%"];

    if (!empty($category)) {
        $sql .= " AND category_id = :category";
        $params[':category'] = $category;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Verbindung zur Datenbank fehlgeschlagen',
        'details' => $e->getMessage()
    ]);
}
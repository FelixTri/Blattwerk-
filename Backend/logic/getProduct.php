<?php // Produktdetails abrufen
// Datei wird aufgerufen, wenn die Produktdetails abgerufen werden sollen
require_once __DIR__ . '/../helpers/dbaccess.php'; // DB-Zugriff

header('Content-Type: application/json');

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['error' => 'Ungültige Produkt-ID']);
    exit;
}

try {
    $pdo = DbAccess::connect(); // DB-Zugriff

    $sql = "
        SELECT
            p.*, 
            c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ";  // SQL-Statement zum Abrufen der Produktdetails
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]); 

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($product ?: ['error' => 'Produkt nicht gefunden']);

} catch (Exception $e) {
    echo json_encode(['error' => 'Serverfehler: ' . $e->getMessage()]);
}
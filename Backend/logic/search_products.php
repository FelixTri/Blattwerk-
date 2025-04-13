<?php
header('Content-Type: application/json');

$dbHost = 'localhost';
$dbName = 'blattwerk_shop';  
$dbUser = 'root';            
$dbPass = '';              

$pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);

$query = $_GET['query'] ?? '';
$stmt = $pdo->prepare("SELECT name, description, image FROM products WHERE name LIKE :query OR description LIKE :query");
$stmt->execute(['query' => "%$query%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
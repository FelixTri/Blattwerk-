<?php
class Product {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../config/dbaccess.php';
        $this->pdo = DbAccess::connect();
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM products");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductsByIds($ids) {
        if (empty($ids)) return [];
    
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
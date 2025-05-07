<?php
require_once __DIR__ . '/../helpers/dbaccess.php';

class Order {
    private $pdo;

    public function __construct() {
        $this->pdo = DbAccess::connect();
    }

    /**
     * Liefert alle Bestellungen eines Users, sortiert nach Datum aufsteigend
     * @param int $userId
     * @return array
     */
    public function findByUser(int $userId): array {
        $sql = "
            SELECT
                o.id,
                o.created_at,
                COALESCE(i.invoice_number, '') AS invoice_number
            FROM orders o
            LEFT JOIN invoices i ON i.order_id = o.id
            WHERE o.user_id = :user_id
            ORDER BY o.created_at ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liefert Details zu einer einzelnen Bestellung inklusive Positionen und User-Adresse
     * @param int $orderId
     * @param int $userId
     * @return array|null
     */
    public function findById(int $orderId, int $userId): ?array {
        // Bestellung mit User-Daten laden
        $sqlOrder = "
            SELECT
                o.id,
                o.created_at,
                COALESCE(i.invoice_number, '') AS invoice_number,
                u.first_name,
                u.last_name,
                u.address,
                u.postal_code,
                u.city
            FROM orders o
            JOIN users u ON u.id = o.user_id
            LEFT JOIN invoices i ON i.order_id = o.id
            WHERE o.id = :id
              AND o.user_id = :user_id
        ";
        $stmtOrder = $this->pdo->prepare($sqlOrder);
        $stmtOrder->execute([
            'id' => $orderId,
            'user_id' => $userId
        ]);
        $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            return null;
        }

        // Positionen laden
        $sqlItems = "
            SELECT
                p.id AS product_id,
                p.name,
                p.price,
                oi.quantity
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = :id
        ";
        $stmtItems = $this->pdo->prepare($sqlItems);
        $stmtItems->execute(['id' => $orderId]);
        $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * Erzeugt oder liefert bestehende Rechnungsnummer
     * @param int $orderId
     * @return string
     */
    public function getOrCreateInvoiceNumber(int $orderId): string {
        // Prüfen, ob bereits vorhanden
        $sqlCheck = "SELECT invoice_number FROM invoices WHERE order_id = :order_id";
        $stmtCheck = $this->pdo->prepare($sqlCheck);
        $stmtCheck->execute(['order_id' => $orderId]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['invoice_number'])) {
            return $row['invoice_number'];
        }

        // Generieren: z.B. RW{YYYY}-{laufende Nummer}
        $prefix = 'RW' . date('Y');
        $sqlCount = "SELECT COUNT(*) AS cnt FROM invoices WHERE invoice_number LIKE :prefix";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute(['prefix' => "$prefix%"]);
        $count = (int)$stmtCount->fetchColumn();
        $next = str_pad((string)($count + 1), 6, '0', STR_PAD_LEFT);
        $invoiceNumber = $prefix . '-' . $next;

        // Speichern
        $sqlInsert = "
            INSERT INTO invoices (order_id, invoice_number, created_at)
            VALUES (:order_id, :invoice_number, NOW())
        ";
        $stmtInsert = $this->pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            'order_id'      => $orderId,
            'invoice_number'=> $invoiceNumber
        ]);

        return $invoiceNumber;
    }
}
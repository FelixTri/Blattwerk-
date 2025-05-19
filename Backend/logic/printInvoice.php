<?php // Rechnung erstellen
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once __DIR__ . '/../models/Order.class.php'; // Abrufen der Bestellungsdaten

if (!isset($_SESSION['user_id']) || !isset($_GET['orderId'])) {
    http_response_code(403);
    exit('Zugriff verweigert');
}

$orderId = (int)$_GET['orderId'];
$model = new Order();
// Prüfen und ggf. Invoice-Nummer erzeugen
$invoiceNumber = $model->getOrCreateInvoiceNumber($orderId);
// Bestellung und Positionen laden
$order = $model->findById($orderId, (int)$_SESSION['user_id']);
if (!$order) {
    http_response_code(404);
    exit('Bestellung nicht gefunden');
}

$userName   = htmlspecialchars($order['first_name'] . ' ' . $order['last_name']);
$userAddress = nl2br(htmlspecialchars($order['address']));
$userCity   = htmlspecialchars($order['postal_code'] . ' ' . $order['city']);


// Ausgabe als HTML
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Rechnung '.$invoiceNumber.'</title>';
echo '<style>body{font-family:Arial,sans-serif;}table{width:100%;border-collapse:collapse;}td,th{border:1px solid #ccc;padding:8px;}</style>';
echo '</head><body>';
echo '<h1>Rechnung '.$invoiceNumber.'</h1>';
echo '<p>' . $userName . '</p>';
echo '<p>' . $userAddress . '<br>' . $userCity . '</p>';
echo '<p>Datum: '.date('d.m.Y', strtotime($order['created_at'])).'</p>';
echo '<h2>Positionen</h2><table><tr><th>Artikel</th><th>Menge</th><th>Preis</th></tr>';
foreach($order['items'] as $item) {
    echo '<tr>';
    echo '<td>'.htmlspecialchars($item['name']).'</td>';
    echo '<td>'.$item['quantity'].'</td>';
    echo '<td>'.number_format($item['price'],2,',','.').' €</td>';
    echo '</tr>';
}
echo '</table>';
echo '<p><strong>Gesamt: '.number_format(array_sum(array_column($order['items'],'price')) * 1,2,',','.').' €</strong></p>';
echo '</body></html>';

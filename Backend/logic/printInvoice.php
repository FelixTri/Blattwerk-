<?php
session_start();
require_once __DIR__ . '/../helpers/dbaccess.php';

$orderId = (int)($_GET['orderId'] ?? 0);
if ($orderId <= 0 || !isset($_SESSION['user_id'])) {
    echo "Ungültiger Zugriff.";
    exit;
}

try {
    $pdo = DbAccess::connect();

    // Bestellung & User prüfen
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email
        FROM orders o
        JOIN users u ON u.id = o.user_id
        WHERE o.id = ? AND u.id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "Keine Berechtigung oder Bestellung nicht gefunden.";
        exit;
    }

    // Artikel laden
    $itemsStmt = $pdo->prepare("
        SELECT p.name, oi.quantity, p.price
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Gutschein laden (falls vorhanden)
    $voucher = null;
    if (!empty($order['voucher_id'])) {
        $vstmt = $pdo->prepare("SELECT code, amount FROM vouchers WHERE id = ?");
        $vstmt->execute([$order['voucher_id']]);
        $voucher = $vstmt->fetch(PDO::FETCH_ASSOC);
    }

    // Gesamtsumme berechnen
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['quantity'] * $item['price'];
    }

    $discount = $voucher ? min($voucher['amount'], $subtotal) : 0;
    $total = $subtotal - $discount;
} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Rechnung</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
  <h1>Rechnung #<?= htmlspecialchars($order['id']) ?></h1>
  <p><strong>Datum:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
  <p><strong>Kunde:</strong> <?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>

  <hr>

  <table class="table">
    <thead>
      <tr>
        <th>Produkt</th>
        <th>Menge</th>
        <th>Einzelpreis (€)</th>
        <th>Zwischensumme (€)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td><?= number_format($item['price'], 2) ?> €</td>
          <td><?= number_format($item['quantity'] * $item['price'], 2) ?> €</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3" class="text-end">Zwischensumme:</th>
        <td><?= number_format($subtotal, 2) ?> €</td>
      </tr>
      <?php if ($voucher): ?>
      <tr>
        <th colspan="3" class="text-end">Gutschein (<?= htmlspecialchars($voucher['code']) ?>):</th>
        <td>-<?= number_format($discount, 2) ?> €</td>
      </tr>
      <?php endif; ?>
      <tr>
        <td colspan="3" class="text-end"><strong>Gesamt:</strong></td>
        <td><strong><?= number_format($total, 2, ',', '.') ?> €</strong></td>
    </tr>
    <?php if (isset($order['payment_used']) && str_starts_with($order['payment_used'], 'GUTSCHEIN:')): ?>
        <tr>
        <td colspan="4" class="text-end text-muted">
            Hinweis: Es wurde ein Gutschein angewendet (<?= htmlspecialchars($order['payment_used']) ?>)
        </td>
        </tr>
    <?php endif; ?>
    </tfoot>
  </table>


<?php if (!empty($order['voucher']['code'])): ?>
        <p><strong>Hinweis:</strong> Gutschein <code><?= htmlspecialchars($order['voucher']['code']) ?></code> wurde eingelöst (ursprünglicher Wert: <?= number_format($order['voucher']['amount'], 2) ?> €).</p>
<?php endif; ?>

  <p>Vielen Dank für Ihren Einkauf!</p>
</body>
</html>
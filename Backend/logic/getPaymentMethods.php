<?php
// Zahlungsarten abrufen
// Datei wird aufgerufen, wenn die Zahlungsarten des eingeloggten Nutzers abgerufen werden sollen
session_start();
header('Content-Type: application/json');

if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../helpers/dbaccess.php';


    try {
            $pdo = DbAccess::connect();  // DB-Verbindung herstellen
            
            $stmt = $pdo->prepare("SELECT payment_info FROM users WHERE id = ?");
            $stmt->execute([ (int)$_SESSION['user_id'] ]);
            $raw = $stmt->fetchColumn();

            $methods = json_decode($raw, true);
        // Fallback: wenn es kein Array ist, aber ein Nicht-Leer-String
        if (!is_array($methods)) {
            $pi = trim((string)$raw);
            if ($pi !== '') {
                // letzte 4 Ziffern extrahieren (Bsp. Kreditkarte/IBAN)
                
                $digits = preg_replace('/\D/', '', $pi);

                $last4 = substr($digits, -4);
                $methods = [[
                    'id'     => 'stored',
                    'type'   => 'Gespeichert',
                    'last4'  => $last4 ?: substr($pi, -4),
                    'label'  => $pi      // gesamter Text als Fallback-Label
                ]];
            } else {
                $methods = [];
            }
        }

        echo json_encode([
            'success' => true,
            'methods' => $methods
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'DB-Fehler: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Nicht eingeloggt'
    ]);
}
<?php
class DbAccess {
    public static function connect() { // Funktion, um eine Verbindung zur Datenbank herzustellen
        $host = 'localhost';
        $dbname = 'blattwerk_shop';
        $user = 'root';
        $pass = '';

        try {
            return new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage() // Fehlermeldung, falls Verbindung fehlschlägt
            ])
            exit;
        }
    }
}
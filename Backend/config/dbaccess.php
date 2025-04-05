<?php
class DbAccess {
    public static function connect() {
        $host = 'localhost';
        $dbname = 'webshop';
        $user = 'root';
        $pass = '';

        try {
            return new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        } catch (PDOException $e) {
            die("Verbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
}
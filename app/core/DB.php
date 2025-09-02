<?php
// app/Core/DB.php
namespace App\Core;

class DB {
    private static $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            date_default_timezone_set('UTC');

            $dsn = 'mysql:host=' . \DB_HOST . ';dbname=' . \DB_NAME . ';charset=utf8mb4';
            try {
                self::$instance = new \PDO($dsn, \DB_USER, \DB_PASS, [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (\PDOException $e) {
                die('Помилка підключення до бази даних: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
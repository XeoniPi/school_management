<?php
/**
 * KMA — Database Configuration
 * Uses PDO with prepared statements for full SQL injection prevention.
 * Edit credentials to match your XAMPP setup.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'kma_school');  // Update if you used a different name in your SQL import
define('DB_USER', 'root');       // XAMPP default
define('DB_PASS', '');           // XAMPP default (set your password in production)
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never expose credentials or raw errors to the browser
            error_log('DB Connection Error: ' . $e->getMessage());
            http_response_code(503);
            die(json_encode(['error' => 'Database unavailable. Please try again later.']));
        }
    }
    return $pdo;
}
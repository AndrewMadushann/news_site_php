<?php
/**
 * db.php
 * Database connection bootstrap.
 *
 * Loads connection settings from the .env file (via includes/env.php) and
 * falls back to safe defaults. Errors surface a friendly message to users
 * and the technical detail to the PHP error log.
 */

require_once __DIR__ . '/env.php';

define('DB_HOST', env('DB_HOST',  'localhost'));
define('DB_NAME', env('DB_NAME',  'news_db'));
define('DB_USER', env('DB_USER',  'root'));
define('DB_PASS', (string) env('DB_PASS', ''));
define('DB_PORT', (int)    env('DB_PORT', 3306));

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('[db.php] Connection failed: ' . $e->getMessage());
    http_response_code(503);
    die('Service temporarily unavailable. Please try again later.');
}

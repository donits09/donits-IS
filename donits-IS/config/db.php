<?php

// =========================
// ENVIRONMENT
// =========================
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}

// =========================
// DATABASE CONFIG
// =========================
$server = $_SERVER['SERVER_NAME'] ?? 'localhost';
$isLocal = ($server === 'localhost' || $server === '127.0.0.1');

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'inventory_system');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');

// =========================
// PATH CONFIG
// =========================
if (!defined('ROOT')) define('ROOT', '/inventory-system');
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/inventory-system');

// =========================
// ERROR HANDLING
// =========================
if (!defined('DISPLAY_ERROR')) {
    define('DISPLAY_ERROR', getenv('DISPLAY_ERROR') ?: '1');
}

if (APP_ENV === 'development' || DISPLAY_ERROR === '1') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// =========================
// TIMEZONE
// =========================
date_default_timezone_set('Asia/Manila');

// =========================
// DATABASE CONNECTION
// =========================
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
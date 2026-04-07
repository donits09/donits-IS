<?php

require_once __DIR__ . '/../includes/functions.php';

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


/*
|--------------------------------------------------------------------------
| DATABASE CONFIG...
|--------------------------------------------------------------------------
*/

$defaultDbHost = $isLocal ? '127.0.0.1' : 'localhost';

if ($isLocal) {
    if (!defined('DB_HOST')) define('DB_HOST', $defaultDbHost);
    if (!defined('DB_NAME')) define('DB_NAME', 'inventory_system');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
} else {
    if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: $defaultDbHost);
    if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'inventory_system');
    if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
    if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
}

/*
|--------------------------------------------------------------------------
| BASE URL + ROOT
|--------------------------------------------------------------------------
*/

if ($isLocal) {

    if (!defined('ROOT')) define('ROOT', '/donits-IS');
    if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/donits-IS');

} else {
    if (!defined('ROOT')) define('ROOT', getenv('ROOT') ?: '');
    if (!defined('BASE_URL')) define('BASE_URL', getenv('BASE_URL') ?: '');
}


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

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(120) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS capital_expenses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            amount DECIMAL(12,2) NOT NULL,
            note VARCHAR(255) DEFAULT NULL,
            item_id INT UNSIGNED DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

<?php

require_once __DIR__ . '/../includes/functions.php';

if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}




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
    if (!defined('DB_NAME')) define('DB_NAME', 'donits-IS');
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
    define('DISPLAY_ERROR', getenv('DISPLAY_ERROR') ?: '0');
}

if (APP_ENV === 'development' || DISPLAY_ERROR === '1' || DISPLAY_ERROR === 'true') {

    ini_set('display_errors', '1');
    error_reporting(E_ALL);

} else {

    ini_set('display_errors', '0');
    error_reporting(0);

}


if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Asia/Manila');
}

date_default_timezone_set(APP_TIMEZONE);



ini_set('session.cookie_secure', getenv('SESSION_COOKIE_SECURE') === 'true' ? '1' : '0');
ini_set('session.cookie_httponly', getenv('SESSION_COOKIE_HTTPONLY') === 'true' ? '1' : '0');
ini_set('session.cookie_samesite', 'Strict');



if (!defined('APP_VERSION')) {
    define('APP_VERSION', 'ver.2.0.000.000');
}

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

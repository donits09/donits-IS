<?php
require_once __DIR__ . '/../includes/functions.php';

$dbname = getenv('DB_NAME') ?: 'inventory_system';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';

$configuredHosts = getenv('DB_HOSTS') ?: (getenv('DB_HOST') ?: 'localhost,127.0.0.1');
$hosts = array_values(array_filter(array_map('trim', explode(',', $configuredHosts))));

if ($hosts === []) {
    $hosts = ['localhost', '127.0.0.1'];
}

$connectionErrors = [];

foreach ($hosts as $host) {
    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        break;
    } catch (PDOException $exception) {
        $connectionErrors[] = "{$host}: {$exception->getMessage()}";
    }
}

if (!isset($pdo)) {
    throw new RuntimeException(
        'Database connection failed. Tried hosts [' . implode(', ', $hosts) . '] on port ' . $port . '. ' .
        'Set DB_HOST/DB_HOSTS, DB_PORT, DB_USER, DB_PASS, DB_NAME to match your MariaDB setup. ' .
        'Driver errors: ' . implode(' | ', $connectionErrors)
    );
}

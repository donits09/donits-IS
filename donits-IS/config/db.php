<?php
require_once __DIR__ . '/../includes/functions.php';

$pdo = new PDO(
    'mysql:host=localhost;dbname=inventory_system;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

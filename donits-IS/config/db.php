<?php
$pdo = new PDO("mysql:host=localhost;dbname=inventory_system", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
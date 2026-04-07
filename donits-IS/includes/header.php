<?php $flash = get_flash(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= e(app_url('index.php')) ?>">Inventory System</a>
        <div class="navbar-nav">
            <a class="nav-link" href="<?= e(app_url('index.php')) ?>">Dashboard</a>
            <a class="nav-link" href="<?= e(app_url('modules/items/index.php')) ?>">Items</a>
            <a class="nav-link" href="<?= e(app_url('modules/sales/index.php')) ?>">Sales</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

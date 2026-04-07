<?php
require_once __DIR__ . '/functions.php';

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%) !important;
            box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.2);
        }

        .app-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.25rem rgba(15, 23, 42, 0.08);
        }

        .section-title {
            font-weight: 600;
            color: #0f172a;
        }

        .form-label {
            font-weight: 500;
            color: #334155;
        }

        .table thead th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= e(app_url('index.php')) ?>">
            <i class="bi bi-boxes me-2"></i>Inventory System
        </a>
        <div class="navbar-nav ms-auto gap-1">
            <a class="nav-link" href="<?= e(app_url('index.php')) ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
            <a class="nav-link" href="<?= e(app_url('modules/items/index.php')) ?>"><i class="bi bi-archive me-1"></i>Items</a>
            <a class="nav-link" href="<?= e(app_url('modules/sales/index.php')) ?>"><i class="bi bi-receipt-cutoff me-1"></i>Sales</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show app-card" role="alert">
            <i class="bi bi-info-circle me-1"></i><?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

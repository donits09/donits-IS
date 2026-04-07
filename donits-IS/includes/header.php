<?php
require_once __DIR__ . '/functions.php';

$flash = get_flash();
$showAuditAlert = false;
$auditScheduleText = '';

if (isset($pdo) && $pdo instanceof PDO) {
    $auditEnabled = get_setting($pdo, 'audit_enabled', '0') === '1';
    $auditSnoozeUntil = get_setting($pdo, 'audit_snooze_until');
    $auditNextAt = get_setting($pdo, 'audit_next_at');

    $nowTs = time();
    $snoozeTs = $auditSnoozeUntil ? strtotime($auditSnoozeUntil) : false;
    $nextTs = $auditNextAt ? strtotime($auditNextAt) : false;

    if ($auditEnabled && $nextTs !== false && $nextTs <= $nowTs && ($snoozeTs === false || $snoozeTs <= $nowTs)) {
        $showAuditAlert = true;
        $auditScheduleText = date('F j, Y g:i A', $nextTs);
    }
}
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
            <a class="nav-link" href="<?= e(app_url('modules/settings/index.php')) ?>"><i class="bi bi-gear me-1"></i>Settings</a>
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

    <?php if ($showAuditAlert): ?>
        <div class="modal fade" id="auditReminderModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-clipboard2-check me-2"></i>Inventory Physical Audit Reminder</h5>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Your scheduled physical inventory check is due.</p>
                        <p class="mb-0 text-secondary small">Schedule: <?= e($auditScheduleText) ?></p>
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="<?= e(app_url('modules/settings/index.php')) ?>" class="d-inline">
                            <input type="hidden" name="action" value="snooze_audit">
                            <button type="submit" class="btn btn-outline-secondary">Snooze 1 Minute</button>
                        </form>
                        <form method="POST" action="<?= e(app_url('modules/settings/index.php')) ?>" class="d-inline">
                            <input type="hidden" name="action" value="complete_audit">
                            <button type="submit" class="btn btn-primary">Check Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
        window.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('auditReminderModal');
            if (!modalEl || !window.bootstrap) {
                return;
            }
            const modal = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
        });
        </script>
    <?php endif; ?>

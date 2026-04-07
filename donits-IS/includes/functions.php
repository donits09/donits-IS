<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function app_base_url(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if (strpos($scriptName, '/modules/') !== false) {
        return rtrim(explode('/modules/', $scriptName)[0], '/');
    }

    return rtrim(dirname($scriptName), '/');
}

function app_url(string $path = ''): string
{
    $base = app_base_url();
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base === '' ? '/' : $base;
    }

    return ($base === '' ? '' : $base) . '/' . $path;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function redirect(string $path): void
{
    header('Location: ' . app_url($path));
    exit;
}

function format_currency(float $value): string
{
    return '₱' . number_format($value, 2);
}

function calculate_markup_percent(float $costPrice, float $salePrice): float
{
    if ($costPrice <= 0) {
        return 0;
    }

    return (($salePrice - $costPrice) / $costPrice) * 100;
}

function stock_health_label(int $remaining): string
{
    if ($remaining <= 0) {
        return 'Out of stock';
    }

    if ($remaining <= 5) {
        return 'Low stock';
    }

    return 'Healthy';
}

function stock_health_badge_class(int $remaining): string
{
    if ($remaining <= 0) {
        return 'danger';
    }

    if ($remaining <= 5) {
        return 'warning';
    }

    return 'success';
}

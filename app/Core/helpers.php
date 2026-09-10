<?php

use App\Core\Auth;
use App\Core\Csrf;

function app_config(string $key, mixed $default = null): mixed
{
    static $config = null;
    $config ??= require dirname(__DIR__, 2) . '/config/app.php';
    return $config[$key] ?? $default;
}

function url(string $path = ''): string
{
    return rtrim(app_config('base_url'), '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string
{
    return Csrf::field();
}

function active_nav(string $path): string
{
    return str_starts_with($_SERVER['REQUEST_URI'] ?? '', url($path)) ? 'active' : '';
}

function current_user(): ?array
{
    return Auth::user();
}

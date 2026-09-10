<?php

namespace App\Core;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim((require dirname(__DIR__, 2) . '/config/app.php')['base_url'], '/');

        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }

        if (str_starts_with($path, '/public/')) {
            $path = substr($path, 7) ?: '/';
        } elseif ($path === '/public') {
            $path = '/';
        }

        return '/' . trim($path, '/');
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public static function only(array $keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = trim((string)($_POST[$key] ?? ''));
        }
        return $data;
    }
}

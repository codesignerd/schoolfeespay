<?php

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(): void
    {
        $method = Request::method();
        $path = Request::path();
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Page not found']);
            return;
        }

        [$class, $action] = $handler;
        (new $class())->{$action}();
    }

    private function normalize(string $path): string
    {
        return '/' . trim($path, '/');
    }
}

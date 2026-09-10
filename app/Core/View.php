<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__) . "/Views/{$view}.php";

        if (!is_file($viewFile)) {
            http_response_code(500);
            exit("View {$view} not found.");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === '') {
            echo $content;
            return;
        }

        require dirname(__DIR__) . "/Views/layouts/{$layout}.php";
    }
}

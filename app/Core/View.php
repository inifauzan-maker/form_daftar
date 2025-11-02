<?php

namespace App\Core;

class View
{
    public static function make(string $template, array $data = []): string
    {
        $path = __DIR__ . '/../Views/' . $template . '.php';

        if (!file_exists($path)) {
            http_response_code(500);
            return 'View not found.';
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;

        return (string) ob_get_clean();
    }
}


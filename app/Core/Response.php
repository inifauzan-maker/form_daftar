<?php

namespace App\Core;

class Response
{
    public function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    public function view(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        echo View::make($template, $data);
    }

    public function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }
}


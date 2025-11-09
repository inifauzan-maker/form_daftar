<?php

namespace App\Core;

class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        return rtrim($path, '/') ?: '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $source = $this->method() === 'GET' ? $_GET : $_POST;

        if (isset($source[$key])) {
            return is_string($source[$key]) ? trim((string) $source[$key]) : $source[$key];
        }

        return $default;
    }

    public function json(): array
    {
        $payload = file_get_contents('php://input');
        $decoded = json_decode($payload ?? '', true);

        return is_array($decoded) ? $decoded : [];
    }

    public function all(): array
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->method() === 'GET' ? $_GET : $_POST
        );
    }

    public function ip(): string
    {
        $candidates = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($candidates as $key) {
            if (!isset($_SERVER[$key]) || $_SERVER[$key] === '') {
                continue;
            }

            $value = (string) $_SERVER[$key];
            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $value = explode(',', $value)[0] ?? $value;
            }

            $value = trim($value);

            if ($value !== '') {
                return $value;
            }
        }

        return '0.0.0.0';
    }

    public function userAgent(): string
    {
        return trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    }

    public function isJson(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        return str_contains($contentType, 'application/json');
    }
}

<?php

if (!function_exists('env')) {
    /**
     * Retrieve environment variables with optional default values.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        $lower = strtolower($value);

        return match ($lower) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (!function_exists('config')) {
    /**
     * Load configuration arrays from the config directory.
     */
    function config(string $path, mixed $default = null): mixed
    {
        static $cache = [];

        if (isset($cache[$path])) {
            return $cache[$path];
        }

        $segments = explode('.', $path);
        $file = array_shift($segments);
        $configFile = __DIR__ . '/../config/' . $file . '.php';

        if (!file_exists($configFile)) {
            return $default;
        }

        $config = require $configFile;

        foreach ($segments as $segment) {
            if (is_array($config) && array_key_exists($segment, $config)) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }

        $cache[$path] = $config;

        return $config;
    }
}

if (!function_exists('base_path')) {
    /**
     * Detect application base path segment (e.g. /public) for shared hosting setups.
     */
    function base_path(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $directory = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($directory === '' || $directory === '/' || $directory === '\\') {
            return '';
        }

        return $directory;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL honoring the detected base path.
     */
    function asset(string $path): string
    {
        return rtrim(base_path(), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('route_path')) {
    /**
     * Generate a route-relative path honoring the detected base path.
     */
    function route_path(string $path = ''): string
    {
        $normalized = ltrim($path, '/');
        $base = base_path();

        if ($base === '') {
            return '/' . $normalized;
        }

        return $base . '/' . $normalized;
    }
}

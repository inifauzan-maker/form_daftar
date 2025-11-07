<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

require __DIR__ . '/../app/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($path)) {
        require $path;
    }
});

if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$name, $value] = array_map('trim', explode('=', $line, 2));
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => $isSecure,
        'cookie_samesite' => 'Lax',
    ]);
}

date_default_timezone_set((string) config('app.timezone'));

$request = new Request();
$response = new Response();
$router = new Router();

require __DIR__ . '/../routes/web.php';

$router->dispatch($request, $response);

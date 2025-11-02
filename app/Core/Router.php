<?php

namespace App\Core;

use Closure;
use Throwable;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, array|Closure $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array|Closure $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->method();
        $path = $request->path();
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            $response->json(['message' => 'Not Found'], 404);
            return;
        }

        try {
            if ($handler instanceof Closure) {
                $handler($request, $response);
                return;
            }

            [$class, $action] = $handler;
            $controller = new $class($request, $response);
            $controller->{$action}();
        } catch (Throwable $throwable) {
            if (config('app.debug')) {
                throw $throwable;
            }

            $response->json(['message' => 'Internal server error'], 500);
        }
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        return rtrim($path, '/') ?: '/';
    }
}


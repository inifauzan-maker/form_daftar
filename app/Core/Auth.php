<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    private const SESSION_KEY = 'auth_user';

    public static function check(): bool
    {
        self::ensureSessionStarted();

        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function user(): ?array
    {
        self::ensureSessionStarted();

        $user = $_SESSION[self::SESSION_KEY] ?? null;

        return is_array($user) ? $user : null;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user ? (int) $user['id'] : null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || $user['status'] !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        self::login((int) $user['id']);

        return true;
    }

    public static function login(int $userId): void
    {
        $userModel = new User();
        $fresh = $userModel->findWithRelations($userId);
        $fresh['permission_slugs'] = self::permissionsToSlugs($fresh['permissions'] ?? []);

        self::ensureSessionStarted();
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = $fresh;
        $_SESSION['user_id'] = $fresh['id'] ?? $userId;
    }

    public static function logout(): void
    {
        self::ensureSessionStarted();
        unset($_SESSION[self::SESSION_KEY], $_SESSION['user_id']);
        session_regenerate_id(true);
    }

    public static function reload(int $userId): void
    {
        $current = self::user();

        if ($current && (int) $current['id'] === $userId) {
            self::login($userId);
        }
    }

    public static function can(string $permission): bool
    {
        $user = self::user();

        if (!$user) {
            return false;
        }

        if (self::isSuperAdmin($user)) {
            return true;
        }

        $slugs = $user['permission_slugs'] ?? null;

        if (!is_array($slugs)) {
            $slugs = self::permissionsToSlugs($user['permissions'] ?? []);
            $_SESSION[self::SESSION_KEY]['permission_slugs'] = $slugs;
            $user['permission_slugs'] = $slugs;
        }

        return in_array($permission, $slugs, true);
    }

    public static function requirePermission(Request $request, Response $response, string $permission): void
    {
        if (!self::check()) {
            self::deny($request, $response, 401, 'Silakan masuk terlebih dahulu.');
        }

        if (!self::can($permission)) {
            self::deny($request, $response, 403, 'Anda tidak memiliki hak akses yang diperlukan.');
        }
    }

    private static function deny(Request $request, Response $response, int $status, string $message): void
    {
        $expectsJson = $request->isJson()
            || $request->method() === 'POST'
            || str_starts_with($request->path(), '/api');

        if ($expectsJson) {
            $response->json(['message' => $message], $status);
            exit;
        }

        if ($status === 401) {
            $response->redirect(route_path('/login'));
            exit;
        }

        $response->view('errors/forbidden', [
            'message' => $message,
            'status' => $status,
        ], $status);

        exit;
    }

    private static function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private static function permissionsToSlugs(array $permissions): array
    {
        $slugs = [];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $slugs[] = $permission;
                continue;
            }

            if (is_array($permission) && isset($permission['slug'])) {
                $slugs[] = (string) $permission['slug'];
            }
        }

        return array_values(array_unique($slugs));
    }

    private static function isSuperAdmin(array $user): bool
    {
        $roles = $user['roles'] ?? [];

        foreach ($roles as $role) {
            if (is_string($role) && $role === 'admin') {
                return true;
            }

            if (is_array($role) && isset($role['slug']) && $role['slug'] === 'admin') {
                return true;
            }
        }

        return false;
    }
}

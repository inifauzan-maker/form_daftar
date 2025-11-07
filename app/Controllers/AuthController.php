<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class AuthController extends Controller
{
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
    }

    public function showLoginForm(): void
    {
        if (Auth::check()) {
            $this->response->redirect(route_path('/dashboard'));
            return;
        }

        $this->response->view('auth/login', [
            'appName' => config('app.name'),
            'redirect' => $this->sanitizeRedirect((string) $this->request->input('redirect', route_path('/dashboard'))),
        ]);
    }

    public function login(): void
    {
        $payload = $this->request->all();
        $email = isset($payload['email']) ? trim((string) $payload['email']) : '';
        $password = $payload['password'] ?? '';
        $redirect = $this->sanitizeRedirect((string) ($payload['redirect'] ?? route_path('/dashboard')));
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Gunakan alamat email yang valid.';
        }

        if ($password === '') {
            $errors['password'][] = 'Kata sandi wajib diisi.';
        }

        if (!empty($errors)) {
            $this->respondLoginFailure($email, $redirect, $errors);
            return;
        }

        if (!Auth::attempt($email, (string) $password)) {
            $this->respondLoginFailure($email, $redirect, [
                'credentials' => ['Email atau kata sandi tidak sesuai, atau akun tidak aktif.'],
            ]);
            return;
        }

        if ($this->request->isJson()) {
            $this->response->json([
                'message' => 'Berhasil masuk.',
                'redirect' => $redirect ?: route_path('/dashboard'),
            ]);
            return;
        }

        $target = $redirect ?: route_path('/dashboard');
        $this->response->redirect($target);
    }

    public function logout(): void
    {
        Auth::logout();

        if ($this->request->isJson()) {
            $this->response->json(['message' => 'Berhasil keluar.']);
            return;
        }

        $this->response->redirect(route_path('/login'));
    }

    private function respondLoginFailure(string $email, string $redirect, array $errors): void
    {
        if ($this->request->isJson()) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        $this->response->view('auth/login', [
            'appName' => config('app.name'),
            'redirect' => $redirect ?: route_path('/dashboard'),
            'errors' => $errors,
            'email' => $email,
        ], 422);
    }

    private function sanitizeRedirect(string $redirect): string
    {
        $redirect = trim($redirect);

        if ($redirect === '' || str_starts_with($redirect, 'http://') || str_starts_with($redirect, 'https://')) {
            return route_path('/dashboard');
        }

        if (!str_starts_with($redirect, '/')) {
            return route_path('/dashboard');
        }

        return $redirect;
    }
}

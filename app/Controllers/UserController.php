<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ActivityLogger;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use PDOException;

class UserController extends Controller
{
    private User $users;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->users = new User();
    }

    public function index(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');

        $this->response->view('users/index', [
            'appName' => config('app.name'),
            'user' => Auth::user(),
        ]);
    }

    public function list(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');

        $this->response->json([
            'data' => $this->users->allWithRelations(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');

        $payload = $this->normalizePayload();
        $errors = $this->validateUserPayload($payload, true);

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        try {
            $userId = $this->users->create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => password_hash($payload['password'], PASSWORD_DEFAULT),
                'status' => $payload['status'],
            ], $payload['roles'], $payload['permissions']);
        } catch (PDOException $exception) {
            $this->response->json([
                'errors' => $this->duplicateError($exception),
            ], 422);
            return;
        }

        ActivityLogger::log(
            $this->request,
            'users.create',
            'Membuat pengguna baru.',
            [
                'target_user_id' => $userId,
                'target_email' => $payload['email'],
                'status' => $payload['status'],
                'role_ids' => $payload['roles'],
            ]
        );

        $this->response->json([
            'message' => 'Pengguna berhasil dibuat.',
            'id' => $userId,
        ], 201);
    }

    public function update(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');

        $payload = $this->normalizePayload();
        $errors = $this->validateUserPayload($payload, false);

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        $userId = (int) $payload['id'];
        $attributes = [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'status' => $payload['status'],
        ];

        if (!empty($payload['password'])) {
            $attributes['password'] = password_hash($payload['password'], PASSWORD_DEFAULT);
        }

        try {
            $this->users->updateUser(
                $userId,
                $attributes,
                $payload['roles'],
                $payload['permissions']
            );
        } catch (PDOException $exception) {
            $this->response->json([
                'errors' => $this->duplicateError($exception),
            ], 422);
            return;
        }

        Auth::reload($userId);

        ActivityLogger::log(
            $this->request,
            'users.update',
            'Memperbarui data pengguna.',
            [
                'target_user_id' => $userId,
                'target_email' => $payload['email'],
                'status' => $payload['status'],
                'role_ids' => $payload['roles'],
            ]
        );

        $this->response->json(['message' => 'Pengguna berhasil diperbarui.']);
    }

    public function delete(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');

        $payload = $this->request->json();
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;

        if ($id <= 0) {
            $this->response->json(['errors' => ['id' => ['ID pengguna tidak valid.']]], 422);
            return;
        }

        if ($id === Auth::id()) {
            $this->response->json([
                'errors' => [
                    'id' => ['Anda tidak dapat menghapus akun yang sedang digunakan.'],
                ],
            ], 422);
            return;
        }

        $target = $this->users->find($id);
        $this->users->delete($id);

        ActivityLogger::log(
            $this->request,
            'users.delete',
            'Menghapus pengguna.',
            [
                'target_user_id' => $id,
                'target_email' => $target['email'] ?? null,
            ]
        );

        $this->response->json(['message' => 'Pengguna berhasil dihapus.']);
    }

    private function normalizePayload(): array
    {
        $payload = $this->request->isJson() ? $this->request->json() : $this->request->all();

        return [
            'id' => $payload['id'] ?? null,
            'name' => isset($payload['name']) ? trim((string) $payload['name']) : '',
            'email' => isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : '',
            'password' => isset($payload['password']) ? (string) $payload['password'] : '',
            'status' => isset($payload['status']) ? strtolower((string) $payload['status']) : 'active',
            'roles' => $this->normalizeIntegerArray($payload['roles'] ?? []),
            'permissions' => $this->normalizeIntegerArray($payload['permissions'] ?? []),
        ];
    }

    private function normalizeIntegerArray(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_values(array_unique(array_map(static fn ($value) => (int) $value, $values)));
    }

    private function validateUserPayload(array $payload, bool $creating): array
    {
        $errors = [];

        if (!$creating) {
            $id = (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                $errors['id'][] = 'ID pengguna tidak valid.';
            }
        }

        if ($payload['name'] === '') {
            $errors['name'][] = 'Nama lengkap wajib diisi.';
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Gunakan alamat email yang valid.';
        }

        if ($payload['status'] !== 'active' && $payload['status'] !== 'inactive') {
            $errors['status'][] = 'Status harus active atau inactive.';
        }

        if ($creating && strlen($payload['password']) < 6) {
            $errors['password'][] = 'Kata sandi minimal 6 karakter.';
        } elseif (!$creating && $payload['password'] !== '' && strlen($payload['password']) < 6) {
            $errors['password'][] = 'Kata sandi minimal 6 karakter.';
        }

        return $errors;
    }

    private function duplicateError(PDOException $exception): array
    {
        if ($exception->getCode() === '23000') {
            return ['email' => ['Alamat email sudah digunakan.']];
        }

        return ['general' => ['Terjadi kesalahan saat menyimpan pengguna.']];
    }
}

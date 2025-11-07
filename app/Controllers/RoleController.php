<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Role;
use PDOException;

class RoleController extends Controller
{
    private Role $roles;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->roles = new Role();
    }

    public function list(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');

        $this->response->json([
            'data' => $this->roles->allWithPermissions(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_roles');

        $payload = $this->normalizePayload();
        $errors = $this->validatePayload($payload, true);

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        try {
            $roleId = $this->roles->create([
                'name' => $payload['name'],
                'slug' => $payload['slug'],
                'description' => $payload['description'],
            ], $payload['permissions']);
        } catch (PDOException $exception) {
            $this->response->json([
                'errors' => $this->duplicateError($exception),
            ], 422);
            return;
        }

        $this->response->json([
            'message' => 'Peran berhasil dibuat.',
            'id' => $roleId,
        ], 201);
    }

    public function update(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_roles');

        $payload = $this->normalizePayload();
        $errors = $this->validatePayload($payload, false);

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        $role = $this->roles->find((int) $payload['id']);
        if (!$role) {
            $this->response->json([
                'errors' => ['id' => ['Peran tidak ditemukan.']],
            ], 404);
            return;
        }

        if ($this->isProtectedSlug($role['slug']) && $payload['slug'] !== $role['slug']) {
            $this->response->json([
                'errors' => ['slug' => ['Slug peran ini tidak dapat diubah.']],
            ], 422);
            return;
        }

        try {
            $this->roles->updateRole(
                (int) $payload['id'],
                [
                    'name' => $payload['name'],
                    'slug' => $payload['slug'],
                    'description' => $payload['description'],
                ],
                $payload['permissions']
            );
        } catch (PDOException $exception) {
            $this->response->json([
                'errors' => $this->duplicateError($exception),
            ], 422);
            return;
        }

        $this->response->json(['message' => 'Peran berhasil diperbarui.']);
    }

    public function delete(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_roles');

        $payload = $this->request->json();
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;

        if ($id <= 0) {
            $this->response->json(['errors' => ['id' => ['ID peran tidak valid.']]], 422);
            return;
        }

        $role = $this->roles->find($id);
        if (!$role) {
            $this->response->json(['errors' => ['id' => ['Peran tidak ditemukan.']]], 404);
            return;
        }

        if ($this->isProtectedSlug($role['slug'])) {
            $this->response->json([
                'errors' => ['slug' => ['Peran ini tidak dapat dihapus.']],
            ], 422);
            return;
        }

        $this->roles->delete($id);

        $this->response->json(['message' => 'Peran berhasil dihapus.']);
    }

    private function normalizePayload(): array
    {
        $payload = $this->request->isJson() ? $this->request->json() : $this->request->all();

        return [
            'id' => $payload['id'] ?? null,
            'name' => isset($payload['name']) ? trim((string) $payload['name']) : '',
            'slug' => $this->sanitizeSlug($payload['slug'] ?? ''),
            'description' => isset($payload['description']) ? trim((string) $payload['description']) : null,
            'permissions' => $this->normalizeIntegerArray($payload['permissions'] ?? []),
        ];
    }

    private function validatePayload(array $payload, bool $creating): array
    {
        $errors = [];

        if (!$creating) {
            $id = (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                $errors['id'][] = 'ID peran tidak valid.';
            }
        }

        if ($payload['name'] === '') {
            $errors['name'][] = 'Nama peran wajib diisi.';
        }

        if ($payload['slug'] === '') {
            $errors['slug'][] = 'Slug peran wajib diisi.';
        } elseif (!preg_match('/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', $payload['slug'])) {
            $errors['slug'][] = 'Slug hanya boleh berisi huruf kecil, angka, tanda hubung, atau garis bawah.';
        }

        return $errors;
    }

    private function normalizeIntegerArray(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_values(array_unique(array_map(static fn ($value) => (int) $value, $values)));
    }

    private function sanitizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9-_]+/', '-', $slug);
        $slug = preg_replace('/-{2,}/', '-', $slug);

        return trim($slug ?? '', '- ');
    }

    private function duplicateError(PDOException $exception): array
    {
        if ($exception->getCode() === '23000') {
            return ['slug' => ['Slug peran sudah digunakan.']];
        }

        return ['general' => ['Terjadi kesalahan saat menyimpan peran.']];
    }

    private function isProtectedSlug(string $slug): bool
    {
        return in_array($slug, ['admin'], true);
    }
}


<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Permission;
use PDOException;

class PermissionController extends Controller
{
    private Permission $permissions;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->permissions = new Permission();
    }

    public function list(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');

        $this->response->json([
            'data' => $this->permissions->all(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_permissions');

        $payload = $this->normalizePayload();
        $errors = $this->validatePayload($payload, true);

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        try {
            $id = $this->permissions->create([
                'name' => $payload['name'],
                'slug' => $payload['slug'],
                'description' => $payload['description'],
            ]);
        } catch (PDOException $exception) {
            $this->response->json([
                'errors' => $this->duplicateError($exception),
            ], 422);
            return;
        }

        $this->response->json([
            'message' => 'Izin berhasil dibuat.',
            'id' => $id,
        ], 201);
    }

    public function update(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_permissions');

        $payload = $this->normalizePayload();
        $errors = $this->validatePayload($payload, false);

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        $permission = $this->permissions->find((int) $payload['id']);
        if (!$permission) {
            $this->response->json([
                'errors' => ['id' => ['Izin tidak ditemukan.']],
            ], 404);
            return;
        }

        try {
            $this->permissions->updatePermission((int) $payload['id'], [
                'name' => $payload['name'],
                'slug' => $payload['slug'],
                'description' => $payload['description'],
            ]);
        } catch (PDOException $exception) {
            $this->response->json([
                'errors' => $this->duplicateError($exception),
            ], 422);
            return;
        }

        $this->response->json(['message' => 'Izin berhasil diperbarui.']);
    }

    public function delete(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_permissions');

        $payload = $this->request->json();
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;

        if ($id <= 0) {
            $this->response->json(['errors' => ['id' => ['ID izin tidak valid.']]], 422);
            return;
        }

        $permission = $this->permissions->find($id);
        if (!$permission) {
            $this->response->json(['errors' => ['id' => ['Izin tidak ditemukan.']]], 404);
            return;
        }

        $this->permissions->delete($id);

        $this->response->json(['message' => 'Izin berhasil dihapus.']);
    }

    private function normalizePayload(): array
    {
        $payload = $this->request->isJson() ? $this->request->json() : $this->request->all();

        return [
            'id' => $payload['id'] ?? null,
            'name' => isset($payload['name']) ? trim((string) $payload['name']) : '',
            'slug' => $this->sanitizeSlug($payload['slug'] ?? ''),
            'description' => isset($payload['description']) ? trim((string) $payload['description']) : null,
        ];
    }

    private function validatePayload(array $payload, bool $creating): array
    {
        $errors = [];

        if (!$creating) {
            $id = (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                $errors['id'][] = 'ID izin tidak valid.';
            }
        }

        if ($payload['name'] === '') {
            $errors['name'][] = 'Nama izin wajib diisi.';
        }

        if ($payload['slug'] === '') {
            $errors['slug'][] = 'Slug izin wajib diisi.';
        } elseif (!preg_match('/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', $payload['slug'])) {
            $errors['slug'][] = 'Slug hanya boleh berisi huruf kecil, angka, tanda hubung, atau garis bawah.';
        }

        return $errors;
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
            return ['slug' => ['Slug izin sudah digunakan.']];
        }

        return ['general' => ['Terjadi kesalahan saat menyimpan izin.']];
    }
}


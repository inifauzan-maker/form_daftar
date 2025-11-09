<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ActivityLogger;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Program;

class ProgramDetailController extends Controller
{
    private Program $programs;
    private array $categories = ['SD_SMP', 'X_XI', 'XII'];
    private const MAX_DECIMAL_12_2 = 9999999999.99;
    private const MAX_DECIMAL_14_2 = 999999999999.99;
    private const MAX_INT_UNSIGNED = 4294967295;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        Auth::requirePermission($this->request, $this->response, 'view_dashboard');
        $this->programs = new Program();
    }

    public function index(): void
    {
        $user = Auth::user();
        $programs = $this->programs->all();
        $canManage = Auth::can('manage_users');

        $status = isset($_GET['status']) ? (string) $_GET['status'] : null;
        $message = isset($_GET['message']) ? (string) $_GET['message'] : null;

        $this->response->view('programs/index', [
            'appName' => config('app.name'),
            'user' => $user,
            'programs' => $programs,
            'canManagePrograms' => $canManage,
            'flashStatus' => $status,
            'flashMessage' => $message,
            'categories' => $this->categories,
        ]);
    }

    public function save(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');

        $id = (int) ($this->request->input('id') ?? 0);
        $isUpdate = $id > 0;

        $code = strtoupper(trim((string) $this->request->input('code')));
        $name = trim((string) $this->request->input('name'));
        $classCategory = trim((string) $this->request->input('class_category'));
        $registrationFee = $this->sanitizeMoney($this->request->input('registration_fee'));
        $tuitionFee = $this->sanitizeMoney($this->request->input('tuition_fee'));
        $targetStudents = $this->sanitizeInt($this->request->input('target_students'));
        $targetRevenue = $this->sanitizeMoney($this->request->input('target_revenue'));
        $description = trim((string) $this->request->input('description'));

        $errors = [];

        if ($code === '') {
            $errors[] = 'Kode program wajib diisi.';
        }

        if ($name === '') {
            $errors[] = 'Nama program wajib diisi.';
        }

        if (!in_array($classCategory, $this->categories, true)) {
            $errors[] = 'Kategori kelas tidak valid.';
        }

        if ($registrationFee < 0 || $tuitionFee < 0) {
            $errors[] = 'Biaya tidak boleh bernilai negatif.';
        }

        if ($targetStudents < 0 || $targetRevenue < 0) {
            $errors[] = 'Target siswa dan omzet tidak boleh negatif.';
        }

        if ($registrationFee > self::MAX_DECIMAL_12_2) {
            $errors[] = 'Biaya pendaftaran melebihi batas maksimal ' . $this->formatMoney(self::MAX_DECIMAL_12_2) . '.';
        }

        if ($tuitionFee > self::MAX_DECIMAL_12_2) {
            $errors[] = 'Biaya pendidikan melebihi batas maksimal ' . $this->formatMoney(self::MAX_DECIMAL_12_2) . '.';
        }

        if ($targetRevenue > self::MAX_DECIMAL_14_2) {
            $errors[] = 'Target omzet melebihi batas maksimal ' . $this->formatMoney(self::MAX_DECIMAL_14_2) . '.';
        }

        if ($targetStudents > self::MAX_INT_UNSIGNED) {
            $errors[] = 'Target siswa melebihi kapasitas sistem.';
        }

        $existing = $this->programs->findByCode($code);
        if ($existing && (!$isUpdate || $existing['id'] !== $id)) {
            $errors[] = 'Kode program sudah dipakai.';
        }

        if (!empty($errors)) {
            $this->redirectWithMessage('error', implode(' ', $errors));
            return;
        }

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagePath = $this->handleUpload($_FILES['image'], $errors);
            if (!empty($errors)) {
                $this->redirectWithMessage('error', implode(' ', $errors));
                return;
            }
        }

        if ($isUpdate) {
            $record = $this->programs->find($id);
            if (!$record) {
                $this->redirectWithMessage('error', 'Program tidak ditemukan.');
                return;
            }

            if ($imagePath === null) {
                $imagePath = $record['image_path'] ?? null;
            } elseif (!empty($record['image_path'])) {
                $this->deleteImage($record['image_path']);
            }

            $this->programs->update($id, [
                'code' => $code,
                'name' => $name,
                'class_category' => $classCategory,
                'registration_fee' => $registrationFee,
                'tuition_fee' => $tuitionFee,
                'target_students' => $targetStudents,
                'target_revenue' => $targetRevenue,
                'description' => $description,
                'image_path' => $imagePath,
            ]);

            ActivityLogger::log(
                $this->request,
                'programs.update',
                'Memperbarui program bimbel.',
                [
                    'program_id' => $id,
                    'code' => $code,
                    'class_category' => $classCategory,
                ]
            );

            $this->redirectWithMessage('success', 'Program berhasil diperbarui.');
            return;
        }

        $programId = $this->programs->create([
            'code' => $code,
            'name' => $name,
            'class_category' => $classCategory,
            'registration_fee' => $registrationFee,
            'tuition_fee' => $tuitionFee,
            'target_students' => $targetStudents,
            'target_revenue' => $targetRevenue,
            'description' => $description,
            'image_path' => $imagePath,
        ]);

        ActivityLogger::log(
            $this->request,
            'programs.create',
            'Menambahkan program bimbel.',
            [
                'program_id' => $programId,
                'code' => $code,
                'class_category' => $classCategory,
            ]
        );

        $this->redirectWithMessage('success', 'Program berhasil ditambahkan.');
    }

    public function delete(): void
    {
        Auth::requirePermission($this->request, $this->response, 'manage_users');
        $id = (int) ($this->request->input('id') ?? 0);

        if ($id <= 0) {
            $this->redirectWithMessage('error', 'Program tidak ditemukan.');
            return;
        }

        $record = $this->programs->find($id);
        if (!$record) {
            $this->redirectWithMessage('error', 'Program tidak ditemukan.');
            return;
        }

        if (!empty($record['image_path'])) {
            $this->deleteImage($record['image_path']);
        }

        $this->programs->delete($id);

        ActivityLogger::log(
            $this->request,
            'programs.delete',
            'Menghapus program bimbel.',
            [
                'program_id' => $id,
                'code' => $record['code'] ?? null,
            ]
        );

        $this->redirectWithMessage('success', 'Program dihapus.');
    }

    private function sanitizeMoney(mixed $value): float
    {
        $raw = preg_replace('/[^\d,.-]/', '', (string) $value);
        if ($raw === '' || $raw === null) {
            return 0.0;
        }

        $normalized = str_replace('.', '', $raw);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    private function sanitizeInt(mixed $value): int
    {
        return max(0, (int) preg_replace('/[^\d-]/', '', (string) $value));
    }

    private function formatMoney(float $value): string
    {
        return 'Rp' . number_format($value, 0, ',', '.');
    }

    private function handleUpload(array $file, array &$errors): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Gagal mengunggah gambar.';
            return null;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
            return null;
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/programs';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            $errors[] = 'Gagal membuat folder upload.';
            return null;
        }

        $filename = 'program-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $errors[] = 'Gagal menyimpan gambar.';
            return null;
        }

        return 'uploads/programs/' . $filename;
    }

    private function deleteImage(string $relativePath): void
    {
        $fullPath = dirname(__DIR__, 2) . '/public/' . ltrim($relativePath, '/');
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function redirectWithMessage(string $status, string $message): void
    {
        $query = http_build_query([
            'status' => $status,
            'message' => $message,
        ]);

        $this->response->redirect(route_path('/programs') . '?' . $query);
    }
}

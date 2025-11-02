<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Program;
use App\Models\Registration;
use App\Models\School;

class RegistrationController extends Controller
{
    private Registration $registrations;
    private School $schools;
    private Program $programs;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->registrations = new Registration();
        $this->schools = new School();
        $this->programs = new Program();
    }

    public function index(): void
    {
        $this->response->view('registration/form', [
            'appName' => config('app.name'),
        ]);
    }

    public function store(): void
    {
        $payload = $this->request->all();
        $errors = $this->validate($payload);

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        $program = $this->programs->find((int) $payload['program_id']);

        if (!$program) {
            $this->response->json(['errors' => ['program_id' => ['Program tidak ditemukan.']]], 404);
            return;
        }

        $schoolId = isset($payload['school_id']) && $payload['school_id'] !== '' ? (int) $payload['school_id'] : null;
        $schoolName = $payload['school_name'] ?? '';

        if ($schoolId) {
            $school = $this->schools->find($schoolId);

            if (!$school) {
                $this->response->json(['errors' => ['school_id' => ['Sekolah tidak valid.']]], 422);
                return;
            }

            $schoolName = $school['name'];

            if (!$this->classMatchesLevelGroup($payload['class_level'], $school['level_group'])) {
                $this->response->json(['errors' => ['class_level' => ['Jenjang kelas tidak sesuai dengan asal sekolah.']]], 422);
                return;
            }
        }

        if (!$this->classMatchesProgramCategory($payload['class_level'], $program['class_category'])) {
            $this->response->json(['errors' => ['program_id' => ['Program tidak sesuai dengan jenjang kelas.']]], 422);
            return;
        }

        $data = [
            'full_name' => $payload['full_name'],
            'school_id' => $schoolId,
            'school_name' => $schoolName,
            'class_level' => $payload['class_level'],
            'phone_number' => $payload['phone_number'],
            'province' => $payload['province'],
            'city' => $payload['city'],
            'district' => $payload['district'],
            'subdistrict' => $payload['subdistrict'],
            'postal_code' => $payload['postal_code'],
            'address_detail' => $payload['address_detail'] ?? '',
            'program_id' => (int) $payload['program_id'],
            'student_status' => $this->normalizeStudentStatus($payload['student_status'] ?? null),
            'payment_status' => $this->normalizePaymentStatus($payload['payment_status'] ?? null),
            'payment_notes' => $payload['payment_notes'] ?? null,
        ];

        try {
            $id = $this->registrations->create($data);

            $this->response->json([
                'message' => 'Pendaftaran berhasil disimpan.',
                'id' => $id,
            ]);
        } catch (\PDOException $exception) {
            $this->response->json([
                'errors' => [
                    'server' => ['Terjadi kesalahan saat menyimpan data. Pastikan struktur tabel telah diperbarui.'],
                ],
            ], 500);
        }
    }

    private function validate(array $payload): array
    {
        $errors = [];

        if (empty($payload['full_name']) || strlen($payload['full_name']) < 3) {
            $errors['full_name'][] = 'Nama lengkap wajib diisi (min. 3 karakter).';
        }

        if (empty($payload['school_name'])) {
            $errors['school_name'][] = 'Asal sekolah wajib dipilih.';
        }

        if (empty($payload['class_level'])) {
            $errors['class_level'][] = 'Kelas wajib dipilih.';
        } elseif (!in_array($payload['class_level'], $this->allowedClassLevels(), true)) {
            $errors['class_level'][] = 'Kelas tidak valid.';
        }

        if (empty($payload['phone_number']) || !preg_match('/^62\d{9,13}$/', $payload['phone_number'])) {
            $errors['phone_number'][] = 'Nomor HP wajib diawali 62 dan terdiri dari 11-15 digit.';
        }

        foreach (['province', 'city', 'district', 'subdistrict', 'postal_code'] as $field) {
            if (empty($payload[$field])) {
                $errors[$field][] = 'Field alamat wajib diisi.';
            }
        }

        if (empty($payload['program_id'])) {
            $errors['program_id'][] = 'Program bimbel wajib dipilih.';
        }

        if (isset($payload['student_status']) && !in_array($payload['student_status'], $this->studentStatusOptions(), true)) {
            $errors['student_status'][] = 'Status siswa tidak valid.';
        }

        if (isset($payload['payment_status']) && !in_array($payload['payment_status'], $this->paymentStatusOptions(), true)) {
            $errors['payment_status'][] = 'Status pembayaran tidak valid.';
        }

        return $errors;
    }

    private function allowedClassLevels(): array
    {
        return [
            'I', 'II', 'III', 'IV', 'V', 'VI',
            'VII', 'VIII', 'IX',
            'X', 'XI', 'XII',
        ];
    }

    private function classMatchesLevelGroup(string $classLevel, string $group): bool
    {
        return match ($group) {
            'SD' => in_array($classLevel, ['I', 'II', 'III', 'IV', 'V', 'VI'], true),
            'SMP' => in_array($classLevel, ['VII', 'VIII', 'IX'], true),
            'SMA' => in_array($classLevel, ['X', 'XI', 'XII'], true),
            default => false,
        };
    }

    private function classMatchesProgramCategory(string $classLevel, string $category): bool
    {
        return match ($category) {
            'SD_SMP' => in_array($classLevel, ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'], true),
            'X_XI' => in_array($classLevel, ['X', 'XI'], true),
            'XII' => $classLevel === 'XII',
            default => false,
        };
    }

    private function studentStatusOptions(): array
    {
        return ['pending', 'active', 'graduated', 'dropped'];
    }

    private function paymentStatusOptions(): array
    {
        return ['unpaid', 'partial', 'paid'];
    }

    private function normalizeStudentStatus(?string $value): string
    {
        return in_array($value, $this->studentStatusOptions(), true) ? $value : 'pending';
    }

    private function normalizePaymentStatus(?string $value): string
    {
        return in_array($value, $this->paymentStatusOptions(), true) ? $value : 'unpaid';
    }
}

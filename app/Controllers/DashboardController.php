<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Registration;
use DateTimeImmutable;
use DateTimeZone;

class DashboardController extends Controller
{
    private Registration $registrations;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->registrations = new Registration();
    }

    public function index(): void
    {
        $this->response->view('dashboard/index', [
            'appName' => config('app.name'),
        ]);
    }

    public function list(): void
    {
        $records = $this->registrations->all();

        $this->response->json(['data' => $records]);
    }

    public function export(): void
    {
        $records = $this->registrations->all();
        $filename = 'registrations-' . date('Ymd-His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'ID',
            'Nama Lengkap',
            'Asal Sekolah',
            'Kelas',
            'Program',
            'Kode Program',
            'Nomor HP',
            'Provinsi',
            'Kota/Kabupaten',
            'Kecamatan',
            'Kelurahan',
            'Kode Pos',
            'Detail Alamat',
            'Status Siswa',
            'Status Pembayaran',
            'Lokasi Belajar',
            'Nomor Registrasi',
            'Nomor Invoice',
            'Biaya Program (Rp)',
            'Biaya Registrasi (Rp)',
            'Diskon (Rp)',
            'Total Tagihan (Rp)',
            'Jumlah Dibayar (Rp)',
            'Sisa Tagihan (Rp)',
            'Pembayaran Terakhir',
            'Catatan Pembayaran',
            'Dibuat',
            'Diperbarui',
        ]);

        foreach ($records as $row) {
            fputcsv($handle, [
                $row['id'] ?? '',
                $row['full_name'] ?? '',
                $row['school_name'] ?? '',
                $row['class_level'] ?? '',
                $row['program_name'] ?? '',
                $row['program_code'] ?? '',
                $row['phone_number'] ?? '',
                $row['province'] ?? '',
                $row['city'] ?? '',
                $row['district'] ?? '',
                $row['subdistrict'] ?? '',
                $row['postal_code'] ?? '',
                $row['address_detail'] ?? '',
                $row['student_status'] ?? '',
                $row['payment_status'] ?? '',
                $row['study_location'] ?? '',
                $row['registration_number'] ?? '',
                $row['invoice_number'] ?? '',
                $row['program_fee'] ?? '',
                $row['registration_fee'] ?? '',
                $row['discount_amount'] ?? '',
                $row['total_due'] ?? '',
                $row['amount_paid'] ?? '',
                $row['balance_due'] ?? '',
                $row['last_payment_at'] ?? '',
                $row['payment_notes'] ?? '',
                $row['created_at'] ?? '',
                $row['updated_at'] ?? '',
            ]);
        }

        fclose($handle);
    }

    public function updateStatus(): void
    {
        $payload = $this->request->json();
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;
        $studentStatus = (string) ($payload['student_status'] ?? '');
        $paymentStatus = (string) ($payload['payment_status'] ?? '');
        $paymentNotes = isset($payload['payment_notes']) ? trim((string) $payload['payment_notes']) : null;
        $programFee = $payload['program_fee'] ?? 0;
        $registrationFee = $payload['registration_fee'] ?? 0;
        $discountAmount = $payload['discount_amount'] ?? 0;
        $amountPaid = $payload['amount_paid'] ?? 0;
        $lastPaymentAt = $payload['last_payment_at'] ?? null;
        $studyLocation = $this->normalizeLocation($payload['study_location'] ?? null);
        $registrationNumber = isset($payload['registration_number']) ? trim((string) $payload['registration_number']) : null;
        $invoiceNumber = isset($payload['invoice_number']) ? trim((string) $payload['invoice_number']) : null;

        $errors = [];

        if ($id <= 0) {
            $errors['id'][] = 'ID registrasi tidak valid.';
        }

        if (!in_array($studentStatus, $this->studentStatusOptions(), true)) {
            $errors['student_status'][] = 'Status siswa tidak valid.';
        }

        if (!in_array($paymentStatus, $this->paymentStatusOptions(), true)) {
            $errors['payment_status'][] = 'Status pembayaran tidak valid.';
        }

        foreach ([
            'program_fee' => $programFee,
            'registration_fee' => $registrationFee,
            'discount_amount' => $discountAmount,
            'amount_paid' => $amountPaid,
        ] as $field => $value) {
            if (!is_numeric($value) || (float) $value < 0) {
                $errors[$field][] = 'Nilai harus berupa angka positif.';
            }
        }

        if ($lastPaymentAt) {
            $date = \DateTime::createFromFormat('Y-m-d', $lastPaymentAt);
            if (!$date || $date->format('Y-m-d') !== $lastPaymentAt) {
                $errors['last_payment_at'][] = 'Tanggal pembayaran tidak valid.';
            }
        }

        if (!$studyLocation) {
            $errors['study_location'][] = 'Lokasi belajar tidak valid.';
        }

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        $programFee = $this->toDecimal($programFee);
        $registrationFee = $this->toDecimal($registrationFee);
        $discountAmount = $this->toDecimal($discountAmount);
        $totalDue = $this->toDecimal(max(0, $programFee + $registrationFee - $discountAmount));
        $amountPaid = $this->toDecimal($amountPaid);
        $balanceDue = $this->toDecimal(max(0, $totalDue - $amountPaid));

        $codes = $this->locationCodes();
        $locationCode = $codes[$studyLocation] ?? '00';

        [$registrationNumber, $invoiceNumber] = $this->resolveRegistrationNumbers(
            $registrationNumber ?: null,
            $invoiceNumber ?: null,
            $locationCode
        );

        $this->registrations->updateStatus($id, [
            'student_status' => $studentStatus,
            'payment_status' => $paymentStatus,
            'payment_notes' => $paymentNotes,
            'program_fee' => $programFee,
            'registration_fee' => $registrationFee,
            'discount_amount' => $discountAmount,
            'total_due' => $totalDue,
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'last_payment_at' => $lastPaymentAt,
            'study_location' => $studyLocation,
            'registration_number' => $registrationNumber,
            'invoice_number' => $invoiceNumber,
        ]);

        $this->response->json([
            'message' => 'Status pendaftaran berhasil diperbarui.',
        ]);
    }

    private function studentStatusOptions(): array
    {
        return ['pending', 'active', 'graduated', 'dropped'];
    }

    private function paymentStatusOptions(): array
    {
        return ['unpaid', 'partial', 'paid'];
    }

    private function locationCodes(): array
    {
        return [
            'Bandung' => '11',
            'Jaksel' => '21',
            'Jaktim' => '31',
        ];
    }

    private function normalizeLocation(?string $value): ?string
    {
        $locations = array_keys($this->locationCodes());

        return in_array($value, $locations, true) ? $value : null;
    }

    private function resolveRegistrationNumbers(?string $registrationNumber, ?string $invoiceNumber, string $locationCode): array
    {
        $yearSegment = $this->academicYearSegment();
        $pattern = sprintf('/^%s-%s\d{3}$/', $yearSegment, $locationCode);

        if ($registrationNumber && preg_match($pattern, $registrationNumber)) {
            $invoiceNumber = $registrationNumber;
            return [$registrationNumber, $invoiceNumber];
        }

        if ($invoiceNumber && preg_match($pattern, $invoiceNumber)) {
            return [$invoiceNumber, $invoiceNumber];
        }

        $sequence = $this->registrations->nextSequence($yearSegment, $locationCode);
        $registrationNumber = sprintf('%s-%s%03d', $yearSegment, $locationCode, $sequence);

        return [$registrationNumber, $registrationNumber];
    }

    private function academicYearSegment(): string
    {
        $timezone = config('app.timezone') ?? 'Asia/Jakarta';
        $now = new DateTimeImmutable('now', new DateTimeZone($timezone));
        $year = (int) $now->format('Y');
        $month = (int) $now->format('n');

        $startYear = $month >= 7 ? $year : $year - 1;
        $endYear = $startYear + 1;

        return sprintf('%02d%02d', $startYear % 100, $endYear % 100);
    }

    private function toDecimal(mixed $value): float
    {
        return (float) number_format((float) $value, 2, '.', '');
    }
}


<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Registration;
use DateTimeImmutable;
use DateTimeZone;

class DashboardController extends Controller
{
    private const BRANCH_LABELS = [
        'Bandung' => 'Bandung',
        'Jaksel' => 'Jakarta Selatan',
        'Jaktim' => 'Jakarta Timur',
    ];

    private Registration $registrations;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        Auth::requirePermission($this->request, $this->response, 'view_dashboard');
        $this->registrations = new Registration();
    }

    public function index(): void
    {
        $this->response->view('dashboard/index', [
            'appName' => config('app.name'),
            'user' => Auth::user(),
        ]);
    }

    public function metrics(): void
    {
        $filters = $this->resolveDashboardFilters();

        $years = $this->registrations->availableYears();
        $activeYear = $filters['year'] ?? ($years[0] ?? $this->currentYear());
        if ($activeYear) {
            $filters['year'] = (int) $activeYear;
        }

        $summary = $this->registrations->dashboardSummary($filters);
        $limitMonth = $filters['month'] ?? null;
        $monthly = $this->registrations->dashboardMonthly($filters, true, $limitMonth);
        $yearly = $this->registrations->dashboardYearly($filters, true);
        $branchBreakdown = $this->registrations->dashboardByBranch($filters);
        $programBreakdown = $this->registrations->dashboardByProgram($filters);

        $programs = $this->registrations->availablePrograms();
        $targets = $this->resolveTargets((int) $activeYear);

        $this->response->json([
            'filters' => [
                'selected' => [
                    'year' => $filters['year'],
                    'month' => $filters['month'],
                    'program_id' => $filters['program_id'],
                    'branch' => $filters['branch'],
                ],
                'options' => [
                    'years' => $years,
                    'months' => $this->monthOptions(),
                    'programs' => $this->formatProgramOptions($programs),
                    'branches' => $this->branchOptions(),
                ],
            ],
            'summary' => $this->transformSummary($summary, $targets),
            'monthly' => $monthly,
            'yearly' => $yearly,
            'by_branch' => $this->transformBranchBreakdown($branchBreakdown),
            'by_program' => $this->transformProgramBreakdown($programBreakdown),
            'forecast' => $this->forecastNextYear($yearly),
            'targets' => $targets,
        ]);
    }

    public function geography(): void
    {
        $province = $this->sanitizeLocationInput($this->request->input('province'));
        $city = $this->sanitizeLocationInput($this->request->input('city'));

        $rows = $this->registrations->locationSummary($province, $city);
        $markers = array_map(function (array $row): array {
            $coordinates = $this->coordinatesForLocation($row['province'] ?? null, $row['city'] ?? null);

            return [
                'province' => $row['province'] ?? null,
                'city' => $row['city'] ?? null,
                'total' => $row['total'] ?? 0,
                'paid' => $row['paid'] ?? 0,
                'partial' => $row['partial'] ?? 0,
                'unpaid' => $row['unpaid'] ?? 0,
                'coordinates' => $coordinates,
            ];
        }, $rows);

        $provinces = $this->registrations->distinctProvinces();
        $cities = $this->registrations->distinctCities($province);

        $this->response->json([
            'filters' => [
                'selected' => [
                    'province' => $province,
                    'city' => $city,
                ],
                'options' => [
                    'provinces' => $provinces,
                    'cities' => $cities,
                ],
            ],
            'markers' => $markers,
        ]);
    }

    private function resolveDashboardFilters(): array
    {
        $year = $this->positiveIntOrNull($this->request->input('year'));
        $month = $this->positiveIntOrNull($this->request->input('month'));
        if ($month !== null && ($month < 1 || $month > 12)) {
            $month = null;
        }

        $programId = $this->positiveIntOrNull($this->request->input('program_id'));
        $branchInput = $this->request->input('branch');

        return [
            'year' => $year,
            'month' => $month,
            'program_id' => $programId,
            'branch' => $branchInput ? $this->normalizeLocation($branchInput) : null,
        ];
    }

    private function monthOptions(): array
    {
        $labels = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $options = [];
        foreach ($labels as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    private function branchOptions(): array
    {
        $options = [];
        foreach (self::BRANCH_LABELS as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    private function formatProgramOptions(array $programs): array
    {
        return array_map(static function (array $program): array {
            $name = $program['name'] ?? '';
            $code = $program['code'] ?? '';
            $label = $name;
            if ($code !== '') {
                $label .= ' (' . $code . ')';
            }

            return [
                'value' => (int) ($program['id'] ?? 0),
                'label' => trim($label) ?: 'Program Tanpa Nama',
            ];
        }, $programs);
    }

    private function transformSummary(array $summary, array $targets): array
    {
        $students = max(0, (int) ($summary['total_students'] ?? 0));
        $paidStudents = max(0, (int) ($summary['total_paid_students'] ?? 0));
        $revenue = max(0.0, (float) ($summary['total_revenue'] ?? 0));
        $expected = max(0.0, (float) ($summary['total_expected'] ?? 0));
        $discount = max(0.0, (float) ($summary['total_discount'] ?? 0));

        $studentTarget = max(0, (int) ($targets['students'] ?? 0));
        $revenueTarget = max(0.0, (float) ($targets['revenue'] ?? 0));

        return [
            'students' => [
                'total' => $students,
                'paid' => $paidStudents,
                'target' => $studentTarget,
                'progress' => $studentTarget > 0 ? min(1.0, $students / $studentTarget) : null,
            ],
            'revenue' => [
                'actual' => $revenue,
                'expected' => $expected,
                'discount' => $discount,
                'target' => $revenueTarget,
                'progress' => $revenueTarget > 0 ? min(1.0, $revenue / $revenueTarget) : null,
                'average_per_student' => $students > 0 ? $revenue / $students : 0.0,
            ],
        ];
    }

    private function transformBranchBreakdown(array $rows): array
    {
        $defaults = [];
        foreach (self::BRANCH_LABELS as $code => $label) {
            $defaults[$code] = [
                'branch' => $code,
                'label' => $label,
                'students' => 0,
                'revenue' => 0.0,
            ];
        }

        foreach ($rows as $row) {
            $code = $row['branch'] ?? null;
            if ($code === null || !array_key_exists($code, $defaults)) {
                continue;
            }

            $defaults[$code]['students'] = max(0, (int) ($row['students'] ?? 0));
            $defaults[$code]['revenue'] = max(0.0, (float) ($row['revenue'] ?? 0));
        }

        return array_values($defaults);
    }

    private function transformProgramBreakdown(array $rows): array
    {
        return array_map(static function (array $row): array {
            $label = $row['program_name'] ?? '';
            $code = $row['program_code'] ?? '';
            if ($code !== '') {
                $label .= ' (' . $code . ')';
            }

            return [
                'program_id' => (int) ($row['program_id'] ?? 0),
                'label' => trim($label) ?: 'Program Tanpa Nama',
                'students' => max(0, (int) ($row['students'] ?? 0)),
                'revenue' => max(0.0, (float) ($row['revenue'] ?? 0)),
            ];
        }, $rows);
    }

    private function forecastNextYear(array $yearly): array
    {
        if (empty($yearly)) {
            $nextYear = $this->currentYear() + 1;

            return [
                'year' => $nextYear,
                'students' => [
                    'current' => 0,
                    'projected' => 0,
                    'growth_rate' => 0.0,
                ],
                'revenue' => [
                    'current' => 0.0,
                    'projected' => 0.0,
                    'growth_rate' => 0.0,
                ],
            ];
        }

        usort($yearly, static fn ($a, $b) => ($a['year'] ?? 0) <=> ($b['year'] ?? 0));
        $last = $yearly[count($yearly) - 1];

        $studentRates = [];
        $revenueRates = [];

        for ($i = 1, $max = count($yearly); $i < $max; $i++) {
            $previous = $yearly[$i - 1];
            $current = $yearly[$i];

            if (($previous['students'] ?? 0) > 0) {
                $studentRates[] = (($current['students'] ?? 0) - ($previous['students'] ?? 0)) / $previous['students'];
            }
            if (($previous['revenue'] ?? 0) > 0) {
                $revenueRates[] = (($current['revenue'] ?? 0) - ($previous['revenue'] ?? 0)) / $previous['revenue'];
            }
        }

        $studentGrowth = $this->averageGrowth($studentRates);
        $revenueGrowth = $this->averageGrowth($revenueRates);

        $currentStudents = max(0, (int) ($last['students'] ?? 0));
        $currentRevenue = max(0.0, (float) ($last['revenue'] ?? 0));

        $projectedStudents = (int) round(max(0, $currentStudents * (1 + $studentGrowth)));
        $projectedRevenue = max(0.0, $currentRevenue * (1 + $revenueGrowth));

        return [
            'year' => (int) ($last['year'] ?? $this->currentYear()) + 1,
            'students' => [
                'current' => $currentStudents,
                'projected' => $projectedStudents,
                'growth_rate' => $studentGrowth,
            ],
            'revenue' => [
                'current' => $currentRevenue,
                'projected' => $projectedRevenue,
                'growth_rate' => $revenueGrowth,
            ],
        ];
    }

    private function averageGrowth(array $rates): float
    {
        $filtered = array_filter($rates, static fn ($rate) => is_numeric($rate));

        if (empty($filtered)) {
            return 0.0;
        }

        return array_sum($filtered) / count($filtered);
    }

    private function resolveTargets(int $year): array
    {
        $studentDefault = (int) (config('dashboard.targets.students.default') ?? 0);
        $studentByYear = config('dashboard.targets.students.per_year', []);
        if (is_array($studentByYear) && isset($studentByYear[$year])) {
            $studentDefault = (int) $studentByYear[$year];
        }

        $revenueDefault = (float) (config('dashboard.targets.revenue.default') ?? 0);
        $revenueByYear = config('dashboard.targets.revenue.per_year', []);
        if (is_array($revenueByYear) && isset($revenueByYear[$year])) {
            $revenueDefault = (float) $revenueByYear[$year];
        }

        return [
            'year' => $year,
            'students' => max(0, $studentDefault),
            'revenue' => max(0.0, $revenueDefault),
        ];
    }

    private function currentYear(): int
    {
        $timezone = config('app.timezone') ?? 'Asia/Jakarta';
        $now = new DateTimeImmutable('now', new DateTimeZone($timezone));

        return (int) $now->format('Y');
    }

    private function positiveIntOrNull(mixed $value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        return $intValue > 0 ? $intValue : null;
    }

    private function coordinatesForLocation(?string $province, ?string $city): array
    {
        $config = config('locations', []);
        $cities = $config['cities'] ?? [];
        $provinces = $config['provinces'] ?? [];
        $default = $config['default'] ?? ['lat' => -2.5489, 'lng' => 118.0149];

        if ($city && isset($cities[$city])) {
            return [
                'lat' => (float) $cities[$city]['lat'],
                'lng' => (float) $cities[$city]['lng'],
            ];
        }

        if ($province && isset($provinces[$province])) {
            return [
                'lat' => (float) $provinces[$province]['lat'],
                'lng' => (float) $provinces[$province]['lng'],
            ];
        }

        return [
            'lat' => (float) ($default['lat'] ?? -2.5489),
            'lng' => (float) ($default['lng'] ?? 118.0149),
        ];
    }

    private function sanitizeLocationInput(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    public function list(): void
    {
        $records = $this->registrations->all();

        $this->response->json(['data' => $records]);
    }

    public function export(): void
    {
        Auth::requirePermission($this->request, $this->response, 'export_registrations');
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
        Auth::requirePermission($this->request, $this->response, 'update_registration_status');
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

<?php

declare(strict_types=1);

// php database/seeders/seed_registrations.php [--force] [--year=2025]

const BASE_PATH = __DIR__ . '/../../';

require BASE_PATH . 'app/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = BASE_PATH . 'app/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($path)) {
        require $path;
    }
});

if (file_exists(BASE_PATH . '.env')) {
    $lines = file(BASE_PATH . '.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$name, $value] = array_map('trim', explode('=', $line, 2));
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

date_default_timezone_set((string) config('app.timezone', 'Asia/Jakarta'));

$force = in_array('--force', $argv, true);
$yearOption = array_values(array_filter($argv, static fn (string $arg) => str_starts_with($arg, '--year=')));
$targetYear = $yearOption ? (int) substr($yearOption[0], strlen('--year=')) : (int) date('Y');
$targetYear = $targetYear > 0 ? $targetYear : (int) date('Y');

$connection = App\Core\Database::connection();

$count = (int) $connection->query('SELECT COUNT(*) FROM registrations')->fetchColumn();

if ($count > 0 && !$force) {
    fwrite(STDOUT, "Registrations table already contains {$count} rows.\n");
    fwrite(STDOUT, "Run with --force to truncate the table before seeding.\n");
    exit(0);
}

try {
    if ($force) {
        $connection->exec('DELETE FROM registrations');
    }

    $connection->beginTransaction();

    $monthlyPlan = [
        1 => ['students' => 6, 'base_fee' => 3_900_000, 'variance' => 250_000],
        2 => ['students' => 8, 'base_fee' => 4_100_000, 'variance' => 275_000],
        3 => ['students' => 10, 'base_fee' => 4_250_000, 'variance' => 300_000],
        4 => ['students' => 12, 'base_fee' => 4_400_000, 'variance' => 325_000],
        5 => ['students' => 14, 'base_fee' => 4_550_000, 'variance' => 350_000],
        6 => ['students' => 16, 'base_fee' => 4_700_000, 'variance' => 375_000],
        7 => ['students' => 18, 'base_fee' => 4_850_000, 'variance' => 400_000],
        8 => ['students' => 20, 'base_fee' => 5_000_000, 'variance' => 425_000],
        9 => ['students' => 22, 'base_fee' => 5_100_000, 'variance' => 450_000],
        10 => ['students' => 24, 'base_fee' => 5_200_000, 'variance' => 475_000],
        11 => ['students' => 26, 'base_fee' => 5_350_000, 'variance' => 500_000],
        12 => ['students' => 28, 'base_fee' => 5_500_000, 'variance' => 525_000],
    ];

    $locations = ['Bandung', 'Jaksel', 'Jaktim'];
    $programIds = array_map(
        static fn ($row) => (int) $row['id'],
        $connection->query('SELECT id FROM programs ORDER BY id ASC')->fetchAll()
    );

    if (empty($programIds)) {
        throw new RuntimeException('Program data is required before seeding registrations.');
    }

    $statement = $connection->prepare(
        'INSERT INTO registrations (
            full_name,
            school_id,
            school_name,
            class_level,
            phone_number,
            province,
            city,
            district,
            subdistrict,
            postal_code,
            address_detail,
            program_id,
            student_status,
            payment_status,
            payment_notes,
            program_fee,
            registration_fee,
            discount_amount,
            total_due,
            amount_paid,
            balance_due,
            last_payment_at,
            study_location,
            registration_number,
            invoice_number,
            created_at,
            updated_at
        ) VALUES (
            :full_name,
            NULL,
            :school_name,
            :class_level,
            :phone_number,
            :province,
            :city,
            :district,
            :subdistrict,
            :postal_code,
            :address_detail,
            :program_id,
            :student_status,
            :payment_status,
            :payment_notes,
            :program_fee,
            :registration_fee,
            :discount_amount,
            :total_due,
            :amount_paid,
            :balance_due,
            :last_payment_at,
            :study_location,
            :registration_number,
            :invoice_number,
            :created_at,
            :updated_at
        )'
    );

    $studentCounter = 1;
    $now = new DateTimeImmutable();

    foreach ($monthlyPlan as $month => $config) {
        $students = $config['students'];
        $baseFee = $config['base_fee'];
        $variance = $config['variance'];

        for ($i = 0; $i < $students; $i++, $studentCounter++) {
            $programId = $programIds[$studentCounter % count($programIds)];
            $location = $locations[$studentCounter % count($locations)];
            $day = min(25, 5 + ($studentCounter % 20));
            $createdAt = new DateTimeImmutable(sprintf('%04d-%02d-%02d 10:15:00', $targetYear, $month, $day));

            $programFee = $baseFee + random_int(-$variance, $variance);
            $registrationFee = 500_000;
            $discount = max(0, random_int(0, 150_000));
            $totalDue = $programFee + $registrationFee - $discount;
            $paidPortion = random_int((int) ($totalDue * 0.7), (int) ($totalDue));
            $balance = max(0, $totalDue - $paidPortion);
            $paymentStatus = $balance > 0 ? 'partial' : 'paid';

            $params = [
                'full_name' => sprintf('Siswa Dummy %02d-%02d', $month, $studentCounter),
                'school_name' => sprintf('SMA Negeri %d', 1 + ($studentCounter % 10)),
                'class_level' => ['X', 'XI', 'XII'][($studentCounter % 3)],
                'phone_number' => '08' . str_pad((string) random_int(1_000_000_000, 9_999_999_999), 10, '0', STR_PAD_LEFT),
                'province' => $location === 'Bandung' ? 'Jawa Barat' : 'DKI Jakarta',
                'city' => $location === 'Bandung' ? 'Kota Bandung' : ($location === 'Jaksel' ? 'Kota Jakarta Selatan' : 'Kota Jakarta Timur'),
                'district' => 'Kecamatan ' . chr(65 + ($studentCounter % 5)),
                'subdistrict' => 'Kelurahan ' . chr(75 + ($studentCounter % 5)),
                'postal_code' => str_pad((string) random_int(15110, 40290), 5, '0', STR_PAD_LEFT),
                'address_detail' => 'Jl. Pendidikan No. ' . random_int(1, 150),
                'program_id' => $programId,
                'student_status' => 'active',
                'payment_status' => $paymentStatus,
                'payment_notes' => $paymentStatus === 'partial' ? 'Menunggu pelunasan.' : null,
                'program_fee' => number_format($programFee, 2, '.', ''),
                'registration_fee' => number_format($registrationFee, 2, '.', ''),
                'discount_amount' => number_format($discount, 2, '.', ''),
                'total_due' => number_format($totalDue, 2, '.', ''),
                'amount_paid' => number_format($paidPortion, 2, '.', ''),
                'balance_due' => number_format($balance, 2, '.', ''),
                'last_payment_at' => $paymentStatus === 'paid' ? $createdAt->format('Y-m-d') : null,
                'study_location' => $location,
                'registration_number' => sprintf('%02d%02d-%s%03d', $targetYear % 100, ($targetYear + 1) % 100, $location === 'Bandung' ? '11' : ($location === 'Jaksel' ? '21' : '31'), $studentCounter),
                'invoice_number' => sprintf('INV-%04d-%02d-%04d', $targetYear, $month, $studentCounter),
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->modify('+' . random_int(1, 20) . ' days')->format('Y-m-d H:i:s'),
            ];

            $statement->execute($params);
        }
    }

    if ($connection->inTransaction()) {
        $connection->commit();
    }

    $totalInserted = (int) $connection->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
    $summary = sprintf(
        "Seeded %d registrations spanning year %d.\n",
        $totalInserted,
        $targetYear
    );
    fwrite(STDOUT, $summary);
} catch (Throwable $exception) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    fwrite(STDERR, 'Seeding failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

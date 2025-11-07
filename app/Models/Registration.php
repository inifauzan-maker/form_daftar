<?php

namespace App\Models;

use PDOException;
use PDO;

class Registration extends Model
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO registrations
                (full_name, school_id, school_name, class_level, phone_number, province, city, district, subdistrict, postal_code, address_detail, program_id, student_status, payment_status, payment_notes, program_fee, registration_fee, discount_amount, total_due, amount_paid, balance_due, last_payment_at, study_location, registration_number, invoice_number)
                VALUES
                (:full_name, :school_id, :school_name, :class_level, :phone_number, :province, :city, :district, :subdistrict, :postal_code, :address_detail, :program_id, :student_status, :payment_status, :payment_notes, :program_fee, :registration_fee, :discount_amount, :total_due, :amount_paid, :balance_due, :last_payment_at, :study_location, :registration_number, :invoice_number)';

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':full_name', $data['full_name']);
        $statement->bindValue(':school_id', $data['school_id'], $data['school_id'] ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
        $statement->bindValue(':school_name', $data['school_name']);
        $statement->bindValue(':class_level', $data['class_level']);
        $statement->bindValue(':phone_number', $data['phone_number']);
        $statement->bindValue(':province', $data['province']);
        $statement->bindValue(':city', $data['city']);
        $statement->bindValue(':district', $data['district']);
        $statement->bindValue(':subdistrict', $data['subdistrict']);
        $statement->bindValue(':postal_code', $data['postal_code']);
        $statement->bindValue(':address_detail', $data['address_detail']);
        $statement->bindValue(':program_id', $data['program_id'], \PDO::PARAM_INT);
        $statement->bindValue(':student_status', $data['student_status'] ?? 'pending');
        $statement->bindValue(':payment_status', $data['payment_status'] ?? 'unpaid');
        $statement->bindValue(':payment_notes', $data['payment_notes'] ?? null);
        $statement->bindValue(':program_fee', $this->toDecimal($data['program_fee'] ?? 0));
        $statement->bindValue(':registration_fee', $this->toDecimal($data['registration_fee'] ?? 0));
        $statement->bindValue(':discount_amount', $this->toDecimal($data['discount_amount'] ?? 0));
        $statement->bindValue(':total_due', $this->toDecimal($data['total_due'] ?? 0));
        $statement->bindValue(':amount_paid', $this->toDecimal($data['amount_paid'] ?? 0));
        $statement->bindValue(':balance_due', $this->toDecimal($data['balance_due'] ?? 0));
        if (!empty($data['last_payment_at'])) {
            $statement->bindValue(':last_payment_at', $data['last_payment_at']);
        } else {
            $statement->bindValue(':last_payment_at', null, \PDO::PARAM_NULL);
        }
        $statement->bindValue(':study_location', $data['study_location'] ?? null);
        $statement->bindValue(':registration_number', $data['registration_number'] ?? null);
        $statement->bindValue(':invoice_number', $data['invoice_number'] ?? null);

        try {
            $statement->execute();
        } catch (PDOException $exception) {
            throw $exception;
        }

        return (int) $this->connection->lastInsertId();
    }

    public function nextSequence(string $yearSegment, string $locationCode): int
    {
        $prefix = sprintf('%s-%s', $yearSegment, $locationCode);
        $sql = 'SELECT registration_number
                FROM registrations
                WHERE registration_number LIKE :prefix
                ORDER BY registration_number DESC
                LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':prefix', $prefix . '%');
        $statement->execute();

        $latest = $statement->fetchColumn();

        if (!$latest) {
            return 1;
        }

        $sequence = substr($latest, -3);

        return (int) $sequence + 1;
    }

    public function all(): array
    {
        $sql = 'SELECT r.id, r.full_name, r.school_name, r.class_level, r.phone_number,
                       r.province, r.city, r.district, r.subdistrict, r.postal_code,
                       r.address_detail, r.student_status, r.payment_status, r.payment_notes,
                       r.program_fee, r.registration_fee, r.discount_amount, r.total_due,
                       r.amount_paid, r.balance_due, r.last_payment_at,
                       r.study_location, r.registration_number, r.invoice_number,
                       r.created_at, r.updated_at,
                       p.name AS program_name, p.code AS program_code
                FROM registrations r
                INNER JOIN programs p ON p.id = r.program_id
                ORDER BY r.created_at DESC';

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findWithProgram(int $id): ?array
    {
        $sql = 'SELECT r.id, r.full_name, r.school_name, r.class_level, r.phone_number,
                       r.province, r.city, r.district, r.subdistrict, r.postal_code,
                       r.address_detail, r.student_status, r.payment_status, r.payment_notes,
                       r.program_fee, r.registration_fee, r.discount_amount, r.total_due,
                       r.amount_paid, r.balance_due, r.last_payment_at,
                       r.study_location, r.registration_number, r.invoice_number,
                       r.created_at, r.updated_at,
                       p.name AS program_name, p.code AS program_code
                FROM registrations r
                INNER JOIN programs p ON p.id = r.program_id
                WHERE r.id = :id
                LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        $record = $statement->fetch();

        return $record ?: null;
    }

    public function updateStatus(int $id, array $attributes): bool
    {
        $sql = 'UPDATE registrations
                SET student_status = :student_status,
                    payment_status = :payment_status,
                    payment_notes = :payment_notes,
                    program_fee = :program_fee,
                    registration_fee = :registration_fee,
                    discount_amount = :discount_amount,
                    total_due = :total_due,
                    amount_paid = :amount_paid,
                    balance_due = :balance_due,
                    last_payment_at = :last_payment_at,
                    study_location = :study_location,
                    registration_number = :registration_number,
                    invoice_number = :invoice_number
                WHERE id = :id';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->bindValue(':student_status', $attributes['student_status']);
        $statement->bindValue(':payment_status', $attributes['payment_status']);
        $statement->bindValue(':payment_notes', $attributes['payment_notes']);
        $statement->bindValue(':program_fee', $this->toDecimal($attributes['program_fee']));
        $statement->bindValue(':registration_fee', $this->toDecimal($attributes['registration_fee']));
        $statement->bindValue(':discount_amount', $this->toDecimal($attributes['discount_amount']));
        $statement->bindValue(':total_due', $this->toDecimal($attributes['total_due']));
        $statement->bindValue(':amount_paid', $this->toDecimal($attributes['amount_paid']));
        $statement->bindValue(':balance_due', $this->toDecimal($attributes['balance_due']));
        if (!empty($attributes['last_payment_at'])) {
            $statement->bindValue(':last_payment_at', $attributes['last_payment_at']);
        } else {
            $statement->bindValue(':last_payment_at', null, \PDO::PARAM_NULL);
        }
        $statement->bindValue(':study_location', $attributes['study_location']);
        $statement->bindValue(':registration_number', $attributes['registration_number']);
        $statement->bindValue(':invoice_number', $attributes['invoice_number']);

        return $statement->execute();
    }

    public function dashboardSummary(array $filters): array
    {
        $params = [];
        $where = $this->buildFilters($filters, $params);
        $sql = 'SELECT COUNT(*) AS total_students,
                       SUM(CASE WHEN r.payment_status = \'paid\' THEN 1 ELSE 0 END) AS total_paid_students,
                       SUM(r.amount_paid) AS total_revenue,
                       SUM(r.total_due) AS total_expected,
                       SUM(r.discount_amount) AS total_discount
                FROM registrations r ' . $where;

        $statement = $this->connection->prepare($sql);
        $this->bindParams($statement, $params);
        $statement->execute();

        $row = $statement->fetch();

        return [
            'total_students' => (int) ($row['total_students'] ?? 0),
            'total_paid_students' => (int) ($row['total_paid_students'] ?? 0),
            'total_revenue' => (float) ($row['total_revenue'] ?? 0),
            'total_expected' => (float) ($row['total_expected'] ?? 0),
            'total_discount' => (float) ($row['total_discount'] ?? 0),
        ];
    }

    public function dashboardMonthly(array $filters, bool $ignoreMonth = true, ?int $limitMonth = null): array
    {
        $params = [];
        $options = ['ignore_month' => $ignoreMonth];
        $where = $this->buildFilters($filters, $params, $options);
        $sql = 'SELECT YEAR(r.created_at) AS year,
                       MONTH(r.created_at) AS month,
                       COUNT(*) AS total_students,
                       SUM(r.amount_paid) AS total_revenue
                FROM registrations r ' . $where . '
                GROUP BY YEAR(r.created_at), MONTH(r.created_at)
                ORDER BY YEAR(r.created_at), MONTH(r.created_at)';

        $statement = $this->connection->prepare($sql);
        $this->bindParams($statement, $params);
        $statement->execute();

        $rawRows = $statement->fetchAll();
        $grouped = [];

        foreach ($rawRows as $row) {
            $year = (int) ($row['year'] ?? 0);
            $month = (int) ($row['month'] ?? 0);
            if ($year === 0 || $month === 0) {
                continue;
            }

            $grouped[$year][$month] = [
                'year' => $year,
                'month' => $month,
                'students' => (int) ($row['total_students'] ?? 0),
                'revenue' => (float) ($row['total_revenue'] ?? 0),
            ];
        }

        $result = [];

        foreach ($grouped as $year => $months) {
            ksort($months);
            $studentsCumulative = 0;
            $revenueCumulative = 0.0;

            for ($month = 1; $month <= 12; $month++) {
                if ($limitMonth !== null && $month > $limitMonth) {
                    break;
                }

                $data = $months[$month] ?? [
                    'year' => $year,
                    'month' => $month,
                    'students' => 0,
                    'revenue' => 0.0,
                ];

                $studentsCumulative += $data['students'];
                $revenueCumulative += $data['revenue'];

                $data['students_cumulative'] = $studentsCumulative;
                $data['revenue_cumulative'] = $revenueCumulative;

                $result[] = $data;
            }
        }

        return $result;
    }

    public function dashboardYearly(array $filters, bool $ignoreYear = false, bool $ignoreMonth = false): array
    {
        $params = [];
        $options = [
            'ignore_year' => $ignoreYear,
            'ignore_month' => $ignoreMonth,
        ];
        $where = $this->buildFilters($filters, $params, $options);
        $sql = 'SELECT YEAR(r.created_at) AS year,
                       COUNT(*) AS total_students,
                       SUM(r.amount_paid) AS total_revenue
                FROM registrations r ' . $where . '
                GROUP BY YEAR(r.created_at)
                ORDER BY YEAR(r.created_at)';

        $statement = $this->connection->prepare($sql);
        $this->bindParams($statement, $params);
        $statement->execute();

        $rows = $statement->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'year' => (int) ($row['year'] ?? 0),
                'students' => (int) ($row['total_students'] ?? 0),
                'revenue' => (float) ($row['total_revenue'] ?? 0),
            ];
        }, $rows);
    }

    public function dashboardByBranch(array $filters, bool $ignoreBranch = false): array
    {
        $params = [];
        $options = ['ignore_branch' => $ignoreBranch];
        $where = $this->buildFilters($filters, $params, $options);
        $sql = 'SELECT r.study_location AS branch,
                       COUNT(*) AS total_students,
                       SUM(r.amount_paid) AS total_revenue
                FROM registrations r ' . $where . '
                GROUP BY r.study_location
                ORDER BY total_students DESC';

        $statement = $this->connection->prepare($sql);
        $this->bindParams($statement, $params);
        $statement->execute();

        $rows = $statement->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'branch' => $row['branch'] ?? null,
                'students' => (int) ($row['total_students'] ?? 0),
                'revenue' => (float) ($row['total_revenue'] ?? 0),
            ];
        }, $rows);
    }

    public function dashboardByProgram(array $filters, bool $ignoreProgram = false): array
    {
        $params = [];
        $options = ['ignore_program' => $ignoreProgram];
        $where = $this->buildFilters($filters, $params, $options);
        $sql = 'SELECT p.id AS program_id,
                       p.name AS program_name,
                       p.code AS program_code,
                       COUNT(*) AS total_students,
                       SUM(r.amount_paid) AS total_revenue
                FROM registrations r
                INNER JOIN programs p ON p.id = r.program_id ' . $where . '
                GROUP BY p.id, p.name, p.code
                ORDER BY total_students DESC, p.name ASC';

        $statement = $this->connection->prepare($sql);
        $this->bindParams($statement, $params);
        $statement->execute();

        $rows = $statement->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'program_id' => (int) ($row['program_id'] ?? 0),
                'program_name' => $row['program_name'] ?? '',
                'program_code' => $row['program_code'] ?? '',
                'students' => (int) ($row['total_students'] ?? 0),
                'revenue' => (float) ($row['total_revenue'] ?? 0),
            ];
        }, $rows);
    }

    public function availableYears(): array
    {
        $sql = 'SELECT DISTINCT YEAR(created_at) AS year
                FROM registrations
                ORDER BY YEAR(created_at) DESC';

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        $years = array_map(static fn ($row) => (int) ($row['year'] ?? 0), $statement->fetchAll());

        return array_values(array_filter($years, static fn ($year) => $year > 0));
    }

    public function availablePrograms(): array
    {
        $sql = 'SELECT id, name, code
                FROM programs
                ORDER BY name ASC';

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return array_map(static function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'name' => $row['name'] ?? '',
                'code' => $row['code'] ?? '',
            ];
        }, $statement->fetchAll());
    }

    public function locationSummary(?string $province = null, ?string $city = null): array
    {
        $conditions = [];
        $params = [];

        if ($province !== null && $province !== '') {
            $conditions[] = 'r.province = :province';
            $params['province'] = $province;
        }

        if ($city !== null && $city !== '') {
            $conditions[] = 'r.city = :city';
            $params['city'] = $city;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = 'SELECT r.province,
                       r.city,
                       COUNT(*) AS total,
                       SUM(CASE WHEN r.payment_status = \'paid\' THEN 1 ELSE 0 END) AS total_paid,
                       SUM(CASE WHEN r.payment_status = \'partial\' THEN 1 ELSE 0 END) AS total_partial,
                       SUM(CASE WHEN r.payment_status = \'unpaid\' THEN 1 ELSE 0 END) AS total_unpaid
                FROM registrations r ' . $where . '
                GROUP BY r.province, r.city
                ORDER BY r.province ASC, r.city ASC';

        $statement = $this->connection->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();

        return array_map(static function (array $row): array {
            return [
                'province' => $row['province'] ?? null,
                'city' => $row['city'] ?? null,
                'total' => (int) ($row['total'] ?? 0),
                'paid' => (int) ($row['total_paid'] ?? 0),
                'partial' => (int) ($row['total_partial'] ?? 0),
                'unpaid' => (int) ($row['total_unpaid'] ?? 0),
            ];
        }, $statement->fetchAll());
    }

    public function distinctProvinces(): array
    {
        $sql = 'SELECT DISTINCT province
                FROM registrations
                WHERE province IS NOT NULL AND province <> \'\'
                ORDER BY province';

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return array_map(static fn ($row) => $row['province'], $statement->fetchAll());
    }

    public function distinctCities(?string $province = null): array
    {
        $conditions = ['city IS NOT NULL', 'city <> \'\''];
        $params = [];

        if ($province !== null && $province !== '') {
            $conditions[] = 'province = :province';
            $params['province'] = $province;
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $sql = 'SELECT DISTINCT city
                FROM registrations ' . $where . '
                ORDER BY city';

        $statement = $this->connection->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();

        return array_map(static fn ($row) => $row['city'], $statement->fetchAll());
    }

    private function toDecimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function buildFilters(array $filters, array &$params, array $options = []): string
    {
        $conditions = [];
        $options = array_merge([
            'ignore_year' => false,
            'ignore_month' => false,
            'ignore_program' => false,
            'ignore_branch' => false,
        ], $options);

        if (!empty($filters['year']) && !$options['ignore_year']) {
            $conditions[] = 'YEAR(r.created_at) = :year';
            $params['year'] = (int) $filters['year'];
        }

        if (!empty($filters['month']) && !$options['ignore_month']) {
            $conditions[] = 'MONTH(r.created_at) = :month';
            $params['month'] = (int) $filters['month'];
        }

        if (!empty($filters['program_id']) && !$options['ignore_program']) {
            $conditions[] = 'r.program_id = :program_id';
            $params['program_id'] = (int) $filters['program_id'];
        }

        if (!empty($filters['branch']) && !$options['ignore_branch']) {
            $conditions[] = 'r.study_location = :branch';
            $params['branch'] = $filters['branch'];
        }

        if (empty($conditions)) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }

    private function bindParams(\PDOStatement $statement, array $params): void
    {
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $statement->bindValue(':' . $key, $value, $paramType);
        }
    }
}

<?php

namespace App\Models;

use PDOException;

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

    private function toDecimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}

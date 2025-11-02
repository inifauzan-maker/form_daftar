<?php

namespace App\Models;

use PDOException;

class Registration extends Model
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO registrations
                (full_name, school_id, school_name, class_level, phone_number, province, city, district, subdistrict, postal_code, address_detail, program_id, student_status, payment_status, payment_notes)
                VALUES
                (:full_name, :school_id, :school_name, :class_level, :phone_number, :province, :city, :district, :subdistrict, :postal_code, :address_detail, :program_id, :student_status, :payment_status, :payment_notes)';

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

        try {
            $statement->execute();
        } catch (PDOException $exception) {
            throw $exception;
        }

        return (int) $this->connection->lastInsertId();
    }

    public function all(): array
    {
        $sql = 'SELECT r.id, r.full_name, r.school_name, r.class_level, r.phone_number,
                       r.province, r.city, r.district, r.subdistrict, r.postal_code,
                       r.address_detail, r.student_status, r.payment_status, r.payment_notes,
                       r.created_at, r.updated_at,
                       p.name AS program_name, p.code AS program_code
                FROM registrations r
                INNER JOIN programs p ON p.id = r.program_id
                ORDER BY r.created_at DESC';

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function updateStatus(int $id, array $attributes): bool
    {
        $sql = 'UPDATE registrations
                SET student_status = :student_status,
                    payment_status = :payment_status,
                    payment_notes = :payment_notes
                WHERE id = :id';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->bindValue(':student_status', $attributes['student_status']);
        $statement->bindValue(':payment_status', $attributes['payment_status']);
        $statement->bindValue(':payment_notes', $attributes['payment_notes']);

        return $statement->execute();
    }
}

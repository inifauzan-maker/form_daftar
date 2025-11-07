<?php

namespace App\Models;

class Program extends Model
{
    public function getByCategory(string $category): array
    {
        $sql = 'SELECT id, name, code, class_category
                FROM programs
                WHERE class_category = :category
                ORDER BY name';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':category', $category);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function all(): array
    {
        $sql = 'SELECT id, name, code, class_category, registration_fee, tuition_fee,
                       target_students, target_revenue, description, image_path
                FROM programs
                ORDER BY class_category, name';

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT id, name, code, class_category, registration_fee, tuition_fee,
                       target_students, target_revenue, description, image_path
                FROM programs
                WHERE id = :id
                LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        $program = $statement->fetch();

        return $program ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $sql = 'SELECT id, code FROM programs WHERE code = :code LIMIT 1';
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':code', $code);
        $statement->execute();

        $program = $statement->fetch();

        return $program ?: null;
    }

    public function create(array $attributes): int
    {
        $sql = 'INSERT INTO programs
                (name, code, class_category, registration_fee, tuition_fee,
                 target_students, target_revenue, description, image_path)
                VALUES
                (:name, :code, :class_category, :registration_fee, :tuition_fee,
                 :target_students, :target_revenue, :description, :image_path)';

        $statement = $this->connection->prepare($sql);
        $this->bindAttributes($statement, $attributes);
        $statement->execute();

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $attributes): bool
    {
        $sql = 'UPDATE programs
                SET name = :name,
                    code = :code,
                    class_category = :class_category,
                    registration_fee = :registration_fee,
                    tuition_fee = :tuition_fee,
                    target_students = :target_students,
                    target_revenue = :target_revenue,
                    description = :description,
                    image_path = :image_path
                WHERE id = :id';

        $statement = $this->connection->prepare($sql);
        $this->bindAttributes($statement, $attributes);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);

        return $statement->execute();
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM programs WHERE id = :id';
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);

        return $statement->execute();
    }

    private function bindAttributes(\PDOStatement $statement, array $attributes): void
    {
        $statement->bindValue(':name', $attributes['name']);
        $statement->bindValue(':code', $attributes['code']);
        $statement->bindValue(':class_category', $attributes['class_category']);
        $statement->bindValue(':registration_fee', $this->toDecimal($attributes['registration_fee']));
        $statement->bindValue(':tuition_fee', $this->toDecimal($attributes['tuition_fee']));
        $statement->bindValue(':target_students', (int) $attributes['target_students'], \PDO::PARAM_INT);
        $statement->bindValue(':target_revenue', $this->toDecimal($attributes['target_revenue']));
        $statement->bindValue(':description', $attributes['description']);
        $statement->bindValue(':image_path', $attributes['image_path']);
    }

    private function toDecimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}

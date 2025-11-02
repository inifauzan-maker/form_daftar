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

    public function find(int $id): ?array
    {
        $sql = 'SELECT id, name, code, class_category FROM programs WHERE id = :id LIMIT 1';
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        $program = $statement->fetch();

        return $program ?: null;
    }
}

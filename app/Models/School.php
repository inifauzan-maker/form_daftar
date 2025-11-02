<?php

namespace App\Models;

class School extends Model
{
    public function search(string $term = '', int $limit = 20): array
    {
        $sql = 'SELECT id, name, type, city, province, level_group
                FROM schools
                WHERE name LIKE :term
                ORDER BY name
                LIMIT :limit';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':term', '%' . $term . '%');
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT id, name, type, city, province, level_group
                FROM schools
                WHERE id = :id
                LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        $school = $statement->fetch();

        return $school ?: null;
    }
}

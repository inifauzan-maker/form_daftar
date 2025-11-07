<?php

namespace App\Models;

use PDO;

class Permission extends Model
{
    public function all(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, slug, description, created_at, updated_at FROM permissions ORDER BY name ASC'
        );
        $statement->execute();

        return array_map(function (array $row): array {
            $row['id'] = (int) $row['id'];
            return $row;
        }, $statement->fetchAll());
    }

    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, slug, description, created_at, updated_at FROM permissions WHERE id = :id LIMIT 1'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $record = $statement->fetch();

        return $record ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, slug, description, created_at, updated_at FROM permissions WHERE slug = :slug LIMIT 1'
        );
        $statement->bindValue(':slug', $slug);
        $statement->execute();

        $record = $statement->fetch();

        return $record ?: null;
    }

    public function create(array $attributes): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO permissions (name, slug, description) VALUES (:name, :slug, :description)'
        );
        $statement->bindValue(':name', $attributes['name']);
        $statement->bindValue(':slug', $attributes['slug']);
        $statement->bindValue(':description', $attributes['description'] ?? null);
        $statement->execute();

        return (int) $this->connection->lastInsertId();
    }

    public function updatePermission(int $id, array $attributes): void
    {
        $fields = [];
        $bindings = [':id' => $id];

        if (array_key_exists('name', $attributes)) {
            $fields[] = 'name = :name';
            $bindings[':name'] = $attributes['name'];
        }

        if (array_key_exists('slug', $attributes)) {
            $fields[] = 'slug = :slug';
            $bindings[':slug'] = $attributes['slug'];
        }

        if (array_key_exists('description', $attributes)) {
            $fields[] = 'description = :description';
            $bindings[':description'] = $attributes['description'];
        }

        if (empty($fields)) {
            return;
        }

        $sql = 'UPDATE permissions SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $statement = $this->connection->prepare($sql);

        foreach ($bindings as $placeholder => $value) {
            $type = $placeholder === ':id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $statement->bindValue($placeholder, $value, $type);
        }

        $statement->execute();
    }

    public function delete(int $id): void
    {
        $statement = $this->connection->prepare('DELETE FROM permissions WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
    }
}


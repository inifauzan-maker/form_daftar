<?php

namespace App\Models;

use PDO;
use PDOException;

class Role extends Model
{
    public function allWithPermissions(): array
    {
        $roles = $this->baseRoles();

        if (empty($roles)) {
            return $roles;
        }

        $roleIds = array_column($roles, 'id');
        $permissions = $this->permissionsForRoles($roleIds);

        $grouped = [];
        foreach ($permissions as $permission) {
            $roleId = (int) $permission['role_id'];
            $grouped[$roleId][] = [
                'id' => (int) $permission['id'],
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'description' => $permission['description'],
            ];
        }

        foreach ($roles as &$role) {
            $role['permissions'] = $grouped[(int) $role['id']] ?? [];
        }

        unset($role);

        return $roles;
    }

    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, slug, description, created_at, updated_at FROM roles WHERE id = :id LIMIT 1'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $record = $statement->fetch();

        return $record ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, slug, description, created_at, updated_at FROM roles WHERE slug = :slug LIMIT 1'
        );
        $statement->bindValue(':slug', $slug);
        $statement->execute();

        $record = $statement->fetch();

        return $record ?: null;
    }

    public function create(array $attributes, array $permissionIds = []): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO roles (name, slug, description) VALUES (:name, :slug, :description)'
        );
        $statement->bindValue(':name', $attributes['name']);
        $statement->bindValue(':slug', $attributes['slug']);
        $statement->bindValue(':description', $attributes['description'] ?? null);

        $this->connection->beginTransaction();

        try {
            $statement->execute();
            $roleId = (int) $this->connection->lastInsertId();

            if (!empty($permissionIds)) {
                $this->syncPermissions($roleId, $permissionIds, false);
            }

            $this->connection->commit();

            return $roleId;
        } catch (PDOException $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function updateRole(int $id, array $attributes, ?array $permissionIds = null): void
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

        if (!empty($fields)) {
            $sql = 'UPDATE roles SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $statement = $this->connection->prepare($sql);

            foreach ($bindings as $placeholder => $value) {
                $type = $placeholder === ':id' ? PDO::PARAM_INT : PDO::PARAM_STR;
                $statement->bindValue($placeholder, $value, $type);
            }

            $statement->execute();
        }

        if ($permissionIds !== null) {
            $this->syncPermissions($id, $permissionIds);
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->connection->prepare('DELETE FROM roles WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
    }

    public function syncPermissions(int $roleId, array $permissionIds, bool $purge = true): void
    {
        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));

        if ($purge) {
            $delete = $this->connection->prepare('DELETE FROM permission_role WHERE role_id = :role_id');
            $delete->bindValue(':role_id', $roleId, PDO::PARAM_INT);
            $delete->execute();
        }

        if (empty($permissionIds)) {
            return;
        }

        $insert = $this->connection->prepare(
            'INSERT IGNORE INTO permission_role (permission_id, role_id) VALUES (:permission_id, :role_id)'
        );

        foreach ($permissionIds as $permissionId) {
            $insert->bindValue(':permission_id', $permissionId, PDO::PARAM_INT);
            $insert->bindValue(':role_id', $roleId, PDO::PARAM_INT);
            $insert->execute();
        }
    }

    private function baseRoles(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, slug, description, created_at, updated_at FROM roles ORDER BY name ASC'
        );
        $statement->execute();

        return array_map(function (array $row): array {
            $row['id'] = (int) $row['id'];
            return $row;
        }, $statement->fetchAll());
    }

    private function permissionsForRoles(array $roleIds): array
    {
        if (empty($roleIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $sql = "SELECT pr.role_id, p.id, p.name, p.slug, p.description
                FROM permission_role pr
                INNER JOIN permissions p ON p.id = pr.permission_id
                WHERE pr.role_id IN ($placeholders)
                ORDER BY p.name ASC";

        $statement = $this->connection->prepare($sql);
        $statement->execute($roleIds);

        return $statement->fetchAll();
    }
}


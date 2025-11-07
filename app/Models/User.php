<?php

namespace App\Models;

use PDO;
use PDOException;

class User extends Model
{
    public function allWithRelations(): array
    {
        $users = $this->baseUsers();
        $userIds = array_column($users, 'id');

        if (empty($userIds)) {
            return $users;
        }

        $roles = $this->rolesForUsers($userIds);
        $directPermissions = $this->directPermissionsForUsers($userIds);
        $rolePermissions = $this->permissionsForRoles(array_unique(array_column($roles, 'role_id')));

        $rolesGrouped = [];
        foreach ($roles as $role) {
            $userId = (int) $role['user_id'];
            $rolesGrouped[$userId][] = [
                'id' => (int) $role['role_id'],
                'name' => $role['name'],
                'slug' => $role['slug'],
                'description' => $role['description'],
            ];
        }

        $directGrouped = [];
        foreach ($directPermissions as $permission) {
            $userId = (int) $permission['user_id'];
            $directGrouped[$userId][] = $this->formatPermissionRecord($permission);
        }

        $rolePermissionMap = [];
        foreach ($rolePermissions as $permission) {
            $roleId = (int) $permission['role_id'];
            $rolePermissionMap[$roleId][] = $this->formatPermissionRecord($permission);
        }

        foreach ($users as &$user) {
            $id = (int) $user['id'];
            $userRoles = $rolesGrouped[$id] ?? [];
            $userDirectPermissions = $directGrouped[$id] ?? [];

            $permissionMap = [];

            foreach ($userRoles as $role) {
                $roleId = (int) $role['id'];
                foreach ($rolePermissionMap[$roleId] ?? [] as $permission) {
                    $permissionMap[$permission['slug']] = $permission;
                }
            }

            foreach ($userDirectPermissions as $permission) {
                $permissionMap[$permission['slug']] = $permission;
            }

            $user['roles'] = $userRoles;
            $user['direct_permissions'] = $userDirectPermissions;
            $user['permissions'] = array_values($permissionMap);
        }

        unset($user);

        return $users;
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT id, name, email, password, status, created_at, updated_at
                FROM users
                WHERE id = :id
                LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $record = $statement->fetch();

        return $record ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id, name, email, password, status, created_at, updated_at
                FROM users
                WHERE email = :email
                LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':email', strtolower(trim($email)));
        $statement->execute();

        $record = $statement->fetch();

        return $record ?: null;
    }

    public function findWithRelations(int $id): array
    {
        $user = $this->find($id);

        if (!$user) {
            throw new PDOException('User not found.');
        }

        $users = $this->allWithRelations();
        foreach ($users as $item) {
            if ((int) $item['id'] === $id) {
                $item['password'] = $user['password'];
                return $item;
            }
        }

        $user['roles'] = [];
        $user['direct_permissions'] = [];
        $user['permissions'] = [];

        return $user;
    }

    public function create(array $attributes, array $roleIds = [], array $permissionIds = []): int
    {
        $sql = 'INSERT INTO users (name, email, password, status)
                VALUES (:name, :email, :password, :status)';

        $this->connection->beginTransaction();

        try {
            $statement = $this->connection->prepare($sql);
            $statement->bindValue(':name', $attributes['name']);
            $statement->bindValue(':email', strtolower(trim($attributes['email'])));
            $statement->bindValue(':password', $attributes['password']);
            $statement->bindValue(':status', $attributes['status'] ?? 'active');
            $statement->execute();

            $userId = (int) $this->connection->lastInsertId();

            if (!empty($roleIds)) {
                $this->syncRoles($userId, $roleIds, false);
            }

            if (!empty($permissionIds)) {
                $this->syncPermissions($userId, $permissionIds, false);
            }

            $this->connection->commit();

            return $userId;
        } catch (PDOException $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function updateUser(int $id, array $attributes, ?array $roleIds = null, ?array $permissionIds = null): void
    {
        $fields = [];
        $bindings = [':id' => $id];

        if (array_key_exists('name', $attributes)) {
            $fields[] = 'name = :name';
            $bindings[':name'] = $attributes['name'];
        }

        if (array_key_exists('email', $attributes)) {
            $fields[] = 'email = :email';
            $bindings[':email'] = strtolower(trim($attributes['email']));
        }

        if (array_key_exists('password', $attributes) && $attributes['password']) {
            $fields[] = 'password = :password';
            $bindings[':password'] = $attributes['password'];
        }

        if (array_key_exists('status', $attributes)) {
            $fields[] = 'status = :status';
            $bindings[':status'] = $attributes['status'];
        }

        if (!empty($fields)) {
            $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $statement = $this->connection->prepare($sql);

            foreach ($bindings as $key => $value) {
                $param = $key === ':id' ? PDO::PARAM_INT : PDO::PARAM_STR;
                $statement->bindValue($key, $value, $param);
            }

            $statement->execute();
        }

        if ($roleIds !== null) {
            $this->syncRoles($id, $roleIds);
        }

        if ($permissionIds !== null) {
            $this->syncPermissions($id, $permissionIds);
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->connection->prepare('DELETE FROM users WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
    }

    public function syncRoles(int $userId, array $roleIds, bool $purge = true): void
    {
        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));

        if ($purge) {
            $delete = $this->connection->prepare('DELETE FROM role_user WHERE user_id = :user_id');
            $delete->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $delete->execute();
        }

        if (empty($roleIds)) {
            return;
        }

        $insert = $this->connection->prepare(
            'INSERT IGNORE INTO role_user (role_id, user_id) VALUES (:role_id, :user_id)'
        );

        foreach ($roleIds as $roleId) {
            $insert->bindValue(':role_id', $roleId, PDO::PARAM_INT);
            $insert->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $insert->execute();
        }
    }

    public function syncPermissions(int $userId, array $permissionIds, bool $purge = true): void
    {
        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));

        if ($purge) {
            $delete = $this->connection->prepare('DELETE FROM permission_user WHERE user_id = :user_id');
            $delete->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $delete->execute();
        }

        if (empty($permissionIds)) {
            return;
        }

        $insert = $this->connection->prepare(
            'INSERT IGNORE INTO permission_user (permission_id, user_id) VALUES (:permission_id, :user_id)'
        );

        foreach ($permissionIds as $permissionId) {
            $insert->bindValue(':permission_id', $permissionId, PDO::PARAM_INT);
            $insert->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $insert->execute();
        }
    }

    private function baseUsers(): array
    {
        $sql = 'SELECT id, name, email, status, created_at, updated_at
                FROM users
                ORDER BY name ASC';

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return array_map(function (array $row): array {
            $row['id'] = (int) $row['id'];
            return $row;
        }, $statement->fetchAll());
    }

    private function rolesForUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "SELECT ru.user_id, r.id AS role_id, r.name, r.slug, r.description
                FROM role_user ru
                INNER JOIN roles r ON r.id = ru.role_id
                WHERE ru.user_id IN ($placeholders)
                ORDER BY r.name ASC";

        $statement = $this->connection->prepare($sql);
        $statement->execute($userIds);

        return $statement->fetchAll();
    }

    private function directPermissionsForUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "SELECT pu.user_id, p.id, p.name, p.slug, p.description
                FROM permission_user pu
                INNER JOIN permissions p ON p.id = pu.permission_id
                WHERE pu.user_id IN ($placeholders)
                ORDER BY p.name ASC";

        $statement = $this->connection->prepare($sql);
        $statement->execute($userIds);

        return $statement->fetchAll();
    }

    private function permissionsForRoles(array $roleIds): array
    {
        $roleIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn ($id) => is_numeric($id) ? (int) $id : null,
                        $roleIds
                    ),
                    static fn ($id) => $id !== null
                )
            )
        );

        if (empty($roleIds)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($roleIds as $index => $roleId) {
            $key = ":role{$index}";
            $placeholders[] = $key;
            $params[$key] = $roleId;
        }

        $sql = sprintf(
            'SELECT pr.role_id, p.id, p.name, p.slug, p.description
             FROM permission_role pr
             INNER JOIN permissions p ON p.id = pr.permission_id
             WHERE pr.role_id IN (%s)
             ORDER BY p.name ASC',
            implode(',', $placeholders)
        );

        $statement = $this->connection->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_INT);
        }

        $statement->execute();

        return $statement->fetchAll();
    }

    private function formatPermissionRecord(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'description' => $row['description'],
        ];
    }
}

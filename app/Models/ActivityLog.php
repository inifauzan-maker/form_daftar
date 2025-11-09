<?php

namespace App\Models;

use PDO;
use PDOStatement;

class ActivityLog extends Model
{
    public function create(array $attributes): int
    {
        $sql = 'INSERT INTO activity_logs (
                    user_id,
                    action,
                    description,
                    metadata,
                    ip_address,
                    user_agent,
                    request_path,
                    request_method
                ) VALUES (
                    :user_id,
                    :action,
                    :description,
                    :metadata,
                    :ip_address,
                    :user_agent,
                    :request_path,
                    :request_method
                )';

        $statement = $this->connection->prepare($sql);

        $userId = isset($attributes['user_id']) && $attributes['user_id'] !== null
            ? (int) $attributes['user_id']
            : null;

        if ($userId !== null) {
            $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        } else {
            $statement->bindValue(':user_id', null, PDO::PARAM_NULL);
        }

        $statement->bindValue(':action', $this->slice((string) ($attributes['action'] ?? 'general'), 150));
        $statement->bindValue(':description', $this->nullableSlice($attributes['description'] ?? null, 1000));
        $statement->bindValue(':metadata', $this->encodeMetadata($attributes['metadata'] ?? null));
        $statement->bindValue(':ip_address', $this->nullableSlice($attributes['ip_address'] ?? null, 45));
        $statement->bindValue(':user_agent', $this->nullableSlice($attributes['user_agent'] ?? null, 255));
        $statement->bindValue(':request_path', $this->nullableSlice($attributes['request_path'] ?? null, 255));
        $statement->bindValue(':request_method', $this->nullableSlice(strtoupper((string) ($attributes['request_method'] ?? '')), 10));

        $statement->execute();

        return (int) $this->connection->lastInsertId();
    }

    public function paginate(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(5, min(100, (int) ($filters['per_page'] ?? 25)));
        $offset = ($page - 1) * $perPage;

        [$whereSql, $params] = $this->buildFilters($filters);

        $countSql = "SELECT COUNT(*) FROM activity_logs al
                     LEFT JOIN users u ON u.id = al.user_id
                     {$whereSql}";
        $countStatement = $this->connection->prepare($countSql);
        foreach ($params as $key => $value) {
            $this->bind($countStatement, $key, $value);
        }
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $sql = "SELECT al.*, u.name AS user_name, u.email AS user_email
                FROM activity_logs al
                LEFT JOIN users u ON u.id = al.user_id
                {$whereSql}
                ORDER BY al.created_at DESC
                LIMIT :limit OFFSET :offset";

        $statement = $this->connection->prepare($sql);
        foreach ($params as $key => $value) {
            $this->bind($statement, $key, $value);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $records = $statement->fetchAll();

        return [
            'data' => $this->transformRecords($records),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / max($perPage, 1))),
            ],
        ];
    }

    public function actions(): array
    {
        $statement = $this->connection->query('SELECT DISTINCT action FROM activity_logs ORDER BY action ASC');

        if (!$statement) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($action) => (string) $action, $statement->fetchAll(PDO::FETCH_COLUMN))));
    }

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];

        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';
        if ($search !== '') {
            $clauses[] = '(al.action LIKE :search OR al.description LIKE :search OR u.name LIKE :search OR u.email LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $action = isset($filters['action']) ? trim((string) $filters['action']) : '';
        if ($action !== '') {
            $clauses[] = 'al.action = :action';
            $params[':action'] = $action;
        }

        $userId = isset($filters['user_id']) ? (int) $filters['user_id'] : 0;
        if ($userId > 0) {
            $clauses[] = 'al.user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        $whereSql = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        return [$whereSql, $params];
    }

    private function transformRecords(array $records): array
    {
        return array_map(function (array $record): array {
            $record['id'] = (int) $record['id'];
            $record['user_id'] = isset($record['user_id']) ? (int) $record['user_id'] : null;
            $record['metadata'] = $this->decodeMetadata($record['metadata'] ?? null);

            return $record;
        }, $records);
    }

    private function encodeMetadata(mixed $metadata): ?string
    {
        if ($metadata === null) {
            return null;
        }

        if (is_string($metadata)) {
            $trimmed = trim($metadata);
            return $trimmed === '' ? null : $trimmed;
        }

        if (empty($metadata)) {
            return null;
        }

        $encoded = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded !== false ? $encoded : null;
    }

    private function decodeMetadata(mixed $metadata): ?array
    {
        if ($metadata === null || $metadata === '') {
            return null;
        }

        if (is_array($metadata)) {
            return $metadata;
        }

        $decoded = json_decode((string) $metadata, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function bind(PDOStatement $statement, string $key, mixed $value): void
    {
        if (is_int($value)) {
            $statement->bindValue($key, $value, PDO::PARAM_INT);
            return;
        }

        $statement->bindValue($key, $value);
    }

    private function slice(string $value, int $limit): string
    {
        $value = trim($value);

        if ($value === '') {
            $value = 'general';
        }

        return mb_substr($value, 0, $limit);
    }

    private function nullableSlice(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $limit);
    }
}

<?php

namespace App\Models;

class School extends Model
{
    private const DATA_FILE = __DIR__ . '/../../database/schools.json';
    private static ?array $cache = null;

    public function search(string $term = '', int $limit = 20): array
    {
        $data = $this->loadData();
        $term = trim($term);
        $lower = static fn (string $value): string => function_exists('mb_strtolower')
            ? mb_strtolower($value)
            : strtolower($value);

        if ($term !== '') {
            $normalized = $lower($term);
            $data = array_filter($data, static function (array $school) use ($normalized, $lower): bool {
                return str_contains($lower($school['name']), $normalized)
                    || str_contains($lower($school['city']), $normalized)
                    || str_contains($lower($school['province']), $normalized);
            });
        }

        usort($data, static fn (array $a, array $b) => strcmp($a['name'], $b['name']));

        return array_slice(array_values($data), 0, max($limit, 1));
    }

    public function find(int $id): ?array
    {
        foreach ($this->loadData() as $school) {
            if ((int) $school['id'] === $id) {
                return $school;
            }
        }

        return null;
    }

    private function loadData(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $path = self::DATA_FILE;

        if (is_file($path)) {
            $json = file_get_contents($path);
            $decoded = json_decode($json ?: '[]', true);

            if (is_array($decoded)) {
                self::$cache = array_map(static function (array $school): array {
                    return [
                        'id' => (int) ($school['id'] ?? 0),
                        'name' => (string) ($school['name'] ?? ''),
                        'type' => (string) ($school['type'] ?? ''),
                        'city' => (string) ($school['city'] ?? ''),
                        'province' => (string) ($school['province'] ?? ''),
                        'level_group' => (string) ($school['level_group'] ?? ''),
                    ];
                }, $decoded);

                return self::$cache;
            }
        }

        $sql = 'SELECT id, name, type, city, province, level_group FROM schools';
        $statement = $this->connection->prepare($sql);
        $statement->execute();

        self::$cache = $statement->fetchAll() ?: [];

        return self::$cache;
    }
}

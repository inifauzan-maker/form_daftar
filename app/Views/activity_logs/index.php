<?php

use App\Core\Auth;

$user = isset($user) && is_array($user) ? $user : Auth::user();
$logs = isset($logs) && is_array($logs) ? $logs : [];
$meta = array_merge([
    'page' => 1,
    'per_page' => 25,
    'total' => 0,
    'last_page' => 1,
], isset($meta) && is_array($meta) ? $meta : []);
$filters = array_merge([
    'search' => '',
    'action' => null,
    'user_id' => null,
    'per_page' => $meta['per_page'],
    'page' => $meta['page'],
], isset($filters) && is_array($filters) ? $filters : []);
$actions = isset($actions) && is_array($actions) ? $actions : [];
$canManageUsers = Auth::can('manage_users');
$canViewActivityLogs = Auth::can('view_activity_logs');

function activity_logs_format_datetime(?string $value): string
{
    if (!$value) {
        return '-';
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value;
    }

    return date('d M Y H:i:s', $timestamp);
}

function activity_logs_format_value(mixed $value): string
{
    if ($value === null) {
        return 'null';
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_array($value)) {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $encoded !== false ? $encoded : '[object]';
    }

    return (string) $value;
}

function activity_logs_query_path(array $filters, array $overrides = []): string
{
    $params = array_merge($filters, $overrides);
    $params = array_filter($params, static fn ($value) => $value !== null && $value !== '');
    $query = http_build_query($params);

    $base = route_path('/activity-logs');

    return $query === '' ? $base : $base . '?' . $query;
}

$total = (int) ($meta['total'] ?? 0);
$page = (int) ($meta['page'] ?? 1);
$perPage = (int) ($meta['per_page'] ?? 25);
$lastPage = max(1, (int) ($meta['last_page'] ?? 1));
$start = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$end = $total === 0 ? 0 : min($total, $start + count($logs) - 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($appName ?? 'SI VMI') . ' - Log Aktivitas', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard dashboard--activity">
    <div class="dashboard__shell">
        <aside class="dashboard__sidebar">
            <div class="brand">
                <span class="brand__logo">SI</span>
                <div class="brand__meta">
                    <strong><?= htmlspecialchars($appName ?? 'SI VMI', ENT_QUOTES, 'UTF-8') ?></strong>
                    <span>Admin Panel</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="<?= htmlspecialchars(route_path('/'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Form Pendaftaran</span>
                </a>
                <a href="<?= htmlspecialchars(route_path('/dashboard'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Dashboard Peraihan</span>
                </a>
                <a href="<?= htmlspecialchars(route_path('/programs'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Program Bimbel</span>
                </a>
                <a href="<?= htmlspecialchars(route_path('/products'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Produk</span>
                </a>
                <?php if ($canManageUsers): ?>
                <a href="<?= htmlspecialchars(route_path('/users'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Manajemen Pengguna</span>
                </a>
                <?php endif; ?>
                <?php if ($canViewActivityLogs): ?>
                <a href="<?= htmlspecialchars(route_path('/activity-logs'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link is-active">
                    <span>Log Aktivitas</span>
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="dashboard__main dashboard__main--activity">
            <header class="dashboard__header">
                <div>
                    <h1>Log Aktivitas Pengguna</h1>
                    <p>Lacak aksi penting yang dijalankan tim pada panel admin.</p>
                </div>
                <div class="header-account">
                    <div class="header-account__meta">
                        <strong><?= htmlspecialchars($user['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <form method="POST" action="<?= htmlspecialchars(route_path('/logout'), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn-ghost btn-ghost--small">Keluar</button>
                    </form>
                </div>
            </header>

            <section class="card activity-filter">
                <header>
                    <h2>Pencarian & Filter</h2>
                    <p>Sesuaikan log berdasarkan aksi, kata kunci, atau ID pengguna.</p>
                </header>
                <form method="GET" action="<?= htmlspecialchars(route_path('/activity-logs'), ENT_QUOTES, 'UTF-8') ?>" class="activity-filter__form">
                    <label>
                        <span>Kata kunci</span>
                        <input
                            type="search"
                            name="search"
                            value="<?= htmlspecialchars((string) ($filters['search'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Cari aksi, deskripsi, atau nama pengguna">
                    </label>
                    <label>
                        <span>Jenis aksi</span>
                        <select name="action">
                            <option value="">Semua aksi</option>
                            <?php foreach ($actions as $action): ?>
                            <option value="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($filters['action'] ?? null) === $action ? 'selected' : '' ?>>
                                <?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>ID pengguna</span>
                        <input
                            type="number"
                            name="user_id"
                            min="1"
                            value="<?= htmlspecialchars((string) ($filters['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Contoh: 3">
                    </label>
                    <label>
                        <span>Data per halaman</span>
                        <select name="per_page">
                            <?php foreach ([10, 25, 50] as $option): ?>
                            <option value="<?= $option ?>" <?= (int) $perPage === $option ? 'selected' : '' ?>>
                                <?= $option ?> baris
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="activity-filter__actions">
                        <button type="submit" class="btn-primary">Terapkan Filter</button>
                        <a class="btn-ghost" href="<?= htmlspecialchars(route_path('/activity-logs'), ENT_QUOTES, 'UTF-8') ?>">Reset</a>
                    </div>
                </form>
            </section>

            <section class="card activity-card">
                <header class="activity-card__header">
                    <div>
                        <h2>Riwayat Aktivitas</h2>
                        <p>Menampilkan <?= $start ?> - <?= $end ?> dari <?= $total ?> catatan.</p>
                    </div>
                    <span class="badge"><?= htmlspecialchars(strtoupper((string) date('d M Y')), ENT_QUOTES, 'UTF-8') ?></span>
                </header>

                <div class="table-wrapper">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="4" class="activity-table__empty">
                                    Belum ada log aktivitas untuk filter yang dipilih.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($logs as $entry): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars(activity_logs_format_datetime($entry['created_at'] ?? null), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="activity-meta">
                                        <?= htmlspecialchars($entry['ip_address'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="activity-user">
                                        <strong><?= htmlspecialchars($entry['user_name'] ?? 'Sistem', ENT_QUOTES, 'UTF-8') ?></strong>
                                        <?php if (!empty($entry['user_email'])): ?>
                                        <span><?= htmlspecialchars($entry['user_email'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($entry['user_id'])): ?>
                                        <span class="activity-user__id">#<?= (int) $entry['user_id'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="activity-pill"><?= htmlspecialchars($entry['action'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                    <p class="activity-description">
                                        <?= htmlspecialchars($entry['description'] ?? 'Tanpa deskripsi', ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </td>
                                <td>
                                    <div class="activity-detail">
                                        <span class="activity-meta">
                                            <?= htmlspecialchars(($entry['request_method'] ?? 'GET') . ' ' . ($entry['request_path'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <?php if (!empty($entry['metadata']) && is_array($entry['metadata'])): ?>
                                        <dl class="activity-detail__list">
                                            <?php foreach ($entry['metadata'] as $key => $value): ?>
                                            <div>
                                                <dt><?= htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') ?></dt>
                                                <dd><?= htmlspecialchars(activity_logs_format_value($value), ENT_QUOTES, 'UTF-8') ?></dd>
                                            </div>
                                            <?php endforeach; ?>
                                        </dl>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="activity-pagination">
                    <div>
                        Halaman <?= $page ?> dari <?= $lastPage ?>.
                    </div>
                    <div class="activity-pagination__controls">
                        <?php $prevDisabled = $page <= 1; ?>
                        <?php $nextDisabled = $page >= $lastPage; ?>
                        <?php if ($prevDisabled): ?>
                        <span class="btn-secondary btn-secondary--disabled">Sebelumnya</span>
                        <?php else: ?>
                        <a class="btn-secondary" href="<?= htmlspecialchars(activity_logs_query_path($filters, ['page' => $page - 1]), ENT_QUOTES, 'UTF-8') ?>">Sebelumnya</a>
                        <?php endif; ?>
                        <?php if ($nextDisabled): ?>
                        <span class="btn-secondary btn-secondary--disabled">Berikutnya</span>
                        <?php else: ?>
                        <a class="btn-secondary" href="<?= htmlspecialchars(activity_logs_query_path($filters, ['page' => $page + 1]), ENT_QUOTES, 'UTF-8') ?>">Berikutnya</a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

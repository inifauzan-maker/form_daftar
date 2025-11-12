<?php

use App\Core\Auth;

$user = isset($user) && is_array($user) ? $user : Auth::user();
$products = isset($products) && is_array($products) ? $products : [];
$totals = array_merge([
    'target_students' => 0,
    'target_revenue' => 0.0,
    'actual_students' => 0,
    'actual_revenue' => 0.0,
], isset($totals) && is_array($totals) ? $totals : []);
$canManageUsers = Auth::can('manage_users');
$canViewActivityLogs = Auth::can('view_activity_logs');
$categoryLabels = [
    'SD_SMP' => 'SD & SMP',
    'X_XI' => 'Kelas X-XI',
    'XII' => 'Kelas XII',
];

function format_rupiah(float $value): string
{
    return 'Rp' . number_format($value, 0, ',', '.');
}

function format_number($value): string
{
    return number_format((int) $value, 0, ',', '.');
}

function format_percent(?float $value): string
{
    if ($value === null) {
        return 'Tidak ditargetkan';
    }

    return rtrim(rtrim(number_format($value, 1, ',', '.'), '0'), ',') . '%';
}

function progress_width(?float $percent): float
{
    if ($percent === null || $percent <= 0) {
        return 0.0;
    }

    return min(100, $percent);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($appName ?? 'SI VMI') . ' - Produk Program', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard dashboard--products">
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
                <a href="<?= htmlspecialchars(route_path('/products'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link is-active">
                    <span>Produk</span>
                </a>
                <?php if ($canManageUsers): ?>
                <a href="<?= htmlspecialchars(route_path('/users'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Manajemen Pengguna</span>
                </a>
                <?php endif; ?>
                <?php if ($canViewActivityLogs): ?>
                <a href="<?= htmlspecialchars(route_path('/activity-logs'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Log Aktivitas</span>
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="dashboard__main dashboard__main--products">
            <header class="dashboard__header">
                <div>
                    <h1>Produk & Poster Program</h1>
                    <p>Kurasi materi promosi, target siswa dan omzet, serta pantau ketercapaian realisasi setiap program.</p>
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

            <section class="product-hero card">
                <div class="product-hero__intro">
                    <p class="badge">Poster Library</p>
                    <h2>Galeri Poster Program Bimbel</h2>
                    <p>Inspirasi materi promo berbasis target siswa dan omzet. Kurasi poster terbaik lalu bagikan ke tim marketing.</p>
                </div>
                <div class="product-hero__summary">
                    <article>
                        <span>Total Target Siswa</span>
                        <strong><?= format_number($totals['target_students']) ?></strong>
                        <p>Realisasi <?= format_number($totals['actual_students']) ?> siswa</p>
                        <div class="progress">
                            <span class="progress__bar" style="width: <?= progress_width(
                                $totals['target_students'] > 0
                                    ? ($totals['actual_students'] / max($totals['target_students'], 1)) * 100
                                    : 0
                            ) ?>%;"></span>
                        </div>
                    </article>
                    <article>
                        <span>Total Target Omzet</span>
                        <strong><?= format_rupiah($totals['target_revenue']) ?></strong>
                        <p>Realisasi <?= format_rupiah($totals['actual_revenue']) ?></p>
                        <div class="progress progress--accent">
                            <span class="progress__bar" style="width: <?= progress_width(
                                $totals['target_revenue'] > 0
                                    ? ($totals['actual_revenue'] / max($totals['target_revenue'], 1)) * 100
                                    : 0
                            ) ?>%;"></span>
                        </div>
                    </article>
                    <article>
                        <span>Produk Aktif</span>
                        <strong><?= format_number(count($products)) ?></strong>
                        <p>Pastikan visual dan copy selalu relevan.</p>
                    </article>
                </div>
            </section>

            <div class="product-tabs">
                <button class="product-tabs__item is-active">Trending di Bimbel</button>
                <button class="product-tabs__item">Template Video</button>
                <button class="product-tabs__item">Template Gambar</button>
                <button class="product-tabs__item">Favorit Tim</button>
            </div>

            <div class="product-controls card">
                <div class="product-controls__group">
                    <label for="filter-level">Jenjang</label>
                    <select id="filter-level">
                        <option value="">Semua Jenjang</option>
                        <?php foreach ($categoryLabels as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="product-controls__group">
                    <label for="filter-kpi">Fokus KPI</label>
                    <select id="filter-kpi">
                        <option value="">Semua KPI</option>
                        <option value="students">Siswa</option>
                        <option value="revenue">Omzet</option>
                        <option value="conversion">Konversi</option>
                    </select>
                </div>
                <div class="product-controls__group product-controls__group--inline">
                    <label for="filter-duration">Durasi Kampanye</label>
                    <div class="product-filter-pills">
                        <button class="is-active">Sprint</button>
                        <button>1 Bulan</button>
                        <button>Quartal</button>
                    </div>
                </div>
                <div class="product-controls__search">
                    <input type="search" placeholder="Cari poster, program, atau kode promo...">
                </div>
            </div>

            <section class="poster-grid">
                <?php if (empty($products)): ?>
                <div class="product-gallery__empty card">
                    <p>Belum ada program yang ditampilkan. Tambahkan program bimbel terlebih dahulu.</p>
                </div>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <?php
                    $posterPath = $product['image_path'] ? asset(ltrim($product['image_path'], '/')) : null;
                    $categoryLabel = $categoryLabels[$product['class_category']] ?? $product['class_category'] ?? 'Program';
                    $studentPercent = $product['percent']['students'] ?? null;
                    $revenuePercent = $product['percent']['revenue'] ?? null;
                    $studentWidth = progress_width($studentPercent);
                    $revenueWidth = progress_width($revenuePercent);
                ?>
                <article class="poster-card">
                    <div class="poster-card__media">
                        <?php if ($posterPath): ?>
                        <img src="<?= htmlspecialchars($posterPath, ENT_QUOTES, 'UTF-8') ?>" alt="Poster <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                        <div class="product-card__poster-empty">
                            <span>Poster belum diunggah</span>
                        </div>
                        <?php endif; ?>
                        <span class="poster-card__badge"><?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="poster-card__tag"><?= htmlspecialchars($product['code'] ?: 'Tanpa Kode', ENT_QUOTES, 'UTF-8') ?></span>
                        <div class="poster-card__meta">
                            <span>
                                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 3.333a4.667 4.667 0 1 1 0 9.334 4.667 4.667 0 0 1 0-9.334zm0 1.334a3.333 3.333 0 1 0 0 6.666 3.333 3.333 0 0 0 0-6.666zm0 1.333a.667.667 0 0 1 .667.667v1.666H10a.667.667 0 1 1 0 1.334H7.333V6.667A.667.667 0 0 1 8 5.999z" fill="currentColor"/></svg>
                                Target <?= format_number($product['targets']['students']) ?> siswa
                            </span>
                            <span>
                                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.333 1.333 5.333V6h1.334v6.667h10.666V6h1.334v-.667L8 1.333Zm0 1.154 4.666 2.8v.046H3.334v-.046l4.666-2.8ZM4.667 7.333h6.666v4H4.667v-4Z" fill="currentColor"/></svg>
                                Omzet <?= format_rupiah($product['targets']['revenue']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="poster-card__body">
                        <h3><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= nl2br(htmlspecialchars($product['description'] ?: 'Belum ada deskripsi program.', ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                    <div class="poster-card__stats">
                        <div>
                            <span>Siswa</span>
                            <strong><?= format_number($product['actual']['students']) ?></strong>
                            <small>dari <?= format_number($product['targets']['students']) ?> target</small>
                            <div class="progress">
                                <span class="progress__bar" style="width: <?= $studentWidth ?>%;"></span>
                            </div>
                            <p><?= format_percent($studentPercent) ?></p>
                        </div>
                        <div>
                            <span>Omzet</span>
                            <strong><?= format_rupiah($product['actual']['revenue']) ?></strong>
                            <small>dari <?= format_rupiah($product['targets']['revenue']) ?></small>
                            <div class="progress progress--accent">
                                <span class="progress__bar" style="width: <?= $revenueWidth ?>%;"></span>
                            </div>
                            <p><?= format_percent($revenuePercent) ?></p>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>

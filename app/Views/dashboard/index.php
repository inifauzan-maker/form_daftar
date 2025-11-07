<?php

use App\Core\Auth;

$user = isset($user) && is_array($user) ? $user : Auth::user();
$permissionSlugs = [];
if ($user) {
    if (isset($user['permission_slugs']) && is_array($user['permission_slugs'])) {
        $permissionSlugs = $user['permission_slugs'];
    } elseif (isset($user['permissions']) && is_array($user['permissions'])) {
        foreach ($user['permissions'] as $permission) {
            if (is_string($permission)) {
                $permissionSlugs[] = $permission;
            } elseif (is_array($permission) && isset($permission['slug'])) {
                $permissionSlugs[] = (string) $permission['slug'];
            }
        }
        $permissionSlugs = array_values(array_unique($permissionSlugs));
    }
}
$canManageUsers = Auth::can('manage_users');
$canExport = Auth::can('export_registrations');
$canUpdateStatus = Auth::can('update_registration_status');
$canViewInvoice = Auth::can('view_invoice');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($appName ?? 'SI VMI') . ' - Dashboard Peraihan', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha384-JsJn0xA4wNG0Ax7Anxr1j403rwhhtjfiPgk41IrT9jp+1rssZCvGSqt94FPdC6Au" crossorigin="">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard dashboard--analytics">
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
                <a href="<?= htmlspecialchars(route_path('/dashboard'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link is-active">
                    <span>Dashboard Peraihan</span>
                </a>
                <a href="<?= htmlspecialchars(route_path('/programs'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Program Bimbel</span>
                </a>
                <?php if ($canManageUsers): ?>
                <a href="<?= htmlspecialchars(route_path('/users'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Manajemen Pengguna</span>
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="dashboard__main dashboard__main--analytics">
            <header class="dashboard__header">
                <div>
                    <h1>Laporan Peraihan Target</h1>
                    <p>Monitor pencapaian jumlah siswa dan omzet bimbel secara real-time.</p>
                </div>
                <div class="header-actions">
                    <?php if ($canExport): ?>
                    <a class="btn-secondary" href="<?= htmlspecialchars(route_path('/dashboard/export'), ENT_QUOTES, 'UTF-8') ?>">
                        Export CSV
                    </a>
                    <?php endif; ?>
                    <button id="refresh-button" class="btn-secondary">Muat ulang</button>
                    <div class="header-account">
                        <div class="header-account__meta">
                            <strong><?= htmlspecialchars($user['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></strong>
                            <span><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <form method="POST" action="<?= htmlspecialchars(route_path('/logout'), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn-ghost btn-ghost--small">Keluar</button>
                        </form>
                    </div>
                </div>
            </header>

            <section class="dashboard-filters">
                <div class="filter-field">
                    <label for="filter-year">Tahun</label>
                    <select id="filter-year"></select>
                </div>
                <div class="filter-field">
                    <label for="filter-month">Bulan</label>
                    <select id="filter-month">
                        <option value="">Semua</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="filter-program">Program Bimbel</label>
                    <select id="filter-program">
                        <option value="">Semua Program</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="filter-branch">Lokasi Cabang</label>
                    <select id="filter-branch">
                        <option value="">Semua Cabang</option>
                    </select>
                </div>
            </section>

            <section class="dashboard-summary">
                <article class="summary-card" data-summary="students">
                    <header>
                        <div>
                            <span class="summary-card__label">Peraihan Siswa</span>
                            <p class="summary-card__hint">Total pendaftar aktif sesuai filter saat ini.</p>
                        </div>
                        <span class="summary-card__target" id="summary-students-target">Target: -</span>
                    </header>
                    <div class="summary-card__value" id="summary-students-total">0</div>
                    <div class="summary-card__meta">
                        <span id="summary-students-paid">0 siswa telah membayar</span>
                    </div>
                    <div class="summary-card__progress">
                        <div class="summary-card__progress-bar" id="summary-students-progress" style="width: 0%;"></div>
                    </div>
                    <footer>
                        <span id="summary-students-progress-label">0% dari target</span>
                    </footer>
                </article>
                <article class="summary-card summary-card--accent" data-summary="revenue">
                    <header>
                        <div>
                            <span class="summary-card__label">Peraihan Omzet</span>
                            <p class="summary-card__hint">Akumulasi pembayaran bersih siswa.</p>
                        </div>
                        <span class="summary-card__target" id="summary-revenue-target">Target: -</span>
                    </header>
                    <div class="summary-card__value" id="summary-revenue-actual">Rp0</div>
                    <div class="summary-card__meta summary-card__meta--two">
                        <span id="summary-revenue-expected">Tagihan: Rp0</span>
                        <span id="summary-revenue-average">Rata-rata: Rp0 / siswa</span>
                    </div>
                    <div class="summary-card__progress">
                        <div class="summary-card__progress-bar" id="summary-revenue-progress" style="width: 0%;"></div>
                    </div>
                    <footer>
                        <span id="summary-revenue-progress-label">0% dari target</span>
                    </footer>
                </article>
                <article class="summary-card summary-card--muted" data-summary="discount">
                    <header>
                        <div>
                            <span class="summary-card__label">Diskon & Potongan</span>
                            <p class="summary-card__hint">Total nilai potongan dari seluruh siswa.</p>
                        </div>
                    </header>
                    <div class="summary-card__value" id="summary-discount-total">Rp0</div>
                    <div class="summary-card__meta">
                        <span id="summary-revenue-difference">Selisih target vs aktual: Rp0</span>
                    </div>
                </article>
            </section>

            <section class="dashboard-charts">
                <article class="chart-card chart-card--wide">
                    <header>
                        <div>
                            <h2>Akumulasi Per Bulan</h2>
                            <p>Memperlihatkan tren kumulatif siswa dan omzet sepanjang tahun.</p>
                        </div>
                        <span id="chart-monthly-caption" class="chart-card__caption"></span>
                    </header>
                    <div class="chart-card__canvas chart-card__canvas--wide">
                        <canvas id="chart-monthly" aria-label="Akumulasi per bulan" role="img"></canvas>
                    </div>
                </article>
                <article class="chart-card">
                    <header>
                        <div>
                            <h2>Perbandingan Tahunan</h2>
                            <p>Jumlah siswa dan omzet setiap tahun ajaran.</p>
                        </div>
                    </header>
                    <div class="chart-card__canvas">
                        <canvas id="chart-yearly" aria-label="Perbandingan tahunan" role="img"></canvas>
                    </div>
                </article>
                <article class="chart-card">
                    <header>
                        <div>
                            <h2>Perbandingan Cabang</h2>
                            <p>Distribusi performa per lokasi cabang bimbel.</p>
                        </div>
                    </header>
                    <div class="chart-card__canvas">
                        <canvas id="chart-branch" aria-label="Perbandingan cabang" role="img"></canvas>
                    </div>
                </article>
                <article class="chart-card">
                    <header>
                        <div>
                            <h2>Perbandingan Program</h2>
                            <p>Kontribusi omzet dan siswa per program bimbel.</p>
                        </div>
                    </header>
                    <div class="chart-card__canvas">
                        <canvas id="chart-program" aria-label="Perbandingan program" role="img"></canvas>
                    </div>
                </article>
            </section>

            <section class="dashboard-map card">
                <header class="dashboard-map__header">
                    <div>
                        <h2>Peta Sebaran Pendaftar</h2>
                        <p>Pantau konsentrasi siswa berdasarkan provinsi dan kabupaten/kota.</p>
                    </div>
                    <div class="map-filters">
                        <label>
                            <span>Provinsi</span>
                            <select id="map-filter-province">
                                <option value="">Semua Provinsi</option>
                            </select>
                        </label>
                        <label>
                            <span>Kabupaten/Kota</span>
                            <select id="map-filter-city" disabled>
                                <option value="">Semua Kabupaten/Kota</option>
                            </select>
                        </label>
                    </div>
                </header>
                <div id="registrant-map" class="map-container" role="img" aria-label="Peta penyebaran pendaftar"></div>
                <div class="map-summary" id="map-summary"></div>
            </section>

            <section class="dashboard-forecast">
                <article class="forecast-card">
                    <header>
                        <div>
                            <span class="forecast-card__label">Proyeksi Pencapaian</span>
                            <h2 id="forecast-year">Tahun Berikutnya</h2>
                        </div>
                        <p class="forecast-card__hint">Proyeksi dihitung dari tren pertumbuhan historis.</p>
                    </header>
                    <div class="forecast-card__grid">
                        <div class="forecast-card__item">
                            <h3>Siswa</h3>
                            <p>
                                <span id="forecast-students-current">0</span>
                                &rarr;
                                <strong id="forecast-students-projected">0</strong>
                            </p>
                            <span id="forecast-students-growth" class="forecast-card__growth is-neutral">0%</span>
                        </div>
                        <div class="forecast-card__item">
                            <h3>Omzet</h3>
                            <p>
                                <span id="forecast-revenue-current">Rp0</span>
                                &rarr;
                                <strong id="forecast-revenue-projected">Rp0</strong>
                            </p>
                            <span id="forecast-revenue-growth" class="forecast-card__growth is-neutral">0%</span>
                        </div>
                    </div>
                </article>
                <div class="dashboard-note">
                    <strong>Catatan:</strong>
                    <span>Data dan proyeksi menyesuaikan filter yang dipilih. Gunakan filter tahun dan cabang untuk melihat performa spesifik.</span>
                </div>
            </section>

            <section class="stats-grid">
                <div class="stats-card">
                    <span class="stats-label">Total Pendaftar</span>
                    <strong id="stat-total">0</strong>
                </div>
                <div class="stats-card">
                    <span class="stats-label">Aktif</span>
                    <strong id="stat-active">0</strong>
                </div>
                <div class="stats-card">
                    <span class="stats-label">Lunas</span>
                    <strong id="stat-paid">0</strong>
                </div>
                <div class="stats-card">
                    <span class="stats-label">Belum Bayar</span>
                    <strong id="stat-unpaid">0</strong>
                </div>
                <div class="stats-card">
                    <span class="stats-label">Program Populer</span>
                    <strong id="stat-program-name">-</strong>
                    <span class="stats-sub" id="stat-program-count">0 pendaftar</span>
                </div>
            </section>

            <section class="card table-card">
                <div class="table-toolbar">
                    <div class="filter-group">
                        <label>
                            <span>Filter Status Siswa</span>
                            <select id="filter-student">
                                <option value="">Semua</option>
                                <option value="pending">Pending</option>
                                <option value="active">Aktif</option>
                                <option value="graduated">Lulus</option>
                                <option value="dropped">Berhenti</option>
                            </select>
                        </label>
                        <label>
                            <span>Status Pembayaran</span>
                            <select id="filter-payment">
                                <option value="">Semua</option>
                                <option value="unpaid">Belum bayar</option>
                                <option value="partial">Cicil</option>
                                <option value="paid">Lunas</option>
                            </select>
                        </label>
                        <label>
                            <span>Lokasi Belajar</span>
                            <select id="filter-location">
                                <option value="">Semua</option>
                                <option value="Bandung">Bandung</option>
                                <option value="Jaksel">Jaksel</option>
                                <option value="Jaktim">Jaktim</option>
                            </select>
                        </label>
                    </div>
                    <div class="search-box">
                        <input type="search" id="search-input" placeholder="Cari nama, sekolah, program, atau nomor...">
                    </div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Sekolah</th>
                                <th>Program</th>
                                <th>Status Siswa</th>
                                <th>Status Pembayaran</th>
                                <th>Lokasi</th>
                                <th>No. Registrasi</th>
                                <th>No. Invoice</th>
                                <th>Pembayaran</th>
                                <th>Dibuat</th>
                                <?php if ($canViewInvoice): ?>
                                <th>Invoice</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="registration-table">
                            <tr>
                                <td colspan="<?= $canViewInvoice ? 11 : 10 ?>" class="empty-state">Memuat data pendaftar...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="drawer" class="drawer" aria-hidden="true">
        <div class="drawer__content">
            <header class="drawer__header">
                <div>
                    <h2 id="drawer-title">Detail Pendaftar</h2>
                    <p>Perbarui status siswa dan progres pembayaran.</p>
                </div>
                <button id="drawer-close" class="drawer__close" aria-label="Tutup">x</button>
            </header>

            <div class="drawer__body" id="drawer-body"></div>

            <footer class="drawer__footer">
                <button id="drawer-save" class="btn-primary<?= $canUpdateStatus ? '' : ' is-hidden' ?>" <?= $canUpdateStatus ? '' : 'disabled' ?>>Simpan Perubahan</button>
                <button id="drawer-cancel" class="btn-ghost">Batalkan</button>
            </footer>
        </div>
    </div>

    <script>
        window.APP_BASE_PATH = '<?= htmlspecialchars(base_path(), ENT_QUOTES, 'UTF-8') ?>';
        window.APP_PERMISSION_SLUGS = <?= json_encode($permissionSlugs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        window.APP_CAN_UPDATE_STATUS = <?= $canUpdateStatus ? 'true' : 'false' ?>;
        window.APP_CAN_VIEW_INVOICE = <?= $canViewInvoice ? 'true' : 'false' ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha384-tAGcCfawKKrkaI0s5momkGLumZ5qX6Ch12HaxDTXiiMHDL/95B2S/bR5fes2w4i4" crossorigin=""></script>
    <script src="<?= htmlspecialchars(asset('assets/js/dashboard.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <script src="<?= htmlspecialchars(asset('assets/js/dashboard-analytics.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <script src="<?= htmlspecialchars(asset('assets/js/dashboard-map.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>

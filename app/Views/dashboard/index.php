<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($appName ?? 'SI VMI') . ' · Dashboard Pendaftar', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard">
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
                    <span>Dashboard Pendaftar</span>
                </a>
            </nav>
        </aside>

        <main class="dashboard__main">
            <header class="dashboard__header">
                <div>
                    <h1>Dashboard Pendaftar</h1>
                    <p>Pantau status siswa dan proses pembayaran dalam satu tempat.</p>
                </div>
                <div class="header-actions">
                    <button id="refresh-button" class="btn-secondary">Muat ulang</button>
                </div>
            </header>

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
                    </div>
                    <div class="search-box">
                        <input type="search" id="search-input" placeholder="Cari nama, sekolah, atau program...">
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
                                <th>Pembayaran</th>
                                <th>Dibuat</th>
                            </tr>
                        </thead>
                        <tbody id="registration-table">
                            <tr>
                                <td colspan="7" class="empty-state">Memuat data pendaftar...</td>
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
                <button id="drawer-close" class="drawer__close" aria-label="Tutup">×</button>
            </header>

            <div class="drawer__body" id="drawer-body"></div>

            <footer class="drawer__footer">
                <button id="drawer-save" class="btn-primary">Simpan Perubahan</button>
                <button id="drawer-cancel" class="btn-ghost">Batalkan</button>
            </footer>
        </div>
    </div>

    <script>
        window.APP_BASE_PATH = '<?= htmlspecialchars(base_path(), ENT_QUOTES, 'UTF-8') ?>';
    </script>
    <script src="<?= htmlspecialchars(asset('assets/js/dashboard.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>


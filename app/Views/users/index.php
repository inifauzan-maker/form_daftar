<?php

use App\Core\Auth;

$user = isset($user) && is_array($user) ? $user : Auth::user();
$permissions = isset($user['permissions']) && is_array($user['permissions']) ? $user['permissions'] : [];
$canManageRoles = in_array('manage_roles', $permissions, true);
$canManagePermissions = in_array('manage_permissions', $permissions, true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($appName ?? 'SI VMI') . ' - Manajemen Pengguna', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard dashboard--users">
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
                    <span>Dashboard Pendaftar</span>
                </a>
                <a href="<?= htmlspecialchars(route_path('/programs'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Program Bimbel</span>
                </a>
                <a href="<?= htmlspecialchars(route_path('/users'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link is-active">
                    <span>Manajemen Pengguna</span>
                </a>
            </nav>
        </aside>

        <main class="dashboard__main">
            <header class="dashboard__header">
                <div>
                    <h1>Manajemen Pengguna</h1>
                    <p>Atur akun tim, peran, dan izin akses ke fitur aplikasi.</p>
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

            <section class="card management-card">
                <header class="management-card__header">
                    <div>
                        <h2>Pengguna</h2>
                        <p>Kelola akun pengguna dan atur peran atau izin spesifik.</p>
                    </div>
                    <button id="user-create-new" class="btn-secondary">Pengguna Baru</button>
                </header>

                <div class="management-grid">
                    <div class="management-panel">
                        <div class="management-panel__header">
                            <h3>Daftar Pengguna</h3>
                            <input type="search" id="user-search" placeholder="Cari nama atau email...">
                        </div>
                        <div class="table-wrapper table-wrapper--compact">
                            <table id="user-table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Peran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="empty-state">Memuat data pengguna...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="management-form">
                        <h3 id="user-form-title">Tambah Pengguna</h3>
                        <form id="user-form">
                            <input type="hidden" name="id" id="user-id">

                            <label>
                                <span>Nama Lengkap</span>
                                <input type="text" id="user-name" name="name" placeholder="Nama lengkap pengguna" required>
                                <small class="field-error" data-error-for="name"></small>
                            </label>

                            <label>
                                <span>Email</span>
                                <input type="email" id="user-email" name="email" placeholder="email@contoh.id" required>
                                <small class="field-error" data-error-for="email"></small>
                            </label>

                            <label>
                                <span>Kata Sandi</span>
                                <input type="password" id="user-password" name="password" placeholder="Minimal 6 karakter">
                                <small class="field-help">Kosongkan jika tidak ingin mengubah kata sandi.</small>
                                <small class="field-error" data-error-for="password"></small>
                            </label>

                            <label>
                                <span>Status</span>
                                <select id="user-status" name="status">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                                <small class="field-error" data-error-for="status"></small>
                            </label>

                            <div class="fieldset">
                                <span>Peran</span>
                                <div id="user-roles" class="checkbox-grid"></div>
                            </div>

                            <div class="fieldset">
                                <span>Izin Langsung</span>
                                <div id="user-permissions" class="checkbox-grid"></div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary btn-primary--full">Simpan</button>
                                <button type="button" id="user-reset" class="btn-ghost btn-ghost--full">Reset</button>
                                <button type="button" id="user-delete" class="btn-danger btn-danger--full is-hidden">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section class="card management-card">
                <header class="management-card__header">
                    <div>
                        <h2>Peran</h2>
                        <p>Kelompokkan izin ke dalam peran untuk mempermudah pengaturan akses.</p>
                    </div>
                </header>

                <div class="management-grid">
                    <div class="management-panel">
                        <div class="management-panel__header">
                            <h3>Daftar Peran</h3>
                        </div>
                        <div class="table-wrapper table-wrapper--compact">
                            <table id="role-table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Slug</th>
                                        <th>Izin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3" class="empty-state">Memuat data peran...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="management-form">
                        <?php if ($canManageRoles): ?>
                        <h3 id="role-form-title">Tambah Peran</h3>
                        <form id="role-form">
                            <input type="hidden" name="id" id="role-id">

                            <label>
                                <span>Nama Peran</span>
                                <input type="text" id="role-name" name="name" placeholder="Contoh: Administrator" required>
                                <small class="field-error" data-error-for="role-name"></small>
                            </label>

                            <label>
                                <span>Slug</span>
                                <input type="text" id="role-slug" name="slug" placeholder="Contoh: admin" required>
                                <small class="field-help">Gunakan huruf kecil tanpa spasi. Tanda hubung/garis bawah diperbolehkan.</small>
                                <small class="field-error" data-error-for="role-slug"></small>
                            </label>

                            <label>
                                <span>Deskripsi</span>
                                <textarea id="role-description" name="description" rows="3" placeholder="Ringkasan peran"></textarea>
                            </label>

                            <div class="fieldset">
                                <span>Izin untuk Peran</span>
                                <div id="role-permissions" class="checkbox-grid"></div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary btn-primary--full">Simpan</button>
                                <button type="button" id="role-reset" class="btn-ghost btn-ghost--full">Reset</button>
                                <button type="button" id="role-delete" class="btn-danger btn-danger--full is-hidden">Hapus</button>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="empty-state">
                            Anda tidak memiliki izin untuk mengelola peran.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="card management-card">
                <header class="management-card__header">
                    <div>
                        <h2>Izin</h2>
                        <p>Definisikan granularitas akses yang dapat diberikan ke peran atau pengguna.</p>
                    </div>
                </header>

                <div class="management-grid">
                    <div class="management-panel">
                        <div class="table-wrapper table-wrapper--compact">
                            <table id="permission-table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Slug</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3" class="empty-state">Memuat daftar izin...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="management-form">
                        <?php if ($canManagePermissions): ?>
                        <h3 id="permission-form-title">Tambah Izin</h3>
                        <form id="permission-form">
                            <input type="hidden" name="id" id="permission-id">

                            <label>
                                <span>Nama Izin</span>
                                <input type="text" id="permission-name" name="name" placeholder="Contoh: Melihat dashboard" required>
                                <small class="field-error" data-error-for="permission-name"></small>
                            </label>

                            <label>
                                <span>Slug</span>
                                <input type="text" id="permission-slug" name="slug" placeholder="Contoh: view_dashboard" required>
                                <small class="field-help">Gunakan huruf kecil dan garis bawah.</small>
                                <small class="field-error" data-error-for="permission-slug"></small>
                            </label>

                            <label>
                                <span>Deskripsi</span>
                                <textarea id="permission-description" name="description" rows="3" placeholder="Penjelasan singkat"></textarea>
                            </label>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary btn-primary--full">Simpan</button>
                                <button type="button" id="permission-reset" class="btn-ghost btn-ghost--full">Reset</button>
                                <button type="button" id="permission-delete" class="btn-danger btn-danger--full is-hidden">Hapus</button>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="empty-state">
                            Anda tidak memiliki izin untuk mengelola daftar izin.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        window.APP_BASE_PATH = '<?= htmlspecialchars(base_path(), ENT_QUOTES, 'UTF-8') ?>';
        window.APP_CAN_MANAGE_ROLES = <?= $canManageRoles ? 'true' : 'false' ?>;
        window.APP_CAN_MANAGE_PERMISSIONS = <?= $canManagePermissions ? 'true' : 'false' ?>;
        window.APP_CURRENT_USER_ID = <?= isset($user['id']) ? (int) $user['id'] : 'null' ?>;
    </script>
    <script src="<?= htmlspecialchars(asset('assets/js/users.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>

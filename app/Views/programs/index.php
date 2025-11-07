<?php

use App\Core\Auth;

$user = isset($user) && is_array($user) ? $user : Auth::user();
$programs = isset($programs) && is_array($programs) ? $programs : [];
$canManagePrograms = isset($canManagePrograms) ? (bool) $canManagePrograms : false;
$categories = isset($categories) && is_array($categories) ? $categories : ['SD_SMP', 'X_XI', 'XII'];

$totalStudents = 0;
$totalRevenue = 0.0;

foreach ($programs as $program) {
    $totalStudents += (int) ($program['target_students'] ?? 0);
    $totalRevenue += (float) ($program['target_revenue'] ?? 0);
}

function format_rupiah(float $value): string
{
    return 'Rp' . number_format($value, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($appName ?? 'SI VMI') . ' - Program Bimbel', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard dashboard--programs">
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
                <a href="<?= htmlspecialchars(route_path('/programs'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link is-active">
                    <span>Program Bimbel</span>
                </a>
                <a href="<?= htmlspecialchars(route_path('/users'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link">
                    <span>Manajemen Pengguna</span>
                </a>
            </nav>
        </aside>

        <main class="dashboard__main">
            <header class="dashboard__header">
                <div>
                    <h1>Program Bimbingan Belajar</h1>
                    <p>Kelola biaya pendaftaran, biaya pendidikan, dan target peraihan setiap program.</p>
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

            <?php if (!empty($flashStatus) && !empty($flashMessage)): ?>
            <div class="alert <?= $flashStatus === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>

            <?php if ($canManagePrograms): ?>
            <section class="card program-form-card">
                <header>
                    <h2 id="program-form-title">Tambah Program Bimbel</h2>
                    <p>Masukkan biaya pendaftaran, biaya pendidikan, serta target siswa per program.</p>
                </header>
                <form id="program-form" class="program-form" action="<?= htmlspecialchars(route_path('/programs/save'), ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="program-id">
                    <div class="program-form__grid">
                        <label>
                            <span>Kode Program</span>
                            <input type="text" name="code" id="program-code" placeholder="Contoh: 1112109" required>
                        </label>
                        <label>
                            <span>Nama Program</span>
                            <input type="text" name="name" id="program-name" placeholder="Contoh: SNBP" required>
                        </label>
                        <label>
                            <span>Kategori Kelas</span>
                            <select name="class_category" id="program-category" required>
                                <option value="">Pilih kategori</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>Biaya Pendaftaran</span>
                            <input type="text" name="registration_fee" id="program-registration-fee" placeholder="200000" required>
                        </label>
                        <label>
                            <span>Biaya Pendidikan</span>
                            <input type="text" name="tuition_fee" id="program-tuition-fee" placeholder="9000000" required>
                        </label>
                        <label>
                            <span>Target Siswa</span>
                            <input type="number" name="target_students" id="program-target-students" min="0" placeholder="100">
                        </label>
                        <label>
                            <span>Target Omzet (Rp)</span>
                            <input type="text" name="target_revenue" id="program-target-revenue" placeholder="336000000">
                        </label>
                    </div>
                    <label>
                        <span>Deskripsi Program</span>
                        <textarea name="description" id="program-description" rows="3" placeholder="Highlight program atau strategi pembelajaran."></textarea>
                    </label>
                    <label class="program-form__upload">
                        <span>Poster / Gambar Program</span>
                        <input type="file" name="image" id="program-image" accept=".jpg,.jpeg,.png,.webp">
                        <small>Opsional. Maksimal 2MB.</small>
                    </label>
                    <div class="program-form__actions">
                        <button type="submit" class="btn-primary" id="program-submit">Simpan Program</button>
                        <button type="button" class="btn-ghost" id="program-reset">Reset Form</button>
                    </div>
                </form>
            </section>
            <?php endif; ?>

            <section class="card program-table-card">
                <header>
                    <div>
                        <h2>Data Program</h2>
                        <p>Referensi dari sheet peraihan program bimbel.</p>
                    </div>
                    <div class="program-table-summary">
                        <div>
                            <span>Total Target Siswa</span>
                            <strong><?= number_format($totalStudents, 0, ',', '.') ?></strong>
                        </div>
                        <div>
                            <span>Total Target Omzet</span>
                            <strong><?= format_rupiah($totalRevenue) ?></strong>
                        </div>
                    </div>
                </header>
                <div class="table-wrapper table-wrapper--compact">
                    <table>
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Biaya</th>
                                <th>Target</th>
                                <th>Poster</th>
                                <?php if ($canManagePrograms): ?>
                                <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($programs)): ?>
                            <tr>
                                <td colspan="<?= $canManagePrograms ? 5 : 4 ?>" class="empty-state">Belum ada data program.</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($programs as $program): ?>
                            <tr>
                                <td>
                                    <div class="program-table__title">
                                        <strong><?= htmlspecialchars($program['name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= htmlspecialchars($program['code'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <?php if (!empty($program['class_category'])): ?>
                                    <div class="program-table__meta">
                                        Kategori: <?= htmlspecialchars($program['class_category'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($program['description'])): ?>
                                    <p class="program-table__description"><?= nl2br(htmlspecialchars($program['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="program-table__figures">
                                        <span>Daftar</span>
                                        <strong><?= format_rupiah((float) $program['registration_fee']) ?></strong>
                                    </div>
                                    <div class="program-table__figures">
                                        <span>Pendidikan</span>
                                        <strong><?= format_rupiah((float) $program['tuition_fee']) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <div class="program-table__figures">
                                        <span>Siswa</span>
                                        <strong><?= number_format((int) $program['target_students'], 0, ',', '.') ?></strong>
                                    </div>
                                    <div class="program-table__figures">
                                        <span>Omzet</span>
                                        <strong><?= format_rupiah((float) $program['target_revenue']) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($program['image_path'])): ?>
                                    <img src="<?= htmlspecialchars(asset($program['image_path']), ENT_QUOTES, 'UTF-8') ?>" alt="Poster <?= htmlspecialchars($program['name'], ENT_QUOTES, 'UTF-8') ?>" class="program-table__image">
                                    <?php else: ?>
                                    <span class="program-table__image--empty">Tidak ada gambar</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canManagePrograms): ?>
                                <td class="program-table__actions">
                                    <button type="button"
                                        class="btn-secondary btn-secondary--small"
                                        data-edit-program='<?= htmlspecialchars(json_encode($program, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>'>
                                        Edit
                                    </button>
                                    <form method="POST" action="<?= htmlspecialchars(route_path('/programs/delete'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Hapus program ini?');">
                                        <input type="hidden" name="id" value="<?= (int) $program['id'] ?>">
                                        <button type="submit" class="btn-ghost btn-ghost--small">Hapus</button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

<?php if ($canManagePrograms): ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('program-form');
        const resetButton = document.getElementById('program-reset');
        const submitButton = document.getElementById('program-submit');
        const title = document.getElementById('program-form-title');
        const imageInput = document.getElementById('program-image');

        const fields = {
            id: document.getElementById('program-id'),
            code: document.getElementById('program-code'),
            name: document.getElementById('program-name'),
            category: document.getElementById('program-category'),
            registrationFee: document.getElementById('program-registration-fee'),
            tuitionFee: document.getElementById('program-tuition-fee'),
            targetStudents: document.getElementById('program-target-students'),
            targetRevenue: document.getElementById('program-target-revenue'),
            description: document.getElementById('program-description'),
        };

        document.querySelectorAll('[data-edit-program]').forEach((button) => {
            button.addEventListener('click', () => {
                const payload = button.getAttribute('data-edit-program');
                if (!payload) {
                    return;
                }
                const data = JSON.parse(payload);
                fields.id.value = data.id || '';
                fields.code.value = data.code || '';
                fields.name.value = data.name || '';
                fields.category.value = data.class_category || '';
                fields.registrationFee.value = data.registration_fee || '';
                fields.tuitionFee.value = data.tuition_fee || '';
                fields.targetStudents.value = data.target_students || '';
                fields.targetRevenue.value = data.target_revenue || '';
                fields.description.value = data.description || '';

                imageInput.value = '';
                submitButton.textContent = 'Perbarui Program';
                title.textContent = 'Edit Program Bimbel';

                window.scrollTo({ top: form.offsetTop - 40, behavior: 'smooth' });
            });
        });

        resetButton.addEventListener('click', () => {
            form.reset();
            fields.id.value = '';
            fields.category.value = '';
            submitButton.textContent = 'Simpan Program';
            title.textContent = 'Tambah Program Bimbel';
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="error-page">
    <main class="error-shell">
        <div class="error-card">
            <span class="error-badge">403</span>
            <h1>Akses Ditolak</h1>
            <p><?= htmlspecialchars($message ?? 'Anda tidak memiliki hak akses ke halaman ini.', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="error-actions">
                <a class="btn-primary" href="<?= htmlspecialchars(route_path('/'), ENT_QUOTES, 'UTF-8') ?>">Kembali ke Beranda</a>
                <a class="btn-ghost" href="<?= htmlspecialchars(route_path('/login'), ENT_QUOTES, 'UTF-8') ?>">Masuk dengan Akun Lain</a>
            </div>
        </div>
    </main>
</body>
</html>

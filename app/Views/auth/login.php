<?php

$errors = isset($errors) && is_array($errors) ? $errors : [];
$emailValue = isset($email) ? htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8') : '';
$redirectTarget = isset($redirect) ? htmlspecialchars((string) $redirect, ENT_QUOTES, 'UTF-8') : htmlspecialchars(route_path('/dashboard'), ENT_QUOTES, 'UTF-8');

$generalErrors = [];
foreach (['credentials', 'general'] as $key) {
    if (!empty($errors[$key])) {
        $generalErrors = array_merge($generalErrors, (array) $errors[$key]);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($appName ?? 'SI VMI') . ' - Masuk', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-card__header">
                <span class="badge">SI VMI</span>
                <h1>Masuk ke Panel Admin</h1>
                <p>Akses dashboard, kelola pendaftar, dan atur hak akses tim Anda.</p>
            </div>

            <?php if (!empty($generalErrors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($generalErrors as $message): ?>
                    <li><?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= htmlspecialchars(route_path('/login'), ENT_QUOTES, 'UTF-8') ?>" class="auth-form">
                <input type="hidden" name="redirect" value="<?= $redirectTarget ?>">

                <label class="auth-field">
                    <span>Alamat Email</span>
                    <input
                        type="email"
                        name="email"
                        autocomplete="username"
                        value="<?= $emailValue ?>"
                        required
                    >
                    <?php if (!empty($errors['email'])): ?>
                    <small class="field-error"><?= htmlspecialchars((string) $errors['email'][0], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </label>

                <label class="auth-field">
                    <span>Kata Sandi</span>
                    <input
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                    <?php if (!empty($errors['password'])): ?>
                    <small class="field-error"><?= htmlspecialchars((string) $errors['password'][0], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </label>

                <button type="submit" class="btn-primary btn-primary--full">Masuk</button>
            </form>
        </section>
    </main>
</body>
</html>


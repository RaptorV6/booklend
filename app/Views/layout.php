<?php clearFlash(); ?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'BookLend') ?> - BookLend</title>

    <!-- SEO Meta -->
    <meta name="description" content="<?= e($description ?? 'Moderní online půjčovna knih') ?>">
    <meta name="keywords" content="půjčovna knih, knihy online, book lending">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="<?= BASE_URL ?>/" class="logo">📚 BookLend</a>

            <div class="nav-links">
                <a href="<?= BASE_URL ?>/">Katalog</a>

                <?php if (app\Auth::check()): ?>
                    <a href="<?= BASE_URL ?>/profil">Profil</a>
                    <a href="<?= BASE_URL ?>/moje-vypujcky">Výpůjčky</a>
                    <form method="POST" action="<?= BASE_URL ?>/logout" style="display: inline;">
                        <button type="submit" class="btn-link">Odhlásit</button>
                    </form>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login">Přihlásit</a>
                    <a href="<?= BASE_URL ?>/register">Registrovat</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= e($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> BookLend. Školní projekt.</p>
        </div>
    </footer>

    <!-- JS -->
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/ajax.js"></script>
</body>
</html>

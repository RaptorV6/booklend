<?php clearFlash(); ?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'BookLend') ?> - BookLend</title>

    <!-- SEO Meta -->
    <meta name="description" content="<?= e($description ?? 'Moderní online půjčovna knih') ?>">
    <?= $seo_tags ?? '' ?>

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%2280%22 font-size=%2280%22>📚</text></svg>">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/toast.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/book-loading.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="<?= BASE_URL ?>/" class="logo">📚 BookLend</a>

            <!-- Hamburger Button (Mobile) -->
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- Nav Links -->
            <div class="nav-links" id="nav-links">
                <a href="<?= BASE_URL ?>/">Katalog</a>

                <?php if (app\Auth::check()): ?>
                    <!-- User Dropdown -->
                    <div class="user-menu">
                        <button class="user-avatar" id="user-avatar" aria-label="Uživatelské menu">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                                <path d="M6.168 18.849A4 4 0 0 1 10 16h4a4 4 0 0 1 3.834 2.855" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div class="user-dropdown" id="user-dropdown">
                            <div class="user-info">
                                <strong><?= e(app\Auth::user()['username'] ?? 'Uživatel') ?></strong>
                                <small><?= e(app\Auth::user()['email'] ?? '') ?></small>
                            </div>
                            <hr>
                            <a href="<?= BASE_URL ?>/profil">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Profil
                            </a>
                            <a href="<?= BASE_URL ?>/moje-vypujcky">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Výpůjčky
                            </a>
                            <?php if (app\Auth::isAdmin()): ?>
                                <hr>
                                <a href="<?= BASE_URL ?>/admin" style="color: #3b82f6;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                                        <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                                        <rect x="14" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                                        <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    Admin
                                </a>
                            <?php endif; ?>
                            <hr>
                            <form method="POST" action="<?= BASE_URL ?>/logout">
                                <button type="submit" class="logout-btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Odhlásit
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <button class="btn-login" id="open-auth-modal">Přihlásit</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> BookLend. Školní projekt.</p>
        </div>
    </footer>

    <!-- Auth Modal -->
    <div class="modal-overlay" id="auth-modal">
        <div class="modal-content auth-modal-content">
            <div class="modal-header">
                <h2 id="auth-modal-title">Přihlášení</h2>
                <button class="modal-close" id="close-auth-modal" aria-label="Zavřít">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <!-- Login Form -->
                <form id="login-form" class="auth-form-modal" style="display: block;">
                    <div class="form-group">
                        <label for="modal-login">Email nebo uživatelské jméno</label>
                        <input type="text" id="modal-login" name="login" required>
                        <span class="error" id="login-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="modal-login-password">Heslo</label>
                        <input type="password" id="modal-login-password" name="password" required>
                        <span class="error" id="login-password-error"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Přihlásit se</button>

                    <p class="auth-toggle">
                        Nemáte účet? <a href="#" id="show-register">Zaregistrujte se</a>
                    </p>
                </form>

                <!-- Register Form -->
                <form id="register-form" class="auth-form-modal" style="display: none;">
                    <div class="form-group">
                        <label for="modal-username">Uživatelské jméno</label>
                        <input type="text" id="modal-username" name="username" required>
                        <span class="error" id="register-username-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="modal-email">Email</label>
                        <input type="email" id="modal-email" name="email" required>
                        <span class="error" id="register-email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="modal-register-password">Heslo (min. 6 znaků)</label>
                        <input type="password" id="modal-register-password" name="password" required>
                        <span class="error" id="register-password-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="modal-password-confirm">Potvrdit heslo</label>
                        <input type="password" id="modal-password-confirm" name="password_confirm" required>
                        <span class="error" id="register-password-confirm-error"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Zaregistrovat se</button>

                    <p class="auth-toggle">
                        Už máte účet? <a href="#" id="show-login">Přihlaste se</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        // Set BASE_URL for JavaScript
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>/assets/js/toast.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/ajax.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/isbn-formatter.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/auth-modal.js"></script>

    <script>
        // Hamburger Menu Toggle
        document.getElementById('hamburger')?.addEventListener('click', function() {
            this.classList.toggle('active');
            document.getElementById('nav-links').classList.toggle('active');
        });

        // User Dropdown Toggle
        document.getElementById('user-avatar')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('user-dropdown').classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('user-dropdown');
            const avatar = document.getElementById('user-avatar');

            if (dropdown && !dropdown.contains(e.target) && e.target !== avatar) {
                dropdown.classList.remove('active');
            }
        });

        // Show toast for session messages
        <?php if (isset($_SESSION['success'])): ?>
            window.toast.success('<?= addslashes($_SESSION['success']) ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            window.toast.error('<?= addslashes($_SESSION['error']) ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>

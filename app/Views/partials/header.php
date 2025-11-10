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
                            <strong><?= htmlspecialchars(app\Auth::user()['username'] ?? 'Uživatel') ?></strong>
                            <small><?= htmlspecialchars(app\Auth::user()['email'] ?? '') ?></small>
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
                <a href="<?= BASE_URL ?>/login">Přihlásit</a>
                <a href="<?= BASE_URL ?>/register">Registrovat</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

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
</script>

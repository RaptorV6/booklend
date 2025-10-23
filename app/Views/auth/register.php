<?php ob_start(); ?>

<div class="container">
    <div class="auth-form">
        <h1>Registrace</h1>

        <form method="POST" action="<?= BASE_URL ?>/register">
            <div class="form-group">
                <label for="username">Uživatelské jméno</label>
                <input type="text" id="username" name="username" value="<?= old('username') ?>" required>
                <?php if ($err = error('username')): ?>
                    <span class="error"><?= e($err) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
                <?php if ($err = error('email')): ?>
                    <span class="error"><?= e($err) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Heslo (min. 6 znaků)</label>
                <input type="password" id="password" name="password" required>
                <?php if ($err = error('password')): ?>
                    <span class="error"><?= e($err) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">Potvrdit heslo</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
                <?php if ($err = error('password_confirm')): ?>
                    <span class="error"><?= e($err) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Zaregistrovat se</button>
        </form>

        <p class="auth-link">
            Už máte účet? <a href="<?= BASE_URL ?>/login">Přihlaste se</a>
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

<?php ob_start(); ?>

<div class="container">
    <div class="auth-form">
        <h1>Přihlášení</h1>

        <form method="POST" action="<?= BASE_URL ?>/login">
            <div class="form-group">
                <label for="login">Email nebo uživatelské jméno</label>
                <input type="text" id="login" name="login" value="<?= old('login') ?>" required>
                <?php if ($err = error('login')): ?>
                    <span class="error"><?= e($err) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Heslo</label>
                <input type="password" id="password" name="password" required>
                <?php if ($err = error('password')): ?>
                    <span class="error"><?= e($err) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Přihlásit se</button>
        </form>

        <p class="auth-link">
            Nemáte účet? <a href="<?= BASE_URL ?>/register">Zaregistrujte se</a>
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

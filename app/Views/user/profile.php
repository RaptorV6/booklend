<?php ob_start(); ?>

<div class="container">
    <h1 class="page-title">Můj profil</h1>

    <div class="profile-card">
        <div class="profile-info">
            <h2><?= e($user['username']) ?></h2>
            <p><strong>Email:</strong> <?= e($user['email']) ?></p>
            <p><strong>Role:</strong> <?= e($user['role']) ?></p>
            <p><strong>Registrace:</strong> <?= date('d.m.Y', strtotime($user['registered_at'])) ?></p>
        </div>

        <div class="profile-actions">
            <a href="<?= BASE_URL ?>/moje-vypujcky" class="btn btn-primary">Zobrazit výpůjčky</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

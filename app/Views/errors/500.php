<?php ob_start(); ?>

<div class="container">
    <div class="error-page">
        <h1 class="error-code">500</h1>
        <p class="error-message">Nastala chyba serveru</p>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary">Zpět na hlavní stránku</a>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = '500 - Chyba serveru';
require __DIR__ . '/../layout.php';
?>

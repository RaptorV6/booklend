<?php ob_start(); ?>

<div class="container">
    <div class="error-page">
        <h1 class="error-code">404</h1>
        <p class="error-message">Stránka nebyla nalezena</p>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary">Zpět na hlavní stránku</a>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = '404 - Nenalezeno';
require __DIR__ . '/../layout.php';
?>

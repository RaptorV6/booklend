<?php ob_start(); ?>

<div class="container">
    <h1 class="page-title">Katalog knih</h1>

    <!-- Search -->
    <div class="search-box">
        <input type="text" id="search-input" placeholder="Hledat knihu..." autocomplete="off">
        <div id="search-results"></div>
    </div>

    <!-- Book Grid -->
    <div class="book-grid">
        <?php foreach ($books as $book): ?>
            <div class="book-card">
                <a href="<?= BASE_URL ?>/kniha/<?= e($book['slug']) ?>">
                    <div class="book-cover" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        📖
                    </div>

                    <div class="book-info">
                        <h3 class="book-title"><?= e($book['title']) ?></h3>
                        <p class="book-author"><?= e($book['author']) ?></p>

                        <div class="book-meta">
                            <?php if ($book['available_copies'] > 0): ?>
                                <span class="badge badge-available">Dostupné (<?= $book['available_copies'] ?>)</span>
                            <?php else: ?>
                                <span class="badge badge-unavailable">Vypůjčeno</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

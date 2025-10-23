<?php ob_start(); ?>

<div class="container">
    <h1 class="page-title">Katalog knih</h1>

    <!-- Search -->
    <div class="search-box">
        <input type="text" id="search-input" placeholder="Hledat knihu..." autocomplete="off">
        <div id="search-results"></div>
    </div>

    <!-- Book Grid -->
    <div class="book-grid" id="book-grid">
        <?php foreach ($books as $book): ?>
            <div class="book-card">
                <a href="<?= BASE_URL ?>/kniha/<?= e($book['slug']) ?>">
                    <?php if (!empty($book['thumbnail'])): ?>
                        <div class="book-cover">
                            <img src="<?= e($book['thumbnail']) ?>" alt="<?= e($book['title']) ?>" onload="this.classList.add('loaded')" style="width: 100%; height: 280px; object-fit: cover;">
                        </div>
                    <?php else: ?>
                        <div class="book-cover" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            📖
                        </div>
                    <?php endif; ?>

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

    <!-- Loading indicator -->
    <div id="loading-more" style="display: none; text-align: center; padding: 2rem;">
        <p style="color: var(--text-muted);">Načítání dalších knih...</p>
    </div>

    <!-- No more books -->
    <div id="no-more-books" style="display: none; text-align: center; padding: 2rem;">
        <p style="color: var(--text-muted);">Zobrazeny všechny knihy</p>
    </div>
</div>

<script>
// Initialize infinite scroll
window.bookCatalogPage = 1;
window.bookCatalogHasMore = true;
window.bookCatalogLoading = false;
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

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

    <!-- Pagination -->
    <?php if ($pagination['totalPages'] > 1): ?>
        <div class="pagination">
            <!-- Previous button -->
            <?php if ($pagination['hasPrev']): ?>
                <a href="<?= BASE_URL ?>/?page=<?= $pagination['currentPage'] - 1 ?>" class="pagination-btn">
                    ← Předchozí
                </a>
            <?php else: ?>
                <span class="pagination-btn pagination-disabled">← Předchozí</span>
            <?php endif; ?>

            <!-- Page numbers -->
            <div class="pagination-pages">
                <?php
                $start = max(1, $pagination['currentPage'] - 2);
                $end = min($pagination['totalPages'], $pagination['currentPage'] + 2);

                // Show first page
                if ($start > 1): ?>
                    <a href="<?= BASE_URL ?>/?page=1" class="pagination-page">1</a>
                    <?php if ($start > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page range -->
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i === $pagination['currentPage']): ?>
                        <span class="pagination-page pagination-active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/?page=<?= $i ?>" class="pagination-page"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Show last page -->
                <?php if ($end < $pagination['totalPages']): ?>
                    <?php if ($end < $pagination['totalPages'] - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/?page=<?= $pagination['totalPages'] ?>" class="pagination-page"><?= $pagination['totalPages'] ?></a>
                <?php endif; ?>
            </div>

            <!-- Next button -->
            <?php if ($pagination['hasNext']): ?>
                <a href="<?= BASE_URL ?>/?page=<?= $pagination['currentPage'] + 1 ?>" class="pagination-btn">
                    Další →
                </a>
            <?php else: ?>
                <span class="pagination-btn pagination-disabled">Další →</span>
            <?php endif; ?>
        </div>

        <!-- Pagination info -->
        <div class="pagination-info">
            Zobrazeno <?= min($pagination['perPage'], $pagination['totalBooks']) ?> z <?= $pagination['totalBooks'] ?> knih
            (Stránka <?= $pagination['currentPage'] ?> z <?= $pagination['totalPages'] ?>)
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

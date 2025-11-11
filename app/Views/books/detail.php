<?php
// SEO proměnné
$title = e($book['title']) . ' – ' . e($book['author']);
$bookDesc = !empty($book['description']) ? substr($book['description'], 0, 150) . '...' : 'Detaily o knize dostupné po přihlášení.';
$description = 'Vypůjčte si knihu ' . e($book['title']) . ' od ' . e($book['author']) . '. ' . e($bookDesc);
$pageUrl = BASE_URL . '/kniha/' . e($book['slug']);
$coverUrl = e($book['thumbnail']);

// SEO tagy
ob_start();
?>
<!-- Canonical URL -->
<link rel="canonical" href="<?= $pageUrl ?>">

<!-- Open Graph & Twitter Cards -->
<meta property="og:type" content="book">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:image" content="<?= $coverUrl ?>">
<meta property="og:url" content="<?= $pageUrl ?>">
<meta name="twitter:card" content="summary_large_image">

<!-- Strukturovaná data (JSON-LD) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Book",
  "name": "<?= e($book['title']) ?>",
  "author": {
    "@type": "Person",
    "name": "<?= e($book['author']) ?>"
  },
  "isbn": "<?= e($book['isbn']) ?>",
  "image": "<?= $coverUrl ?>"
}
</script>
<?php
$seo_tags = ob_get_clean();

ob_start();
?>

<div class="container">
    <div class="book-detail">
        <div class="detail-cover">
            <?php if (!empty($book['thumbnail'])): ?>
                <img
                    src="<?= e($book['thumbnail']) ?>"
                    alt="<?= e($book['title']) ?>"
                    loading="eager"
                    onload="this.classList.add('loaded'); this.style.opacity='1'; const loader = this.parentElement.querySelector('.book-loading'); if(loader) loader.classList.add('hidden');"
                    style="width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.3s;"
                >
                <!-- Loading Animation -->
                <div class="book-loading">
                    <div class="book-loading-animation">
                        <div class="book-icon">
                            <div class="book-cover-left"></div>
                            <div class="book-pages">
                                <div class="page"></div>
                                <div class="page"></div>
                                <div class="page"></div>
                            </div>
                        </div>
                        <div class="book-loading-text">Načítání...</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="book-cover-large" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    📖
                </div>
            <?php endif; ?>
        </div>

        <div class="detail-info">
            <h1><?= e($book['title']) ?></h1>
            <h3 class="author"><?= e($book['author']) ?></h3>

            <?php if (!empty($book['description'])): ?>
                <p class="description"><?= nl2br(e($book['description'])) ?></p>
            <?php endif; ?>

            <div class="metadata">
                <p><strong>ISBN:</strong> <?= e($book['isbn']) ?></p>

                <?php if (!empty($book['published_date'])): ?>
                    <p><strong>Rok vydání:</strong> <?= e($book['published_date']) ?></p>
                <?php endif; ?>

                <?php if (!empty($book['page_count'])): ?>
                    <p><strong>Počet stran:</strong> <?= e($book['page_count']) ?></p>
                <?php endif; ?>

                <p><strong>Dostupnost:</strong> <span id="availability-count" data-available="<?= $book['available_copies'] ?>" data-total="<?= $book['total_copies'] ?>"><?= $book['available_copies'] ?> / <?= $book['total_copies'] ?></span></p>
            </div>

            <div class="actions" id="book-actions">
                <?php if (app\Auth::check()): ?>
                    <?php if ($isRented): ?>
                        <button class="btn btn-secondary" disabled>Již vypůjčeno</button>
                    <?php elseif ($book['available_copies'] > 0): ?>
                        <button class="btn btn-primary" id="rent-button" data-book-id="<?= $book['id'] ?>" onclick="rentBook(<?= $book['id'] ?>)">
                            Půjčit knihu
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Momentálně nedostupné</button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Přihlaste se pro půjčení</a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/" class="btn btn-outline">Zpět na katalog</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

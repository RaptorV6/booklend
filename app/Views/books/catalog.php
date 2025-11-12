<?php
// SEO tagy
$defaultImage = BASE_URL . '/assets/img/og-default.jpg'; // fallback OG image

// SEO BEST PRACTICE: Canonical URL logic for filter combinations
// Single filter = index it, Multiple values or combinations = canonical to simpler version
$canonicalUrl = BASE_URL . '/';

$hasGenre = !empty($currentFilters['genre']);
$hasYear = !empty($currentFilters['year']);
$multipleGenres = $hasGenre && count($currentFilters['genre']) > 1;
$multipleYears = $hasYear && count($currentFilters['year']) > 1;

if ($hasGenre && $hasYear) {
    // Combination of genre + year filters → canonical to homepage
    $canonicalUrl = BASE_URL . '/';
} elseif ($multipleGenres) {
    // Multiple genres → canonical to first genre only
    $canonicalUrl = BASE_URL . '/?zanr=' . urlencode($currentFilters['genre'][0]);
} elseif ($multipleYears) {
    // Multiple years → canonical to first year only
    $canonicalUrl = BASE_URL . '/?rok=' . $currentFilters['year'][0];
} elseif ($hasGenre && count($currentFilters['genre']) === 1) {
    // Single genre filter → canonical to self (indexable)
    $canonicalUrl = BASE_URL . '/?zanr=' . urlencode($currentFilters['genre'][0]);
} elseif ($hasYear && count($currentFilters['year']) === 1) {
    // Single year filter → canonical to self (indexable)
    $canonicalUrl = BASE_URL . '/?rok=' . $currentFilters['year'][0];
}
// else: no filters → homepage canonical (already set)

$pageUrl = $canonicalUrl; // For OG tags consistency

ob_start();
?>
<!-- Canonical URL (SEO best practice for filter combinations) -->
<link rel="canonical" href="<?= $canonicalUrl ?>">

<!-- Open Graph & Twitter Cards -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:image" content="<?= $defaultImage ?>">
<meta property="og:url" content="<?= $pageUrl ?>">
<meta name="twitter:card" content="summary_large_image">

<!-- Strukturovaná data (JSON-LD) - ItemList -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Katalog knih - BookLend",
  "description": "<?= e($description) ?>",
  "numberOfItems": <?= count($books) ?>,
  "itemListElement": [
    <?php foreach ($books as $index => $book): ?>
    {
      "@type": "ListItem",
      "position": <?= $index + 1 ?>,
      "item": {
        "@type": "Book",
        "name": "<?= e($book['title']) ?>",
        "author": {
          "@type": "Person",
          "name": "<?= e($book['author']) ?>"
        },
        "url": "<?= BASE_URL ?>/kniha/<?= e($book['slug']) ?>"
      }
    }<?= $index < count($books) - 1 ? ',' : '' ?>

    <?php endforeach; ?>
  ]
}
</script>
<?php
$seo_tags = ob_get_clean();

ob_start();
?>

<div class="container">
    <h1 class="page-title">Katalog knih</h1>

    <!-- Search & Filters -->
    <div class="catalog-controls">
        <!-- Search Box -->
        <div class="search-box">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <input type="text" id="search-input" placeholder="Hledat knihu..." autocomplete="off">
            <div id="search-results"></div>
        </div>

        <!-- Filter Chips -->
        <div class="filter-chips">
            <!-- Genre Filter Chip -->
            <div class="filter-chip-wrapper">
                <button class="filter-chip" id="genre-chip">
                    <span>Žánr</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="chip-badge" id="genre-badge" style="display: none;">0</span>
                </button>

                <!-- Genre Dropdown -->
                <div class="chip-dropdown" id="genre-dropdown">
                    <div class="chip-dropdown-header">
                        <h3>Vyberte žánry</h3>
                        <button class="chip-clear" data-filter="genre">Vymazat</button>
                    </div>
                    <div class="chip-dropdown-content">
                        <?php foreach ($genres as $g): ?>
                            <label class="chip-option">
                                <input type="checkbox" name="genres[]" value="<?= e($g['genre']) ?>" <?= (isset($currentFilters['genre']) && in_array($g['genre'], $currentFilters['genre'])) ? 'checked' : '' ?>>
                                <span><?= e($g['genre']) ?></span>
                                <span class="option-count"><?= $g['count'] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="chip-dropdown-footer">
                        <button class="chip-apply" data-filter="genre">Použít</button>
                    </div>
                </div>
            </div>

            <!-- Year Filter Chip -->
            <div class="filter-chip-wrapper">
                <button class="filter-chip" id="year-chip">
                    <span>Rok vydání</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="chip-badge" id="year-badge">0</span>
                </button>

                <div class="chip-dropdown" id="year-dropdown">
                    <div class="chip-dropdown-header">
                        <h3>Rok vydání</h3>
                        <button class="chip-clear" data-filter="year">Vymazat</button>
                    </div>
                    <div class="chip-dropdown-content">
                        <?php if (!empty($years)): ?>
                            <?php foreach ($years as $y): ?>
                                <label class="chip-option">
                                    <input type="checkbox" name="years[]" value="<?= $y['year'] ?>" <?= (isset($currentFilters['year']) && in_array((int)$y['year'], $currentFilters['year'])) ? 'checked' : '' ?>>
                                    <span><?= $y['year'] ?></span>
                                    <span class="option-count"><?= $y['count'] ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 12px; color: var(--text-muted); text-align: center;">Žádné roky k dispozici</p>
                        <?php endif; ?>
                    </div>
                    <div class="chip-dropdown-footer">
                        <button class="chip-apply" data-filter="year">Použít</button>
                    </div>
                </div>
            </div>

            <!-- Sort Filter Chip -->
            <div class="filter-chip-wrapper">
                <button class="filter-chip" id="sort-chip">
                    <span>Řadit podle</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="chip-dropdown" id="sort-dropdown">
                    <div class="chip-dropdown-header">
                        <h3>Seřadit podle</h3>
                    </div>
                    <div class="chip-dropdown-content">
                        <?php $currentSort = $currentFilters['sort'] ?? 'newest'; ?>
                        <label class="chip-option">
                            <input type="radio" name="sort" value="newest" <?= $currentSort === 'newest' ? 'checked' : '' ?>>
                            <span>Nejnovější</span>
                        </label>
                        <label class="chip-option">
                            <input type="radio" name="sort" value="oldest" <?= $currentSort === 'oldest' ? 'checked' : '' ?>>
                            <span>Nejstarší</span>
                        </label>
                        <label class="chip-option">
                            <input type="radio" name="sort" value="title-asc" <?= $currentSort === 'title-asc' ? 'checked' : '' ?>>
                            <span>Název (A-Z)</span>
                        </label>
                        <label class="chip-option">
                            <input type="radio" name="sort" value="title-desc" <?= $currentSort === 'title-desc' ? 'checked' : '' ?>>
                            <span>Název (Z-A)</span>
                        </label>
                        <label class="chip-option">
                            <input type="radio" name="sort" value="author-asc" <?= $currentSort === 'author-asc' ? 'checked' : '' ?>>
                            <span>Autor (A-Z)</span>
                        </label>
                        <label class="chip-option">
                            <input type="radio" name="sort" value="author-desc" <?= $currentSort === 'author-desc' ? 'checked' : '' ?>>
                            <span>Autor (Z-A)</span>
                        </label>
                        <label class="chip-option">
                            <input type="radio" name="sort" value="year-asc" <?= $currentSort === 'year-asc' ? 'checked' : '' ?>>
                            <span>Rok (vzestupně)</span>
                        </label>
                        <label class="chip-option">
                            <input type="radio" name="sort" value="year-desc" <?= $currentSort === 'year-desc' ? 'checked' : '' ?>>
                            <span>Rok (sestupně)</span>
                        </label>
                    </div>
                    <div class="chip-dropdown-footer">
                        <button class="chip-apply" data-filter="sort">Použít</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination Controls (inline with filters) -->
        <div id="pagination-controls"></div>

        <!-- Active Filters Display (separate row) -->
        <div class="active-filters" id="active-filters"></div>
    </div>

    <!-- Book Grid -->
    <div class="book-grid" id="book-grid">
        <?php foreach ($books as $book): ?>
            <div class="book-card">
                <a href="<?= BASE_URL ?>/kniha/<?= e($book['slug']) ?>">
                    <?php if (!empty($book['thumbnail'])): ?>
                        <div class="book-cover">
                            <img
                                src="<?= e($book['thumbnail']) ?>"
                                alt="<?= e($book['title']) ?>"
                                loading="lazy"
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
                        </div>
                    <?php else: ?>
                        <div class="book-cover book-cover-placeholder">
                            <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                                <path d="M16 8h24l8 8v40H16V8z" fill="currentColor" opacity="0.2"/>
                                <path d="M16 8h24l8 8v40H16V8z" stroke="currentColor" stroke-width="2"/>
                                <path d="M40 8v8h8" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <div class="book-info">
                        <h3 class="book-title"><?= e($book['title']) ?></h3>
                        <p class="book-author"><?= e($book['author']) ?></p>

                        <div class="book-meta">
                            <span class="badge badge-genre"><?= e($book['genre']) ?></span>
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

    <!-- Pagination (Bottom) -->
    <div id="pagination"></div>
</div>

<!-- Include Pagination CSS & JS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pagination.css">

<script src="<?= BASE_URL ?>/assets/js/pagination.js"></script>
<script>
// Filter Dropdown Controller
const FilterController = {
    init() {
        this.setupFilterDropdown();
    },

    setupFilterDropdown() {
        // Genre Chip
        const genreChip = document.getElementById('genre-chip');
        const genreDropdown = document.getElementById('genre-dropdown');
        const genreBadge = document.getElementById('genre-badge');
        const genreApplyBtn = genreDropdown?.querySelector('.chip-apply');
        const genreClearBtn = genreDropdown?.querySelector('.chip-clear');

        // Year Chip
        const yearChip = document.getElementById('year-chip');
        const yearDropdown = document.getElementById('year-dropdown');
        const yearBadge = document.getElementById('year-badge');
        const yearApplyBtn = yearDropdown?.querySelector('.chip-apply');
        const yearClearBtn = yearDropdown?.querySelector('.chip-clear');

        // Sort Chip
        const sortChip = document.getElementById('sort-chip');
        const sortDropdown = document.getElementById('sort-dropdown');
        const sortApplyBtn = sortDropdown?.querySelector('.chip-apply');

        // Toggle genre dropdown
        genreChip?.addEventListener('click', (e) => {
            e.stopPropagation();
            yearDropdown?.classList.remove('active');
            yearChip?.classList.remove('active');
            sortDropdown?.classList.remove('active');
            sortChip?.classList.remove('active');
            genreDropdown.classList.toggle('active');
            genreChip.classList.toggle('active');
        });

        // Toggle year dropdown
        yearChip?.addEventListener('click', (e) => {
            e.stopPropagation();
            genreDropdown?.classList.remove('active');
            genreChip?.classList.remove('active');
            sortDropdown?.classList.remove('active');
            sortChip?.classList.remove('active');
            yearDropdown.classList.toggle('active');
            yearChip.classList.toggle('active');
        });

        // Toggle sort dropdown
        sortChip?.addEventListener('click', (e) => {
            e.stopPropagation();
            genreDropdown?.classList.remove('active');
            genreChip?.classList.remove('active');
            yearDropdown?.classList.remove('active');
            yearChip?.classList.remove('active');
            sortDropdown.classList.toggle('active');
            sortChip.classList.toggle('active');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            const isChip = e.target.closest('.filter-chip');
            const isDropdown = e.target.closest('.chip-dropdown');

            if (!isChip && !isDropdown) {
                document.querySelectorAll('.chip-dropdown').forEach(d => d.classList.remove('active'));
                document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            }
        });

        // Apply genre filters
        genreApplyBtn?.addEventListener('click', () => this.applyFilters());

        // Clear genre filters
        genreClearBtn?.addEventListener('click', () => {
            genreDropdown.querySelectorAll('input[name="genres[]"]').forEach(cb => cb.checked = false);
            this.updateGenreBadge();
        });

        // Update badge on checkbox change
        genreDropdown?.querySelectorAll('input[name="genres[]"]').forEach(cb => {
            cb.addEventListener('change', () => this.updateGenreBadge());
        });

        // Initialize badge
        this.updateGenreBadge();

        // Apply year filters
        yearApplyBtn?.addEventListener('click', () => this.applyFilters());

        // Clear year filters
        yearClearBtn?.addEventListener('click', () => {
            yearDropdown.querySelectorAll('input[name="years[]"]').forEach(cb => cb.checked = false);
            this.updateYearBadge();
        });

        // Update year badge on checkbox change
        yearDropdown?.querySelectorAll('input[name="years[]"]').forEach(cb => {
            cb.addEventListener('change', () => this.updateYearBadge());
        });

        // Initialize year badge
        this.updateYearBadge();

        // Apply sort
        sortApplyBtn?.addEventListener('click', () => this.applyFilters());
    },

    applyFilters() {
        const url = new URL(window.location.href);
        url.search = '';

        // Genre filter - hyphen-separated
        const selectedGenres = Array.from(
            document.querySelectorAll('input[name="genres[]"]:checked')
        ).map(cb => cb.value);
        if (selectedGenres.length > 0) {
            url.searchParams.set('zanr', selectedGenres.join('-'));
        }

        // Year filter - hyphen-separated
        const selectedYears = Array.from(
            document.querySelectorAll('input[name="years[]"]:checked')
        ).map(cb => cb.value);
        if (selectedYears.length > 0) {
            url.searchParams.set('rok', selectedYears.join('-'));
        }

        // Sort filter
        const selectedSort = document.querySelector('input[name="sort"]:checked')?.value;
        if (selectedSort && selectedSort !== 'newest') {
            url.searchParams.set('sort', selectedSort);
        }

        window.location.href = url.toString();
    },

    updateGenreBadge() {
        const genreDropdown = document.getElementById('genre-dropdown');
        const genreBadge = document.getElementById('genre-badge');
        const selectedCount = genreDropdown?.querySelectorAll('input[name="genres[]"]:checked').length || 0;

        if (selectedCount > 0) {
            genreBadge.textContent = selectedCount;
            genreBadge.style.display = 'inline-block';
        } else {
            genreBadge.style.display = 'none';
        }
    },

    updateYearBadge() {
        const yearDropdown = document.getElementById('year-dropdown');
        const yearBadge = document.getElementById('year-badge');
        const selectedCount = yearDropdown?.querySelectorAll('input[name="years[]"]:checked').length || 0;

        if (selectedCount > 0) {
            yearBadge.textContent = selectedCount;
            yearBadge.style.display = 'inline-block';
        } else {
            yearBadge.style.display = 'none';
        }
    }
};

// Get current filters from URL
const currentUrl = new URL(window.location.href);
const filters = {};

const zanrValue = currentUrl.searchParams.get('zanr');
if (zanrValue) filters.zanr = zanrValue;

const rokValue = currentUrl.searchParams.get('rok');
if (rokValue) filters.rok = rokValue;

const sortValue = currentUrl.searchParams.get('sort');
if (sortValue) filters.sort = sortValue;

// Initialize Paginator
const paginator = new Paginator({
    apiEndpoint: '<?= BASE_URL ?>/api/books',
    containerSelector: '#book-grid',
    controlsSelector: '#pagination-controls',
    paginationSelector: '#pagination',
    defaultPerPage: 12,
    filters: filters,
    renderItem: (book) => {
        const thumbnail = book.thumbnail ? `
            <div class="book-cover">
                <img
                    src="${escapeHtml(book.thumbnail)}"
                    alt="${escapeHtml(book.title)}"
                    loading="lazy"
                    onload="this.classList.add('loaded'); this.style.opacity='1'; const loader = this.parentElement.querySelector('.book-loading'); if(loader) loader.classList.add('hidden');"
                    style="width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.3s;"
                >
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
            </div>
        ` : `
            <div class="book-cover book-cover-placeholder">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                    <path d="M16 8h24l8 8v40H16V8z" fill="currentColor" opacity="0.2"/>
                    <path d="M16 8h24l8 8v40H16V8z" stroke="currentColor" stroke-width="2"/>
                    <path d="M40 8v8h8" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
        `;

        const available = book.available_copies > 0
            ? `<span class="badge badge-available">Dostupné (${book.available_copies})</span>`
            : `<span class="badge badge-unavailable">Vypůjčeno</span>`;

        return `
            <div class="book-card">
                <a href="<?= BASE_URL ?>/kniha/${escapeHtml(book.slug)}">
                    ${thumbnail}
                    <div class="book-info">
                        <h3 class="book-title">${escapeHtml(book.title)}</h3>
                        <p class="book-author">${escapeHtml(book.author)}</p>
                        <div class="book-meta">
                            <span class="badge badge-genre">${escapeHtml(book.genre)}</span>
                            ${available}
                        </div>
                    </div>
                </a>
            </div>
        `;
    }
});

// Helper function
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    FilterController.init();
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

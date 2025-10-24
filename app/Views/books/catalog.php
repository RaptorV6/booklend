<?php ob_start(); ?>

<div class="container">
    <h1 class="page-title">Katalog knih</h1>

    <!-- Search Bar -->
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
                                <input type="checkbox" name="genres[]" value="<?= e($g['genre']) ?>" <?= ($genre === $g['genre']) ? 'checked' : '' ?>>
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

            <!-- Future: Date Filter Chip -->
            <!--
            <div class="filter-chip-wrapper">
                <button class="filter-chip" id="date-chip">
                    <span>Datum vydání</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="chip-dropdown" id="date-dropdown">
                    <div class="chip-dropdown-content">
                        <label class="chip-option">
                            <input type="checkbox" name="years[]" value="2024">
                            <span>2024</span>
                        </label>
                    </div>
                </div>
            </div>
            -->

            <!-- Active Filters Display -->
            <div class="active-filters" id="active-filters"></div>
        </div>
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
                                style="width: 100%; height: 280px; object-fit: cover; opacity: 0; transition: opacity 0.3s;"
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

    <!-- Loading Sentinel for Infinite Scroll -->
    <div id="loading-sentinel" style="height: 20px;"></div>

    <!-- Loading Indicator -->
    <div id="loading-more" class="loading-more" style="display: none;">
        <div class="book-loading-animation">
            <div class="book">
                <div class="book-left"></div>
                <div class="book-page"></div>
                <div class="book-page"></div>
                <div class="book-page"></div>
                <div class="book-right"></div>
            </div>
        </div>
        <p>Načítání dalších knih...</p>
    </div>

    <!-- No More Books -->
    <div id="no-more-books" style="display: none; text-align: center; padding: 40px; color: #94a3b8;">
        <p>To je všechno! Žádné další knihy.</p>
    </div>
</div>

<script>
// Lazy Loading Controller
const LazyLoadController = {
    currentPage: <?= $pagination['currentPage'] ?>,
    hasMore: <?= $pagination['hasNext'] ? 'true' : 'false' ?>,
    loading: false,
    currentGenre: '<?= $genre ?? '' ?>',

    init() {
        this.setupIntersectionObserver();
        this.setupFilterDropdown();
        this.setupImageLoading();
    },

    setupIntersectionObserver() {
        const sentinel = document.getElementById('loading-sentinel');
        const options = {
            root: null,
            rootMargin: '200px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && this.hasMore && !this.loading) {
                    this.loadMore();
                }
            });
        }, options);

        observer.observe(sentinel);
    },

    async loadMore() {
        if (this.loading || !this.hasMore) return;

        this.loading = true;
        document.getElementById('loading-more').style.display = 'flex';

        try {
            const nextPage = this.currentPage + 1;
            const url = `<?= BASE_URL ?>/api/books?page=${nextPage}&limit=12${this.currentGenre ? `&genre=${encodeURIComponent(this.currentGenre)}` : ''}`;

            const response = await fetch(url);
            const data = await response.json();

            if (data.books && data.books.length > 0) {
                this.appendBooks(data.books);
                this.currentPage = nextPage;
                this.hasMore = data.hasMore;

                if (!this.hasMore) {
                    document.getElementById('no-more-books').style.display = 'block';
                }
            } else {
                this.hasMore = false;
                document.getElementById('no-more-books').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading more books:', error);
            window.toast.error('Chyba při načítání knih');
        } finally {
            this.loading = false;
            document.getElementById('loading-more').style.display = 'none';
        }
    },

    appendBooks(books) {
        const grid = document.getElementById('book-grid');

        books.forEach(book => {
            const card = this.createBookCard(book);
            grid.appendChild(card);
        });

        // Re-setup image loading for new cards
        this.setupImageLoading();
    },

    createBookCard(book) {
        const card = document.createElement('div');
        card.className = 'book-card';

        const thumbnail = book.thumbnail ? `
            <div class="book-cover">
                <img
                    src="${this.escapeHtml(book.thumbnail)}"
                    alt="${this.escapeHtml(book.title)}"
                    loading="lazy"
                    onload="this.classList.add('loaded'); this.style.opacity='1'; const loader = this.parentElement.querySelector('.book-loading'); if(loader) loader.classList.add('hidden');"
                    style="width: 100%; height: 280px; object-fit: cover; opacity: 0; transition: opacity 0.3s;"
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

        card.innerHTML = `
            <a href="<?= BASE_URL ?>/kniha/${this.escapeHtml(book.slug)}">
                ${thumbnail}
                <div class="book-info">
                    <h3 class="book-title">${this.escapeHtml(book.title)}</h3>
                    <p class="book-author">${this.escapeHtml(book.author)}</p>
                    <div class="book-meta">
                        <span class="badge badge-genre">${this.escapeHtml(book.genre)}</span>
                        ${available}
                    </div>
                </div>
            </a>
        `;

        return card;
    },

    setupFilterDropdown() {
        // Genre Chip
        const genreChip = document.getElementById('genre-chip');
        const genreDropdown = document.getElementById('genre-dropdown');
        const genreBadge = document.getElementById('genre-badge');
        const genreApplyBtn = genreDropdown?.querySelector('.chip-apply');
        const genreClearBtn = genreDropdown?.querySelector('.chip-clear');

        // Toggle genre dropdown
        genreChip?.addEventListener('click', (e) => {
            e.stopPropagation();
            genreDropdown.classList.toggle('active');
            genreChip.classList.toggle('active');
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
        genreApplyBtn?.addEventListener('click', () => {
            const selectedGenres = Array.from(
                genreDropdown.querySelectorAll('input[name="genres[]"]:checked')
            ).map(cb => cb.value);

            // Build URL with filters (for now just use first genre)
            // TODO: Backend support for multiple genres
            const url = selectedGenres.length > 0
                ? `<?= BASE_URL ?>/?genre=${encodeURIComponent(selectedGenres[0])}`
                : '<?= BASE_URL ?>/';

            window.location.href = url;
        });

        // Clear genre filters
        genreClearBtn?.addEventListener('click', () => {
            genreDropdown.querySelectorAll('input[name="genres[]"]').forEach(cb => {
                cb.checked = false;
            });
            this.updateGenreBadge();
        });

        // Update badge on checkbox change
        genreDropdown?.querySelectorAll('input[name="genres[]"]').forEach(cb => {
            cb.addEventListener('change', () => this.updateGenreBadge());
        });

        // Initialize badge
        this.updateGenreBadge();
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

    setupImageLoading() {
        const images = document.querySelectorAll('.book-cover img:not(.loaded)');

        images.forEach(img => {
            img.addEventListener('load', function() {
                this.style.opacity = '1';
                this.classList.add('loaded');
                const loader = this.parentElement.querySelector('.book-loading');
                if (loader) {
                    loader.classList.add('hidden');
                }
            });

            // Hide loader if image is already cached/loaded
            if (img.complete) {
                img.style.opacity = '1';
                img.classList.add('loaded');
                const loader = img.parentElement.querySelector('.book-loading');
                if (loader) {
                    loader.classList.add('hidden');
                }
            }
        });
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    LazyLoadController.init();
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

<?php ob_start(); ?>

<div class="container">
    <h1 class="page-title">Katalog knih</h1>

    <!-- Search & Filter Bar -->
    <div class="catalog-controls">
        <div class="search-and-filter">
            <!-- Search Box -->
            <div class="search-box">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input type="text" id="search-input" placeholder="Hledat knihu..." autocomplete="off">
                <div id="search-results"></div>
            </div>

            <!-- Filter Button -->
            <button class="filter-toggle-btn" id="filter-toggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Filtry
                <span class="filter-count" id="filter-count" style="display: none;">1</span>
            </button>
        </div>

        <!-- Filter Dropdown -->
        <div class="filter-dropdown" id="filter-dropdown">
            <div class="filter-section">
                <h3 class="filter-title">Žánr</h3>
                <div class="filter-options">
                    <label class="filter-option">
                        <input type="radio" name="genre" value="" <?= !$genre ? 'checked' : '' ?>>
                        <span>Vše</span>
                        <span class="option-count"><?= $pagination['totalBooks'] ?></span>
                    </label>
                    <?php foreach ($genres as $g): ?>
                        <label class="filter-option">
                            <input type="radio" name="genre" value="<?= e($g['genre']) ?>" <?= ($genre === $g['genre']) ? 'checked' : '' ?>>
                            <span><?= e($g['genre']) ?></span>
                            <span class="option-count"><?= $g['count'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Future filters can be added here -->
            <!--
            <div class="filter-section">
                <h3 class="filter-title">Rok vydání</h3>
                <div class="filter-options">
                    <label class="filter-option">
                        <input type="checkbox" name="year" value="2024">
                        <span>2024</span>
                    </label>
                </div>
            </div>
            -->

            <div class="filter-actions">
                <button class="filter-clear-btn" id="filter-clear">Vymazat</button>
                <button class="filter-apply-btn" id="filter-apply">Použít</button>
            </div>
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
        const toggleBtn = document.getElementById('filter-toggle');
        const dropdown = document.getElementById('filter-dropdown');
        const applyBtn = document.getElementById('filter-apply');
        const clearBtn = document.getElementById('filter-clear');
        const filterCount = document.getElementById('filter-count');

        // Toggle dropdown
        toggleBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && e.target !== toggleBtn) {
                dropdown.classList.remove('active');
            }
        });

        // Apply filters
        applyBtn?.addEventListener('click', () => {
            const selectedGenre = document.querySelector('input[name="genre"]:checked')?.value;

            // Build URL with filters
            const url = selectedGenre
                ? `<?= BASE_URL ?>/?genre=${encodeURIComponent(selectedGenre)}`
                : '<?= BASE_URL ?>/';

            window.location.href = url;
        });

        // Clear filters
        clearBtn?.addEventListener('click', () => {
            // Reset all radio buttons to "Vše"
            document.querySelector('input[name="genre"][value=""]').checked = true;

            // Redirect to base URL
            window.location.href = '<?= BASE_URL ?>/';
        });

        // Update filter count badge
        this.updateFilterCount();
    },

    updateFilterCount() {
        const selectedGenre = document.querySelector('input[name="genre"]:checked')?.value;
        const filterCount = document.getElementById('filter-count');

        if (selectedGenre) {
            filterCount.textContent = '1';
            filterCount.style.display = 'inline-block';
        } else {
            filterCount.style.display = 'none';
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

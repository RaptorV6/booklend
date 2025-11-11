<?php
// SEO - Prevent admin pages from being indexed
ob_start();
?>
<meta name="robots" content="noindex,nofollow">
<?php
$seo_tags = ob_get_clean();

ob_start();
?>

<div class="container">
    <div class="admin-header">
        <h1 class="page-title">Správa knih</h1>
        <button class="btn-primary" onclick="openAddModal()">+ Přidat knihu</button>
    </div>

    <!-- Responzivní tabulka wrapper -->
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Obrázek</th>
                    <th>Název</th>
                    <th>Autor</th>
                    <th>ISBN</th>
                    <th>Žánr</th>
                    <th>Rok</th>
                    <th>Kopie</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody id="books-tbody">
                <!-- Books will be loaded here via JS -->
            </tbody>
        </table>
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
    <div id="no-more-books" style="display: none; text-align: center; padding: 40px; color: var(--text-muted);">
        <p>To je všechno! Žádné další knihy.</p>
    </div>
</div>

<!-- Add Book Modal -->
<div id="addBookModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přidat knihu</h2>
            <button onclick="closeAddModal()" class="modal-close" aria-label="Zavřít">&times;</button>
        </div>

        <div class="modal-body">
            <!-- Search Input -->
            <div id="searchSection">
                <div class="form-group">
                    <label for="bookSearch">Vyhledat knihu podle názvu nebo ISBN *</label>
                    <div class="search-box">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input type="text" id="bookSearch" placeholder="Začněte psát název nebo ISBN..." autocomplete="off">
                    </div>
                </div>

                <!-- Search Results -->
                <div id="search-results" class="search-results"></div>
            </div>

            <!-- Selected Book Preview -->
            <div id="bookPreview" style="display: none;">
                <button type="button" onclick="ISBNSearch.resetSearch()" class="btn-secondary" style="margin-bottom: 16px;">
                    ← Změnit výběr
                </button>

                <div class="book-preview-card">
                    <img id="previewThumbnail" src="" alt="Cover" style="width: 100px; height: 150px; object-fit: cover; border-radius: 8px; flex-shrink: 0;">
                    <div class="preview-info">
                        <h3 id="previewTitle"></h3>
                        <p id="previewAuthor"></p>
                        <p id="previewIsbn" class="isbn-badge"></p>
                        <p id="previewGenre"></p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="totalCopies">Celkem kopií *</label>
                        <input type="number" id="totalCopies" min="1" value="1" required>
                    </div>

                    <div class="form-group">
                        <label for="availableCopies">Dostupných kopií *</label>
                        <input type="number" id="availableCopies" min="0" value="1" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeAddModal()" class="btn-secondary">Zrušit</button>
                    <button type="button" onclick="addBook()" class="btn-primary">Přidat do katalogu</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Stock Modal -->
<div id="editStockModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Upravit skladové stavy</h2>
            <button onclick="closeEditModal()" class="modal-close" aria-label="Zavřít">&times;</button>
        </div>

        <div class="modal-body">
            <input type="hidden" id="editBookId">

            <!-- Book Info (Read-only) -->
            <div class="book-info-readonly">
                <p><strong>Název:</strong> <span id="editTitle"></span></p>
                <p><strong>Autor:</strong> <span id="editAuthor"></span></p>
                <p><strong>ISBN:</strong> <span id="editIsbn" class="isbn-badge"></span></p>
            </div>

            <!-- Editable Fields -->
            <div class="form-row">
                <div class="form-group">
                    <label for="editTotalCopies">Celkem kopií *</label>
                    <input type="number" id="editTotalCopies" min="1" required>
                </div>

                <div class="form-group">
                    <label for="editAvailableCopies">Dostupných kopií *</label>
                    <input type="number" id="editAvailableCopies" min="0" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeEditModal()" class="btn-secondary">Zrušit</button>
                <button type="button" onclick="updateStock()" class="btn-primary">Uložit</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Admin Header */
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    gap: 20px;
    flex-wrap: wrap;
}

.admin-header .btn-primary {
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    white-space: nowrap;
}

/* Admin Table */
.admin-table-wrapper {
    background: var(--surface);
    border-radius: 12px;
    overflow-x: auto;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid var(--border);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.admin-table thead {
    background: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid var(--border);
}

.admin-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: var(--text);
    font-size: 0.9rem;
    white-space: nowrap;
}

.admin-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
}

.admin-table tbody tr:hover {
    background: rgba(255, 255, 255, 0.02);
}

.admin-table td {
    padding: 16px;
    color: var(--text-muted);
    vertical-align: middle;
}

.admin-table .book-title-cell {
    color: var(--text);
    font-weight: 500;
}

.isbn-badge {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    background: rgba(102, 126, 234, 0.15);
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-block;
}

.admin-book-thumb {
    max-width: 60px;
    height: auto;
    border-radius: 4px;
    display: block;
}

.admin-actions {
    white-space: nowrap;
    display: flex;
    gap: 8px;
}

.btn-edit,
.btn-delete {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
    font-weight: 500;
}

.btn-edit {
    background: var(--primary);
    color: white;
}

.btn-delete {
    background: var(--danger);
    color: white;
}

.btn-edit:hover,
.btn-delete:hover {
    opacity: 0.85;
    transform: translateY(-1px);
}

/* Modal Overlay */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: var(--surface);
    border-radius: 16px;
    max-width: 950px;
    width: 95%;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    border: 1px solid var(--border);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 30px;
    border-bottom: 1px solid var(--border);
}

.modal-header h2 {
    margin: 0;
    color: var(--text);
    font-size: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 2rem;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text);
}

.modal-body {
    padding: 24px 30px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid var(--border);
    margin-top: 20px;
}

/* Form Elements */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text);
    font-size: 0.95rem;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid var(--border);
    border-radius: 8px;
    background: var(--background);
    color: var(--text);
    font-size: 0.95rem;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.modal-footer .btn-primary,
.modal-footer .btn-secondary {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-size: 0.95rem;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.modal-footer .btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
}

.modal-footer .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.modal-footer .btn-secondary {
    background: var(--background);
    color: var(--text);
    border: 2px solid var(--border);
}

.modal-footer .btn-secondary:hover {
    border-color: var(--text-muted);
}

/* Search Box in Modal */
.search-box {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 12px;
    color: var(--text-muted);
    pointer-events: none;
    z-index: 1;
}

.search-box input {
    padding-left: 48px !important;
    width: 100%;
    font-size: 16px !important;
    padding-top: 14px !important;
    padding-bottom: 14px !important;
}

.search-box input::placeholder {
    color: var(--text-muted);
    opacity: 0.7;
}

.search-results {
    display: none;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    margin-top: 20px;
    animation: fadeIn 0.3s ease;
}

.search-results.active {
    display: grid;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-result-item {
    background: rgba(255, 255, 255, 0.03);
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.search-result-item:hover {
    background: rgba(99, 102, 241, 0.15);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.3);
}

.search-result-item img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.search-result-info h4 {
    margin: 0 0 8px 0;
    color: var(--text);
    font-size: 16px;
    font-weight: 600;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    min-height: 42px;
}

.search-result-info p {
    margin: 0;
    color: var(--text-muted);
    font-size: 13px;
    line-height: 1.5;
}

/* Book Preview Card */
.book-preview-card {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid var(--border);
}

.preview-info h3 {
    margin: 0 0 8px 0;
    color: var(--text);
}

.preview-info p {
    margin: 4px 0;
    color: var(--text-muted);
    font-size: 14px;
}

/* Book Info Read-only */
.book-info-readonly {
    background: rgba(255, 255, 255, 0.02);
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid var(--border);
}

.book-info-readonly p {
    margin: 8px 0;
    color: var(--text-muted);
}

.book-info-readonly strong {
    color: var(--text);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-table-wrapper {
        border-radius: 0;
        margin: 0 -20px;
    }

    .admin-table {
        min-width: auto;
        display: block;
    }

    .admin-table thead {
        display: none;
    }

    .admin-table tbody,
    .admin-table tr {
        display: block;
    }

    .admin-table tr {
        margin-bottom: 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
    }

    .admin-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
    }

    .admin-table td:last-child {
        border-bottom: none;
    }

    .admin-table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: var(--text);
    }

    .admin-actions {
        flex-direction: column;
        align-items: stretch !important;
        width: 100%;
    }

    .btn-edit,
    .btn-delete {
        width: 100%;
        margin: 4px 0;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .modal-content {
        width: 95%;
    }

    .modal-header,
    .modal-body {
        padding: 20px;
    }

    .book-preview-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .search-results {
        grid-template-columns: 1fr;
    }
}

@media (min-width: 768px) {
    .search-results {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1200px) {
    .search-results {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>

<script src="<?= BASE_URL ?>/assets/js/ajax.js"></script>
<script>
console.log('📦 Admin Dashboard v2.1 - Build: <?= date('Y-m-d H:i:s') ?>');
console.log('🔧 No-cache headers enabled');

const BASE_URL = '<?= BASE_URL ?>';

// Lazy Loading Controller
const AdminLazyLoad = {
    currentPage: 0,
    hasMore: true,
    loading: false,

    init() {
        this.loadMore(); // Load first page
        this.setupIntersectionObserver();
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
            const response = await fetch(`${BASE_URL}/api/admin/books?page=${nextPage}&limit=20`);
            const data = await response.json();

            if (data.success && data.books && data.books.length > 0) {
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
            console.error('Error loading books:', error);
            window.toast.error('Chyba při načítání knih');
        } finally {
            this.loading = false;
            document.getElementById('loading-more').style.display = 'none';
        }
    },

    appendBooks(books) {
        const tbody = document.getElementById('books-tbody');

        books.forEach(book => {
            const row = this.createBookRow(book);
            tbody.appendChild(row);
        });
    },

    createBookRow(book) {
        const tr = document.createElement('tr');

        const thumbnail = book.thumbnail
            ? `<img src="${escapeHtml(book.thumbnail)}" alt="Cover" class="admin-book-thumb">`
            : '<span style="color: var(--text-muted);">-</span>';

        const genre = book.genre
            ? `<span class="badge badge-genre">${escapeHtml(book.genre)}</span>`
            : '<span style="color: var(--text-muted);">-</span>';

        tr.innerHTML = `
            <td data-label="ID">${book.id}</td>
            <td data-label="Obrázek">${thumbnail}</td>
            <td data-label="Název" class="book-title-cell">${escapeHtml(book.title)}</td>
            <td data-label="Autor">${escapeHtml(book.author)}</td>
            <td data-label="ISBN"><span class="isbn-badge">${escapeHtml(book.isbn)}</span></td>
            <td data-label="Žánr">${genre}</td>
            <td data-label="Rok">${escapeHtml(book.published_year || '-')}</td>
            <td data-label="Kopie">
                <span class="badge badge-available">${book.available_copies} / ${book.total_copies}</span>
            </td>
            <td data-label="Akce" class="admin-actions">
                <button onclick="openEditModal(${book.id})" class="btn-edit">Upravit</button>
                <button onclick="deleteBook(${book.id})" class="btn-delete">Smazat</button>
            </td>
        `;

        return tr;
    }
};

// ISBN Search Controller
const ISBNSearch = {
    timeout: null,
    selectedBook: null,

    init() {
        const input = document.getElementById('bookSearch');
        const resultsDiv = document.getElementById('search-results');

        input.addEventListener('input', (e) => {
            const query = e.target.value.trim();

            if (query.length < 2) {
                resultsDiv.classList.remove('active');
                return;
            }

            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.search(query);
            }, 300);
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-box')) {
                resultsDiv.classList.remove('active');
            }
        });
    },

    async search(query) {
        const resultsDiv = document.getElementById('search-results');

        try {
            const url = `${BASE_URL}/api/admin/search-books?q=${encodeURIComponent(query)}`;
            console.log('═════════════════════════════════════');
            console.log('🔍 SEARCH DEBUG v2.0');
            console.log('Query:', query);
            console.log('BASE_URL:', BASE_URL);
            console.log('Full URL:', url);
            console.log('═════════════════════════════════════');

            const response = await fetch(url);
            console.log('Response status:', response.status, response.statusText);
            console.log('Response ok:', response.ok);

            if (!response.ok) {
                console.error('❌ API error:', response.status, response.statusText);
                const text = await response.text();
                console.error('Response body:', text);
                resultsDiv.innerHTML = '<div style="grid-column: 1/-1; padding: 40px; color: var(--text-muted); text-align: center;">Chyba API</div>';
                resultsDiv.classList.add('active');
                return;
            }

            const data = await response.json();
            console.log('✅ API response:', JSON.stringify(data, null, 2));
            console.log('Items count:', data.items ? data.items.length : 0);
            console.log('Debug message:', data.debug);

            this.displayResults(data.items || [], data.debug);
        } catch (error) {
            console.error('❌ Search error:', error);
            console.error('Error stack:', error.stack);
            resultsDiv.innerHTML = '<div style="grid-column: 1/-1; padding: 40px; color: var(--text-muted); text-align: center;">Chyba při vyhledávání</div>';
            resultsDiv.classList.add('active');
        }
    },

    displayResults(items, debug) {
        console.log('📋 displayResults() called');
        const resultsDiv = document.getElementById('search-results');
        console.log('resultsDiv element:', resultsDiv);
        console.log('Items to display:', items.length);

        if (items.length === 0) {
            const msg = debug ? `Žádné výsledky (${debug})` : 'Žádné výsledky';
            resultsDiv.innerHTML = `<div style="grid-column: 1/-1; padding: 40px; color: var(--text-muted); text-align: center; font-size: 16px;">${msg}</div>`;
            resultsDiv.classList.add('active');
            console.log('Added .active class (no results)');
            console.log('resultsDiv classes:', resultsDiv.className);
            return;
        }

        console.log('Clearing resultsDiv and adding items...');
        resultsDiv.innerHTML = '';
        items.forEach((item, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'search-result-item';
            itemDiv.dataset.bookData = JSON.stringify(item);

            // Use placeholder if no thumbnail
            const thumbnailUrl = item.thumbnail || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="128" height="192" viewBox="0 0 128 192"%3E%3Crect fill="%23e2e8f0" width="128" height="192"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="14" fill="%23475569"%3ENo Image%3C/text%3E%3C/svg%3E';

            itemDiv.innerHTML = `
                <img src="${escapeHtml(thumbnailUrl)}" alt="Cover" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22128%22 height=%22192%22%3E%3Crect fill=%22%23e2e8f0%22 width=%22128%22 height=%22192%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2214%22 fill=%22%23475569%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                <div class="search-result-info">
                    <h4>${escapeHtml(item.title)}</h4>
                    <p>${escapeHtml(item.author)} • ${escapeHtml(item.isbn)}</p>
                </div>
            `;
            itemDiv.addEventListener('click', () => {
                this.selectBook(item);
            });
            resultsDiv.appendChild(itemDiv);
        });

        console.log('All items added. Adding .active class...');
        resultsDiv.classList.add('active');
        console.log('resultsDiv classes:', resultsDiv.className);
        console.log('resultsDiv display:', window.getComputedStyle(resultsDiv).display);
        console.log('resultsDiv innerHTML length:', resultsDiv.innerHTML.length);
    },

    async selectBook(book) {
        console.log('📚 selectBook() called for:', book.title);

        // Check if ISBN already exists
        const response = await fetch(`${BASE_URL}/api/admin/check-isbn?isbn=${encodeURIComponent(book.isbn)}`);
        const data = await response.json();
        console.log('ISBN check result:', data);

        if (data.exists) {
            window.toast.error('Kniha s tímto ISBN již existuje v katalogu');
            return;
        }

        this.selectedBook = book;
        console.log('Selected book saved:', this.selectedBook);

        // Hide search section, show preview
        document.getElementById('searchSection').style.display = 'none';
        const searchResults = document.getElementById('search-results');
        searchResults.classList.remove('active');

        const bookPreview = document.getElementById('bookPreview');
        bookPreview.style.display = 'block';
        console.log('bookPreview display set to block');

        // Fill preview (with placeholder if no thumbnail)
        const placeholderSvg = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="128" height="192" viewBox="0 0 128 192"%3E%3Crect fill="%23e2e8f0" width="128" height="192"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="14" fill="%23475569"%3ENo Image%3C/text%3E%3C/svg%3E';
        document.getElementById('previewThumbnail').src = book.thumbnail || placeholderSvg;
        document.getElementById('previewTitle').textContent = book.title;
        document.getElementById('previewAuthor').textContent = book.author;
        document.getElementById('previewIsbn').textContent = book.isbn;
        document.getElementById('previewGenre').textContent = book.genre ? `Žánr: ${book.genre}` : '';
    },

    resetSearch() {
        // Show search section again
        document.getElementById('searchSection').style.display = 'block';
        document.getElementById('bookSearch').value = '';
        document.getElementById('bookPreview').style.display = 'none';
        this.selectedBook = null;
        document.getElementById('bookSearch').focus();
    }
};

// Modal Functions
function openAddModal() {
    ISBNSearch.selectedBook = null;
    document.getElementById('searchSection').style.display = 'block';
    document.getElementById('bookSearch').value = '';
    document.getElementById('search-results').classList.remove('active');
    document.getElementById('bookPreview').style.display = 'none';
    document.getElementById('totalCopies').value = '1';
    document.getElementById('availableCopies').value = '1';
    document.getElementById('addBookModal').classList.add('active');
    setTimeout(() => document.getElementById('bookSearch').focus(), 100);
}

function closeAddModal() {
    document.getElementById('addBookModal').classList.remove('active');
}

async function addBook() {
    if (!ISBNSearch.selectedBook) {
        window.toast.error('Nejprve vyberte knihu');
        return;
    }

    const totalCopies = parseInt(document.getElementById('totalCopies').value);
    const availableCopies = parseInt(document.getElementById('availableCopies').value);

    if (availableCopies > totalCopies) {
        window.toast.error('Dostupných kopií nemůže být více než celkem');
        return;
    }

    const data = {
        ...ISBNSearch.selectedBook,
        total_copies: totalCopies,
        available_copies: availableCopies
    };

    try {
        console.log('Adding book:', data);
        const response = await fetch(`${BASE_URL}/api/admin/create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            const text = await response.text();
            console.error('Server error:', response.status, text);
            window.toast.error('Server error: ' + response.status);
            return;
        }

        const result = await response.json();
        console.log('Result:', result);

        if (result.success) {
            window.toast.success(result.message || 'Kniha přidána');
            closeAddModal();

            // Reload pouze seznam knih bez page refresh
            AdminLazyLoad.currentPage = 0;
            AdminLazyLoad.hasMore = true;
            document.getElementById('books-tbody').innerHTML = '';
            AdminLazyLoad.loadMore();
        } else {
            window.toast.error(result.error || 'Chyba při přidávání');
        }
    } catch (error) {
        console.error('Add book error:', error);
        window.toast.error('Chyba při komunikaci se serverem');
    }
}

async function openEditModal(bookId) {
    try {
        const response = await fetch(`${BASE_URL}/api/admin/book?id=${bookId}`);
        const data = await response.json();

        if (data.success) {
            const book = data.book;
            document.getElementById('editBookId').value = book.id;
            document.getElementById('editTitle').textContent = book.title;
            document.getElementById('editAuthor').textContent = book.author;
            document.getElementById('editIsbn').textContent = book.isbn;
            document.getElementById('editTotalCopies').value = book.total_copies;
            document.getElementById('editAvailableCopies').value = book.available_copies;

            document.getElementById('editStockModal').classList.add('active');
        } else {
            window.toast.error(data.error || 'Chyba při načítání knihy');
        }
    } catch (error) {
        window.toast.error('Chyba při komunikaci se serverem');
        console.error(error);
    }
}

function closeEditModal() {
    document.getElementById('editStockModal').classList.remove('active');
}

async function updateStock() {
    const bookId = document.getElementById('editBookId').value;
    const totalCopies = parseInt(document.getElementById('editTotalCopies').value);
    const availableCopies = parseInt(document.getElementById('editAvailableCopies').value);

    if (availableCopies > totalCopies) {
        window.toast.error('Dostupných kopií nemůže být více než celkem');
        return;
    }

    const data = {
        id: bookId,
        total_copies: totalCopies,
        available_copies: availableCopies
    };

    try {
        console.log('Updating stock:', data);
        const response = await fetch(`${BASE_URL}/api/admin/update-stock`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            const text = await response.text();
            console.error('Server error:', response.status, text);
            window.toast.error('Server error: ' + response.status);
            return;
        }

        const result = await response.json();
        console.log('Result:', result);

        if (result.success) {
            window.toast.success(result.message || 'Skladové stavy aktualizovány');
            closeEditModal();

            // Update DOM - najdi řádek a aktualizuj skladové zásoby
            const row = document.querySelector(`tr button[onclick*="openEditModal(${bookId})"]`)?.closest('tr');
            if (row) {
                const stockCell = row.querySelector('td[data-label="Kopie"]');
                if (stockCell) {
                    stockCell.innerHTML = `<span class="badge badge-available">${availableCopies} / ${totalCopies}</span>`;
                }
            }
        } else {
            window.toast.error(result.error || 'Chyba při aktualizaci');
        }
    } catch (error) {
        console.error('Update stock error:', error);
        window.toast.error('Chyba při komunikaci se serverem');
    }
}

async function deleteBook(bookId) {
    // Use confirmToast from ajax.js
    const confirmed = await confirmToast('Opravdu chcete smazat tuto knihu?');
    if (!confirmed) return;

    try {
        const response = await fetch(`${BASE_URL}/api/admin/delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: bookId })
        });

        const result = await response.json();

        if (result.success) {
            window.toast.success(result.message);

            // Remove row from DOM with animation
            const row = document.querySelector(`tr button[onclick*="deleteBook(${bookId})"]`)?.closest('tr');
            if (row) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                row.style.transition = 'all 0.3s ease';
                setTimeout(() => row.remove(), 300);
            }
        } else {
            window.toast.error(result.error || 'Chyba při mazání');
        }
    } catch (error) {
        window.toast.error('Chyba při komunikaci se serverem');
        console.error(error);
    }
}

function escapeHtml(text) {
    if (typeof text !== 'string') return text;
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    AdminLazyLoad.init();
    ISBNSearch.init();
});

// Close modals on ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

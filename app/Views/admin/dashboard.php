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
    <div class="modal-content add-book-modal">
        <div class="modal-header">
            <h2>Přidat knihu</h2>
            <button onclick="closeAddModal()" class="modal-close" aria-label="Zavřít">&times;</button>
        </div>

        <div class="modal-body">
            <!-- Search Section -->
            <div id="searchSection">
                <div class="search-container">
                    <label for="bookSearch" class="search-label">Vyhledejte knihu podle názvu nebo ISBN</label>
                    <div class="search-input-wrapper">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input
                            type="text"
                            id="bookSearch"
                            class="search-input"
                            placeholder="Např. 1984, George Orwell, 978-0-452-28423-4..."
                            autocomplete="off"
                        >
                        <div id="searchLoader" class="search-loader" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                    </div>
                    <p class="search-hint">Začněte psát pro vyhledávání v Google Books databázi</p>
                </div>

                <!-- Search Results List -->
                <div id="searchResults" class="search-results-list"></div>
            </div>

            <!-- Selected Book Preview -->
            <div id="bookPreview" class="book-preview-section" style="display: none;">
                <button type="button" onclick="resetSearch()" class="btn-back">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Vybrat jinou knihu
                </button>

                <div class="selected-book-card">
                    <div class="book-cover-wrapper">
                        <img id="previewThumbnailV2" src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22150%22%3E%3Crect fill=%22%23334155%22 width=%22100%22 height=%22150%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-size=%2240%22%3E📚%3C/text%3E%3C/svg%3E" alt="Cover" style="width: 100px; height: 150px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <div class="book-details">
                        <h3 id="previewTitleV2"></h3>
                        <p class="book-author" id="previewAuthorV2"></p>
                        <div class="book-meta">
                            <span id="previewIsbnV2" class="isbn-badge"></span>
                            <span id="previewGenreV2" class="genre-badge"></span>
                            <span id="previewYearV2" class="year-badge"></span>
                        </div>
                        <p class="book-description" id="previewDescriptionV2"></p>
                    </div>
                </div>

                <div class="stock-form">
                    <div class="form-group">
                        <label for="totalCopies">Celkový počet kusů</label>
                        <input type="number" id="totalCopies" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="availableCopies">Dostupných kusů</label>
                        <input type="number" id="availableCopies" min="0" value="1" required>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeAddModal()" class="btn btn-secondary">Zrušit</button>
                    <button type="button" onclick="addBook()" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Přidat do katalogu
                    </button>
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

/* Add Book Modal - New Design */
.add-book-modal .modal-content {
    max-width: 650px;
}

/* Search Container */
.search-container {
    margin-bottom: 24px;
}

.search-label {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 12px;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 16px;
    color: var(--text-muted);
    pointer-events: none;
    z-index: 1;
}

.search-input {
    width: 100%;
    padding: 14px 50px 14px 48px;
    border: 2px solid var(--border);
    border-radius: 10px;
    background: var(--background);
    color: var(--text);
    font-size: 15px;
    transition: all 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-input::placeholder {
    color: var(--text-muted);
    opacity: 0.6;
}

.search-loader {
    position: absolute;
    right: 16px;
    display: flex;
    align-items: center;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid var(--border);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.search-hint {
    margin-top: 8px;
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* Search Results List */
.search-results-list {
    display: none;
    flex-direction: column;
    gap: 10px;
    max-height: 400px;
    overflow-y: auto;
    margin-top: 16px;
    padding: 4px;
}

.search-results-list.active {
    display: flex;
}

/* Custom Scrollbar for Search Results */
.search-results-list::-webkit-scrollbar {
    width: 8px;
}

.search-results-list::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 10px;
}

.search-results-list::-webkit-scrollbar-thumb {
    background: rgba(102, 126, 234, 0.3);
    border-radius: 10px;
}

.search-results-list::-webkit-scrollbar-thumb:hover {
    background: rgba(102, 126, 234, 0.5);
}

.search-result-item {
    display: flex;
    gap: 14px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.search-result-item:hover {
    background: rgba(102, 126, 234, 0.08);
    border-color: var(--primary);
    transform: translateX(4px);
}

.search-result-item img {
    width: 50px;
    height: 75px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.search-result-info {
    flex: 1;
    min-width: 0;
}

.search-result-info h4 {
    margin: 0 0 4px 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.search-result-info p {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.search-empty,
.search-error {
    padding: 40px 20px;
    text-align: center;
    color: var(--text-muted);
    font-size: 14px;
}

/* Book Preview Section */
.book-preview-section {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 20px;
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--primary);
    transform: translateX(-2px);
}

.selected-book-card {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    border-radius: 12px;
    margin-bottom: 24px;
}

.book-cover-wrapper {
    flex-shrink: 0;
}

.book-cover-wrapper img {
    width: 100px;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}

.book-details {
    flex: 1;
    min-width: 0;
}

.book-details h3 {
    margin: 0 0 6px 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.3;
}

.book-author {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: var(--text-muted);
}

.book-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}

.book-description {
    margin: 16px 0 0 0;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-muted);
    max-height: 120px;
    overflow-y: auto;
}

.book-description:empty {
    display: none;
}

/* Custom scrollbar for description */
.book-description::-webkit-scrollbar {
    width: 6px;
}

.book-description::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 6px;
}

.book-description::-webkit-scrollbar-thumb {
    background: rgba(102, 126, 234, 0.3);
    border-radius: 6px;
}

.book-description::-webkit-scrollbar-thumb:hover {
    background: rgba(102, 126, 234, 0.5);
}

.genre-badge,
.year-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
}

.genre-badge {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
}

.year-badge {
    background: rgba(250, 204, 21, 0.15);
    color: #facc15;
}

/* Stock Form */
.stock-form {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stock-form .form-group {
    margin-bottom: 0;
}

.stock-form label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 8px;
}

.stock-form input {
    padding: 12px 14px;
    font-size: 15px;
}

/* Modal Actions */
.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: var(--background);
    color: var(--text);
    border: 2px solid var(--border);
}

.btn-secondary:hover {
    border-color: var(--text-muted);
    background: rgba(255, 255, 255, 0.03);
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

    /* Add Book Modal Responsive */
    .add-book-modal .modal-content {
        max-width: 95%;
        max-height: 95vh;
    }

    .selected-book-card {
        flex-direction: column;
        text-align: center;
    }

    .book-cover-wrapper {
        margin: 0 auto;
    }

    .book-meta {
        justify-content: center;
    }

    .stock-form {
        grid-template-columns: 1fr;
    }

    .modal-actions {
        flex-direction: column;
    }

    .modal-actions .btn {
        width: 100%;
    }

    .search-input {
        font-size: 16px; /* Prevents zoom on iOS */
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

        const genre = book.genre
            ? `<span class="badge badge-genre">${escapeHtml(book.genre)}</span>`
            : '<span style="color: var(--text-muted);">-</span>';

        tr.innerHTML = `
            <td data-label="ID">${book.id}</td>
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

// Book Search Controller (Refactored)
let selectedBook = null;
let searchTimeout = null;

function initBookSearch() {
    const input = document.getElementById('bookSearch');
    const loader = document.getElementById('searchLoader');
    const resultsDiv = document.getElementById('searchResults');

    input.addEventListener('input', (e) => {
        const query = e.target.value.trim();

        // Clear results if query too short
        if (query.length < 2) {
            resultsDiv.classList.remove('active');
            resultsDiv.innerHTML = '';
            return;
        }

        // Show loader
        loader.style.display = 'flex';

        // Debounce search
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchBooks(query);
        }, 400);
    });
}

async function searchBooks(query) {
    const loader = document.getElementById('searchLoader');
    const resultsDiv = document.getElementById('searchResults');

    try {
        const url = `${BASE_URL}/api/admin/search-books?q=${encodeURIComponent(query)}`;
        console.log('🔍 Searching:', query);

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        console.log('✅ Found:', data.items?.length || 0, 'books');

        displaySearchResults(data.items || []);
    } catch (error) {
        console.error('❌ Search error:', error);
        resultsDiv.innerHTML = '<div class="search-error">Chyba při vyhledávání. Zkuste to znovu.</div>';
        resultsDiv.classList.add('active');
    } finally {
        loader.style.display = 'none';
    }
}

function displaySearchResults(items) {
    const resultsDiv = document.getElementById('searchResults');

    if (items.length === 0) {
        resultsDiv.innerHTML = '<div class="search-empty">Žádné výsledky. Zkuste jiný název nebo ISBN.</div>';
        resultsDiv.classList.add('active');
        return;
    }

    resultsDiv.innerHTML = '';
    items.forEach(book => {
        const item = createSearchResultItem(book);
        resultsDiv.appendChild(item);
    });
    resultsDiv.classList.add('active');
}

function createSearchResultItem(book) {
    const item = document.createElement('div');
    item.className = 'search-result-item';

    const placeholder = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2275%22%3E%3Crect fill=%22%23334155%22 width=%2250%22 height=%2275%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-size=%2210%22 fill=%22%23cbd5e1%22%3E📚%3C/text%3E%3C/svg%3E';
    const thumbnail = book.thumbnail || placeholder;

    item.innerHTML = `
        <img src="${escapeHtml(thumbnail)}" alt="Cover" onerror="this.src='${placeholder}'">
        <div class="search-result-info">
            <h4>${escapeHtml(book.title)}</h4>
            <p>${escapeHtml(book.author || 'Neznámý autor')} • ${escapeHtml(book.isbn)}</p>
        </div>
    `;

    item.addEventListener('click', () => selectBook(book));
    return item;
}

async function selectBook(book) {
    console.log('📚 Selected:', book.title);

    // Check ISBN duplicate
    try {
        const response = await fetch(`${BASE_URL}/api/admin/check-isbn?isbn=${encodeURIComponent(book.isbn)}`);
        const data = await response.json();

        if (data.exists) {
            window.toast.error('Kniha s tímto ISBN již existuje v katalogu');
            return;
        }
    } catch (error) {
        console.error('ISBN check error:', error);
    }

    selectedBook = book;

    // Hide search, show preview
    document.getElementById('searchSection').style.display = 'none';
    document.getElementById('searchResults').classList.remove('active');
    document.getElementById('bookPreview').style.display = 'block';

    // Fill preview (V2)
    const placeholder = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22150%22%3E%3Crect fill=%22%23334155%22 width=%22100%22 height=%22150%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-size=%2240%22%3E📚%3C/text%3E%3C/svg%3E';

    const thumbnailEl = document.getElementById('previewThumbnailV2');
    const titleEl = document.getElementById('previewTitleV2');
    const authorEl = document.getElementById('previewAuthorV2');
    const isbnEl = document.getElementById('previewIsbnV2');

    if (!thumbnailEl || !titleEl || !authorEl || !isbnEl) {
        console.error('⚠️ Preview elements not found! DOM:', {
            thumbnail: !!thumbnailEl,
            title: !!titleEl,
            author: !!authorEl,
            isbn: !!isbnEl
        });
        window.toast.error('Chyba: Restartujte Apache a obnovte stránku');
        return;
    }

    thumbnailEl.src = book.thumbnail || placeholder;
    titleEl.textContent = book.title;
    authorEl.textContent = book.author || 'Neznámý autor';
    isbnEl.textContent = book.isbn;

    const genreEl = document.getElementById('previewGenreV2');
    if (book.genre && genreEl) {
        genreEl.textContent = book.genre;
        genreEl.style.display = 'inline-block';
    } else if (genreEl) {
        genreEl.style.display = 'none';
    }

    const yearEl = document.getElementById('previewYearV2');
    if (book.published_year && yearEl) {
        yearEl.textContent = book.published_year;
        yearEl.style.display = 'inline-block';
    } else if (yearEl) {
        yearEl.style.display = 'none';
    }

    const descriptionEl = document.getElementById('previewDescriptionV2');
    if (descriptionEl) {
        descriptionEl.textContent = book.description || '';
    }
}

function resetSearch() {
    selectedBook = null;
    document.getElementById('searchSection').style.display = 'block';
    document.getElementById('bookPreview').style.display = 'none';
    document.getElementById('bookSearch').value = '';
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchResults').classList.remove('active');
    document.getElementById('bookSearch').focus();
}

// Modal Functions
function openAddModal() {
    resetSearch();
    document.getElementById('totalCopies').value = '1';
    document.getElementById('availableCopies').value = '1';
    document.getElementById('addBookModal').classList.add('active');
    setTimeout(() => document.getElementById('bookSearch').focus(), 100);
}

function closeAddModal() {
    document.getElementById('addBookModal').classList.remove('active');
    resetSearch();
}

async function addBook() {
    if (!selectedBook) {
        window.toast.error('Nejprve vyberte knihu');
        return;
    }

    const totalCopies = parseInt(document.getElementById('totalCopies').value);
    const availableCopies = parseInt(document.getElementById('availableCopies').value);

    if (!totalCopies || totalCopies < 1) {
        window.toast.error('Zadejte platný počet kusů');
        return;
    }

    if (availableCopies < 0 || availableCopies > totalCopies) {
        window.toast.error('Dostupných kusů nemůže být více než celkový počet');
        return;
    }

    const data = {
        ...selectedBook,
        total_copies: totalCopies,
        available_copies: availableCopies
    };

    try {
        console.log('📤 Adding book:', data.title);
        const response = await fetch(`${BASE_URL}/api/admin/create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const text = await response.text();
            console.error('Server error:', response.status, text);
            window.toast.error(`Chyba serveru: ${response.status}`);
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
    initBookSearch();
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

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

    <!-- Admin Controls -->
    <div class="admin-controls">
        <!-- Sort Filter -->
        <div class="filter-chip-wrapper">
            <button class="filter-chip" id="admin-sort-chip">
                <span id="admin-sort-label">Nejnovější</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <div class="chip-dropdown" id="admin-sort-dropdown">
                <div class="chip-dropdown-header">
                    <h3>Řadit podle</h3>
                </div>
                <div class="chip-dropdown-content">
                    <label class="chip-option">
                        <input type="radio" name="admin-sort" value="newest" checked>
                        <span>Nejnovější</span>
                    </label>
                    <label class="chip-option">
                        <input type="radio" name="admin-sort" value="oldest">
                        <span>Nejstarší</span>
                    </label>
                    <label class="chip-option">
                        <input type="radio" name="admin-sort" value="title-asc">
                        <span>Název (A-Z)</span>
                    </label>
                    <label class="chip-option">
                        <input type="radio" name="admin-sort" value="title-desc">
                        <span>Název (Z-A)</span>
                    </label>
                    <label class="chip-option">
                        <input type="radio" name="admin-sort" value="author-asc">
                        <span>Autor (A-Z)</span>
                    </label>
                    <label class="chip-option">
                        <input type="radio" name="admin-sort" value="author-desc">
                        <span>Autor (Z-A)</span>
                    </label>
                </div>
                <div class="chip-dropdown-footer">
                    <button class="chip-apply" id="admin-sort-apply">Použít</button>
                </div>
            </div>
        </div>

        <!-- Pagination Controls (inline) -->
        <div id="pagination-controls"></div>
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

    <!-- Pagination (Bottom) -->
    <div id="pagination"></div>
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

<!-- Include Admin & Pagination CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pagination.css">

<script src="<?= BASE_URL ?>/assets/js/ajax.js"></script>
<script src="<?= BASE_URL ?>/assets/js/pagination.js"></script>
<script src="<?= BASE_URL ?>/assets/js/helpers.js"></script>
<script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
<script>
// Global BASE_URL for admin.js
const BASE_URL = '<?= BASE_URL ?>';

// Initialize Admin Paginator with default sort
initAdminPaginator(BASE_URL, { sort: 'newest' });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

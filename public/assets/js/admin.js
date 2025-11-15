// ═══════════════════════════════════════════════════════════
// BOOKLEND - Admin Dashboard JavaScript
// ═══════════════════════════════════════════════════════════

// Global state
let selectedBook = null;
let searchTimeout = null;

/**
 * Initialize Admin Paginator
 * NOTE: BASE_URL must be defined before including this script
 * NOTE: adminPaginator is global and used by inline onclick handlers
 */
function initAdminPaginator(baseUrl, filters = {}) {
    window.adminPaginator = new Paginator({
        apiEndpoint: `${baseUrl}/api/admin/books`,
        containerSelector: '#books-tbody',
        controlsSelector: '#pagination-controls',
        paginationSelector: '#pagination',
        defaultPerPage: 20,
        filters: filters,
        renderItem: (book) => {
            const genre = book.genre
                ? `<span class="badge badge-genre">${escapeHtml(book.genre)}</span>`
                : '<span style="color: var(--text-muted);">-</span>';

            return `
                <tr>
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
                </tr>
            `;
        }
    });
}

// ════════════════════════════════════════════════════════
// TABLE HEADER SORTING
// ════════════════════════════════════════════════════════

function initTableSort() {
    const headers = document.querySelectorAll('.admin-table th.sortable');

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const column = header.dataset.sort;
            const isActive = header.classList.contains('active');
            const currentDirection = header.classList.contains('desc') ? 'desc' : 'asc';

            // Toggle direction if same column, otherwise default to asc
            const newDirection = isActive ? (currentDirection === 'asc' ? 'desc' : 'asc') : 'asc';

            // Remove active state from all headers
            headers.forEach(h => {
                h.classList.remove('active', 'asc', 'desc');
            });

            // Add active state to clicked header
            header.classList.add('active', newDirection);

            // Update sort indicator arrow
            const indicator = header.querySelector('.sort-indicator svg path');
            if (indicator) {
                if (newDirection === 'asc') {
                    indicator.setAttribute('d', 'M6 3L2 7h8z'); // Arrow up
                } else {
                    indicator.setAttribute('d', 'M6 9L2 5h8z'); // Arrow down
                }
            }

            // Update paginator and reload
            window.adminPaginator.setFilters({ sort: `${column}-${newDirection}` });
            window.adminPaginator.loadPage(1);
        });
    });
}

// ════════════════════════════════════════════════════════
// BOOK SEARCH
// ════════════════════════════════════════════════════════

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
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        // Debug: Show API info in console
        if (data.debug) {
            console.log('🔍 Google Books API Debug:', data.debug);
        }

        displaySearchResults(data.items || []);
    } catch (error) {
        console.error('Search error:', error);
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

    // Fill preview
    const placeholder = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22150%22%3E%3Crect fill=%22%23334155%22 width=%22100%22 height=%22150%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-size=%2240%22%3E📚%3C/text%3E%3C/svg%3E';

    document.getElementById('previewThumbnailV2').src = book.thumbnail || placeholder;
    document.getElementById('previewTitleV2').textContent = book.title;
    document.getElementById('previewAuthorV2').textContent = book.author || 'Neznámý autor';
    document.getElementById('previewIsbnV2').textContent = book.isbn;

    const genreEl = document.getElementById('previewGenreV2');
    if (book.genre) {
        genreEl.textContent = book.genre;
        genreEl.style.display = 'inline-block';
    } else {
        genreEl.style.display = 'none';
    }

    const yearEl = document.getElementById('previewYearV2');
    if (book.published_year) {
        yearEl.textContent = book.published_year;
        yearEl.style.display = 'inline-block';
    } else {
        yearEl.style.display = 'none';
    }

    const descriptionEl = document.getElementById('previewDescriptionV2');
    descriptionEl.textContent = book.description || '';
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

// ════════════════════════════════════════════════════════
// MODAL FUNCTIONS
// ════════════════════════════════════════════════════════

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

// ════════════════════════════════════════════════════════
// CRUD OPERATIONS
// ════════════════════════════════════════════════════════

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
        const response = await fetch(`${BASE_URL}/api/admin/create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            window.toast.error(`Chyba serveru: ${response.status}`);
            return;
        }

        const result = await response.json();

        if (result.success) {
            window.toast.success(result.message || 'Kniha přidána');
            closeAddModal();

            // Reload book list
            window.adminPaginator.reload();
        } else {
            window.toast.error(result.error || 'Chyba při přidávání');
        }
    } catch (error) {
        console.error('Add book error:', error);
        window.toast.error('Chyba při komunikaci se serverem');
    }
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
        const response = await fetch(`${BASE_URL}/api/admin/update-stock`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            window.toast.error('Chyba serveru: ' + response.status);
            return;
        }

        const result = await response.json();

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

// ════════════════════════════════════════════════════════
// INITIALIZATION
// ════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
    initBookSearch();
    initTableSort();
});

// Close modals on ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});

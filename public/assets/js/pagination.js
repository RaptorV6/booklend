// ═══════════════════════════════════════════════════════════
// BOOKLEND - Reusable Pagination Component
// ═══════════════════════════════════════════════════════════

class Paginator {
    /**
     * @param {Object} config Configuration object
     * @param {string} config.apiEndpoint - API endpoint to fetch data
     * @param {string} config.containerSelector - Selector for items container
     * @param {string} config.controlsSelector - Selector for top controls
     * @param {string} config.paginationSelector - Selector for bottom pagination
     * @param {Function} config.renderItem - Function to render single item
     * @param {number} config.defaultPerPage - Default items per page (10, 20, 50)
     * @param {Object} config.filters - Additional filters (genre, year, sort, etc.)
     */
    constructor(config) {
        this.apiEndpoint = config.apiEndpoint;
        this.container = document.querySelector(config.containerSelector);
        this.controlsContainer = document.querySelector(config.controlsSelector);
        this.paginationContainer = document.querySelector(config.paginationSelector);
        this.renderItem = config.renderItem;
        this.filters = config.filters || {};

        // Pagination state
        this.currentPage = 1;
        this.perPage = config.defaultPerPage || 20;
        this.totalItems = 0;
        this.totalPages = 0;
        this.loading = false;

        // Callbacks
        this.onPageChange = config.onPageChange || null;

        this.init();
    }

    init() {
        this.renderControls();
        this.renderPagination();
        this.loadPage(1);
    }

    /**
     * Render top controls (per-page selector)
     */
    renderControls() {
        if (!this.controlsContainer) return;

        const perPageOptions = [10, 20, 50];
        const options = perPageOptions.map(value =>
            `<option value="${value}" ${value === this.perPage ? 'selected' : ''}>Zobrazit ${value}</option>`
        ).join('');

        this.controlsContainer.innerHTML = `
            <div class="pagination-controls">
                <label for="per-page-select" class="per-page-label">
                    <select id="per-page-select" class="per-page-select">
                        ${options}
                    </select>
                    knih na stránku
                </label>
            </div>
        `;

        // Event listener
        document.getElementById('per-page-select').addEventListener('change', (e) => {
            this.perPage = parseInt(e.target.value);
            this.currentPage = 1;
            this.loadPage(1);
        });
    }

    /**
     * Render bottom pagination (page numbers)
     */
    renderPagination() {
        if (!this.paginationContainer) return;

        if (this.totalPages <= 1) {
            this.paginationContainer.innerHTML = '';
            return;
        }

        let html = '<div class="pagination">';

        // Previous button
        if (this.currentPage > 1) {
            html += `<button class="page-btn" data-page="${this.currentPage - 1}">«</button>`;
        }

        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(this.totalPages, startPage + maxVisible - 1);

        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        // First page + ellipsis
        if (startPage > 1) {
            html += `<button class="page-btn" data-page="1">1</button>`;
            if (startPage > 2) {
                html += `<span class="page-ellipsis">...</span>`;
            }
        }

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === this.currentPage ? 'active' : '';
            html += `<button class="page-btn ${activeClass}" data-page="${i}">${i}</button>`;
        }

        // Last page + ellipsis
        if (endPage < this.totalPages) {
            if (endPage < this.totalPages - 1) {
                html += `<span class="page-ellipsis">...</span>`;
            }
            html += `<button class="page-btn" data-page="${this.totalPages}">${this.totalPages}</button>`;
        }

        // Next button
        if (this.currentPage < this.totalPages) {
            html += `<button class="page-btn" data-page="${this.currentPage + 1}">»</button>`;
        }

        html += '</div>';
        this.paginationContainer.innerHTML = html;

        // Event listeners
        this.paginationContainer.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = parseInt(btn.dataset.page);
                this.loadPage(page);
            });
        });
    }

    /**
     * Load specific page via AJAX
     */
    async loadPage(page) {
        if (this.loading) return;

        this.loading = true;
        this.currentPage = page;
        this.showLoading();

        try {
            const params = new URLSearchParams({
                page: page,
                limit: this.perPage,
                ...this.filters
            });

            const response = await fetch(`${this.apiEndpoint}?${params}`);
            const data = await response.json();

            if (data.success) {
                this.totalItems = data.total || 0;
                this.totalPages = Math.ceil(this.totalItems / this.perPage);

                this.renderItems(data.items || data.books || []);
                this.renderPagination();

                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Callback
                if (this.onPageChange) {
                    this.onPageChange(page, data);
                }
            }
        } catch (error) {
            console.error('Pagination load error:', error);
            window.toast.error('Chyba při načítání stránky');
        } finally {
            this.loading = false;
            this.hideLoading();
        }
    }

    /**
     * Render items in container
     */
    renderItems(items) {
        if (!this.container) return;

        if (items.length === 0) {
            this.container.innerHTML = '<div class="no-results">Žádné výsledky</div>';
            return;
        }

        this.container.innerHTML = items.map(item => this.renderItem(item)).join('');
    }

    /**
     * Show loading indicator
     */
    showLoading() {
        if (this.container) {
            this.container.classList.add('loading');
        }
    }

    /**
     * Hide loading indicator
     */
    hideLoading() {
        if (this.container) {
            this.container.classList.remove('loading');
        }
    }

    /**
     * Update filters and reload
     */
    setFilters(newFilters) {
        this.filters = { ...this.filters, ...newFilters };
        this.currentPage = 1;
        this.loadPage(1);
    }

    /**
     * Reload current page
     */
    reload() {
        this.loadPage(this.currentPage);
    }
}

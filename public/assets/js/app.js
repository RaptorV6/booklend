// ═══════════════════════════════════════════════════════════
// BOOKLEND - Main JavaScript
// ═══════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

    // ════════════════════════════════════════════════════════
    // Live Search
    // ════════════════════════════════════════════════════════

    const searchInput = document.querySelector('#search-input');
    const searchResults = document.querySelector('#search-results');

    if (searchInput && searchResults) {
        let searchTimeout;

        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);

            const query = e.target.value.trim();

            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`${getBaseUrl()}/api/search?q=${encodeURIComponent(query)}`);
                    const data = await response.json();

                    displaySearchResults(data.items || []);
                } catch (error) {
                    console.error('Search error:', error);
                }
            }, 300);
        });

        // Close search results when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }

    function displaySearchResults(books) {
        if (!searchResults) return;

        if (books.length === 0) {
            searchResults.innerHTML = '<div class="search-result-item">Žádné výsledky</div>';
            searchResults.style.display = 'block';
            return;
        }

        const html = books.map(book => `
            <a href="${getBaseUrl()}/kniha/${escapeHtml(book.slug)}" class="search-result-item">
                <strong>${escapeHtml(book.title)}</strong>
                <span>${escapeHtml(book.author)}</span>
            </a>
        `).join('');

        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
    }

    // ════════════════════════════════════════════════════════
    // Navbar Scroll Effect
    // ════════════════════════════════════════════════════════

    const navbar = document.querySelector('.navbar');

    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ════════════════════════════════════════════════════════
    // Helpers
    // ════════════════════════════════════════════════════════

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getBaseUrl() {
        // Extract base URL from current page
        const scripts = document.querySelectorAll('script[src]');
        for (let script of scripts) {
            const src = script.getAttribute('src');
            if (src.includes('/assets/js/')) {
                return src.split('/assets/')[0];
            }
        }
        return '';
    }
});

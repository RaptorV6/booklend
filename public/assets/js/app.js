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
    // Lazy Loading Images
    // ════════════════════════════════════════════════════════

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.dataset.src;

                if (src) {
                    img.src = src;
                    img.addEventListener('load', () => {
                        img.classList.add('loaded');
                    });
                    observer.unobserve(img);
                }
            }
        });
    }, {
        rootMargin: '50px' // Start loading 50px before image enters viewport
    });

    // Observe all lazy images
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });

    // ════════════════════════════════════════════════════════
    // Infinite Scroll for Book Catalog
    // ════════════════════════════════════════════════════════

    const bookGrid = document.getElementById('book-grid');
    const loadingMore = document.getElementById('loading-more');
    const noMoreBooks = document.getElementById('no-more-books');

    if (bookGrid && loadingMore) {
        // Infinite scroll observer
        const loadMoreObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && window.bookCatalogHasMore && !window.bookCatalogLoading) {
                    loadMoreBooks();
                }
            });
        }, {
            rootMargin: '200px' // Trigger 200px before reaching bottom
        });

        // Observe loading indicator
        loadMoreObserver.observe(loadingMore);

        async function loadMoreBooks() {
            if (window.bookCatalogLoading || !window.bookCatalogHasMore) return;

            window.bookCatalogLoading = true;
            window.bookCatalogPage++;

            loadingMore.style.display = 'block';

            try {
                const response = await fetch(`${getBaseUrl()}/api/books?page=${window.bookCatalogPage}&limit=12`);
                const data = await response.json();

                if (response.ok && data.books) {
                    // Add new books to grid
                    data.books.forEach(book => {
                        const bookCard = createBookCard(book);
                        bookGrid.appendChild(bookCard);
                    });

                    // Observe new lazy images
                    bookGrid.querySelectorAll('img[data-src]:not(.observing)').forEach(img => {
                        img.classList.add('observing');
                        imageObserver.observe(img);
                    });

                    window.bookCatalogHasMore = data.hasMore;

                    if (!data.hasMore) {
                        loadingMore.style.display = 'none';
                        noMoreBooks.style.display = 'block';
                    }
                } else {
                    console.error('Failed to load more books:', data);
                }
            } catch (error) {
                console.error('Error loading more books:', error);
            } finally {
                window.bookCatalogLoading = false;
                if (window.bookCatalogHasMore) {
                    loadingMore.style.display = 'none';
                }
            }
        }

        function createBookCard(book) {
            const card = document.createElement('div');
            card.className = 'book-card';

            const availableBadge = book.available_copies > 0
                ? `<span class="badge badge-available">Dostupné (${book.available_copies})</span>`
                : `<span class="badge badge-unavailable">Vypůjčeno</span>`;

            const coverHtml = book.thumbnail
                ? `<div class="book-cover">
                       <img src="${escapeHtml(book.thumbnail)}" alt="${escapeHtml(book.title)}" onload="this.classList.add('loaded')" style="width: 100%; height: 280px; object-fit: cover;">
                   </div>`
                : `<div class="book-cover" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                       📖
                   </div>`;

            card.innerHTML = `
                <a href="${getBaseUrl()}/kniha/${escapeHtml(book.slug)}">
                    ${coverHtml}
                    <div class="book-info">
                        <h3 class="book-title">${escapeHtml(book.title)}</h3>
                        <p class="book-author">${escapeHtml(book.author)}</p>
                        <div class="book-meta">
                            ${availableBadge}
                        </div>
                    </div>
                </a>
            `;

            return card;
        }
    }

    // ════════════════════════════════════════════════════════
    // Handle Broken Images (including Google's "image not found")
    // ════════════════════════════════════════════════════════

    document.addEventListener('error', (e) => {
        if (e.target.tagName === 'IMG') {
            const img = e.target;

            // Prevent infinite error loop
            if (img.dataset.errorHandled) return;
            img.dataset.errorHandled = 'true';

            // Create placeholder element
            const placeholder = document.createElement('div');
            placeholder.className = img.className.replace('loaded', '');
            placeholder.style.cssText = img.style.cssText || 'width: 100%; height: 280px;';
            placeholder.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            placeholder.style.display = 'flex';
            placeholder.style.alignItems = 'center';
            placeholder.style.justifyContent = 'center';
            placeholder.style.fontSize = '4rem';
            placeholder.textContent = '📖';

            // Replace image with placeholder
            img.parentNode.replaceChild(placeholder, img);
        }
    }, true); // Use capture phase to catch errors

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

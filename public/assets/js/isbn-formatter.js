/**
 * ISBN Formatter
 * Formats ISBN numbers for better readability
 */

/**
 * Format ISBN-10 or ISBN-13 with hyphens
 * @param {string} isbn - Raw ISBN without hyphens
 * @returns {string} Formatted ISBN with hyphens
 */
function formatISBN(isbn) {
    // Remove any existing hyphens or spaces
    const clean = isbn.replace(/[-\s]/g, '');

    // ISBN-13 (13 digits): 978-1-78110-750-8
    if (clean.length === 13) {
        return `${clean.slice(0, 3)}-${clean.slice(3, 4)}-${clean.slice(4, 9)}-${clean.slice(9, 12)}-${clean.slice(12)}`;
    }

    // ISBN-10 (10 digits): 0-439-70818-0
    if (clean.length === 10) {
        return `${clean.slice(0, 1)}-${clean.slice(1, 4)}-${clean.slice(4, 9)}-${clean.slice(9)}`;
    }

    // If not standard length, return as-is
    return isbn;
}

/**
 * Format all ISBN elements on page load
 */
function formatAllISBNs() {
    // Format in admin table
    document.querySelectorAll('.isbn-badge').forEach(el => {
        el.textContent = formatISBN(el.textContent);
    });

    // Format in book detail
    document.querySelectorAll('.isbn-formatted').forEach(el => {
        el.textContent = formatISBN(el.textContent);
    });
}

// Auto-format on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', formatAllISBNs);
} else {
    formatAllISBNs();
}

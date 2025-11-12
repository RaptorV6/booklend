// ═══════════════════════════════════════════════════════════
// BOOKLEND - Helper Functions (DRY)
// ═══════════════════════════════════════════════════════════

/**
 * Escape HTML to prevent XSS attacks
 * @param {string|any} text - Text to escape
 * @returns {string} - Escaped HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    if (typeof text !== 'string') return text;
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Debounce function to limit function execution rate
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} - Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format date to Czech locale
 * @param {string|Date} date - Date to format
 * @returns {string} - Formatted date
 */
function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('cs-CZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Format date to relative time (e.g., "před 2 dny")
 * @param {string|Date} date - Date to format
 * @returns {string} - Relative time
 */
function formatRelativeTime(date) {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) return `před ${days} ${days === 1 ? 'dnem' : 'dny'}`;
    if (hours > 0) return `před ${hours} ${hours === 1 ? 'hodinou' : 'hodinami'}`;
    if (minutes > 0) return `před ${minutes} ${minutes === 1 ? 'minutou' : 'minutami'}`;
    return 'právě teď';
}

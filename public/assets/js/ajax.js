// ═══════════════════════════════════════════════════════════
// BOOKLEND - AJAX Functions with Toast Notifications
// ═══════════════════════════════════════════════════════════

/**
 * Get base URL
 */
function getBaseUrl() {
    const scripts = document.querySelectorAll('script[src]');
    for (let script of scripts) {
        const src = script.getAttribute('src');
        if (src.includes('/assets/js/')) {
            return src.split('/assets/')[0];
        }
    }
    return '';
}

/**
 * Show confirmation dialog using toast-style
 * Returns a promise that resolves to true/false
 */
function confirmToast(message) {
    return new Promise((resolve) => {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';

        const modal = document.createElement('div');
        modal.className = 'confirm-modal';

        modal.innerHTML = `
            <div class="confirm-content">
                <div class="confirm-icon">?</div>
                <p class="confirm-message">${escapeHtml(message)}</p>
                <div class="confirm-actions">
                    <button class="confirm-btn confirm-cancel">Zrušit</button>
                    <button class="confirm-btn confirm-ok">Potvrdit</button>
                </div>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Animate in
        setTimeout(() => {
            overlay.classList.add('active');
            modal.classList.add('active');
        }, 10);

        // Event handlers
        const okBtn = modal.querySelector('.confirm-ok');
        const cancelBtn = modal.querySelector('.confirm-cancel');

        const cleanup = (result) => {
            overlay.classList.remove('active');
            modal.classList.remove('active');
            setTimeout(() => overlay.remove(), 300);
            resolve(result);
        };

        okBtn.addEventListener('click', () => cleanup(true));
        cancelBtn.addEventListener('click', () => cleanup(false));
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) cleanup(false);
        });
    });
}

/**
 * Escape HTML and convert newlines to <br>
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}

/**
 * Rent book
 */
async function rentBook(bookId) {
    const message = `Opravdu chcete půjčit tuto knihu?\n\n📅 Podmínky půjčení:\n• Výpůjční doba: 30 dní\n• Prodloužení: kdykoliv o 15 dní (placené)\n• Penalizace: 100 000 Kč za každý týden zpoždění`;
    const confirmed = await confirmToast(message);
    if (!confirmed) return;

    try {
        const response = await fetch(`${getBaseUrl()}/api/rent`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ book_id: bookId })
        });

        let data;
        try {
            data = await response.json();
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            window.toast.error('Neplatná odpověď serveru');
            return;
        }

        if (response.ok && data.success) {
            window.toast.success(data.message || 'Kniha byla úspěšně půjčena!');

            // Update UI without reload
            updateBookAvailability(-1);
            updateRentButton(bookId, true);
        } else {
            console.error('Rent failed:', data);
            window.toast.error(data.error || 'Chyba při půjčování knihy');
        }
    } catch (error) {
        console.error('Rent error:', error);
        window.toast.error('Nastala chyba. Zkuste to prosím znovu.');
    }
}

/**
 * Return book
 */
async function returnBook(rentalId) {
    const confirmed = await confirmToast('Opravdu chcete vrátit tuto knihu?');
    if (!confirmed) return;

    try {
        const response = await fetch(`${getBaseUrl()}/api/return`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ rental_id: rentalId })
        });

        let data;
        try {
            data = await response.json();
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            window.toast.error('Neplatná odpověď serveru');
            return;
        }

        if (response.ok && data.success) {
            window.toast.success(data.message || 'Kniha byla úspěšně vrácena!');

            // Reload on return (user is on "My Rentals" page)
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('Return failed:', data);
            window.toast.error(data.error || 'Chyba při vracení knihy');
        }
    } catch (error) {
        console.error('Return error:', error);
        window.toast.error('Nastala chyba při vracení.');
    }
}

/**
 * Extend rental (add 15 days to due date)
 */
async function extendRental(rentalId) {
    const confirmed = await confirmToast('Prodloužit výpůjčku o 15 dní? (Bude účtován poplatek)');
    if (!confirmed) return;

    try {
        const response = await fetch(`${getBaseUrl()}/api/extend`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ rental_id: rentalId })
        });

        let data;
        try {
            data = await response.json();
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            window.toast.error('Neplatná odpověď serveru');
            return;
        }

        if (response.ok && data.success) {
            window.toast.success(data.message || 'Výpůjčka byla úspěšně prodloužena!');

            // Reload to show updated due date and extension count
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('Extend failed:', data);
            window.toast.error(data.error || 'Chyba při prodloužení výpůjčky');
        }
    } catch (error) {
        console.error('Extend error:', error);
        window.toast.error('Nastala chyba při prodloužení.');
    }
}

/**
 * Update book availability count on detail page
 * @param {number} change - Amount to change (-1 for rent, +1 for return)
 */
function updateBookAvailability(change) {
    const availabilityEl = document.getElementById('availability-count');
    if (!availabilityEl) return;

    const currentAvailable = parseInt(availabilityEl.dataset.available);
    const total = parseInt(availabilityEl.dataset.total);
    const newAvailable = currentAvailable + change;

    if (newAvailable < 0 || newAvailable > total) return;

    availabilityEl.dataset.available = newAvailable;
    availabilityEl.textContent = `${newAvailable} / ${total}`;
}

/**
 * Update rent button state on detail page
 * @param {number} bookId - Book ID
 * @param {boolean} isRented - Whether book is now rented
 */
function updateRentButton(bookId, isRented) {
    const button = document.getElementById('rent-button');
    if (!button) return;

    if (isRented) {
        button.className = 'btn btn-secondary';
        button.disabled = true;
        button.textContent = 'Již vypůjčeno';
        button.removeAttribute('onclick');
    }
}

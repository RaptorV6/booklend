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
 * Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Rent book
 */
async function rentBook(bookId) {
    const confirmed = await confirmToast('Opravdu chcete půjčit tuto knihu?');
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
            setTimeout(() => location.reload(), 1500);
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

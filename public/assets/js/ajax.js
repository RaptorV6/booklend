// ═══════════════════════════════════════════════════════════
// BOOKLEND - AJAX Functions
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
 * Rent book
 */
async function rentBook(bookId) {
    if (!confirm('Opravdu chcete půjčit tuto knihu?')) {
        return;
    }

    try {
        const response = await fetch(`${getBaseUrl()}/api/rent`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ book_id: bookId })
        });

        // Try to parse JSON response
        let data;
        try {
            data = await response.json();
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            data = { error: 'Neplatná odpověď serveru' };
        }

        if (response.ok && data.success) {
            alert(data.message || 'Kniha byla úspěšně půjčena!');
            location.reload();
        } else {
            console.error('Rent failed:', data);
            alert(data.error || 'Chyba při půjčování knihy');
        }
    } catch (error) {
        console.error('Rent error:', error);
        alert('Nastala chyba. Zkuste to prosím znovu.');
    }
}

/**
 * Return book
 */
async function returnBook(rentalId) {
    if (!confirm('Opravdu chcete vrátit tuto knihu?')) {
        return;
    }

    try {
        const response = await fetch(`${getBaseUrl()}/api/return`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ rental_id: rentalId })
        });

        // Try to parse JSON response
        let data;
        try {
            data = await response.json();
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            data = { error: 'Neplatná odpověď serveru' };
        }

        if (response.ok && data.success) {
            alert(data.message || 'Kniha byla úspěšně vrácena!');
            location.reload();
        } else {
            console.error('Return failed:', data);
            alert(data.error || 'Chyba při vracení knihy');
        }
    } catch (error) {
        console.error('Return error:', error);
        alert('Nastala chyba při vracení.');
    }
}

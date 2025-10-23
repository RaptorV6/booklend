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

        const data = await response.json();

        if (response.ok) {
            alert('Kniha byla úspěšně půjčena!');
            location.reload();
        } else {
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

        const data = await response.json();

        if (response.ok) {
            alert('Kniha byla úspěšně vrácena!');
            location.reload();
        } else {
            alert(data.error || 'Chyba při vracení knihy');
        }
    } catch (error) {
        console.error('Return error:', error);
        alert('Nastala chyba při vracení.');
    }
}

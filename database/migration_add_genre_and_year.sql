-- ═══════════════════════════════════════════════════════════
-- MIGRACE: Přidání genre a published_year do books
-- Důvod: Kód používá tyto sloupce pro filtrování, ale v DB schématu chybí
-- ═══════════════════════════════════════════════════════════

USE booklend;

-- Přidání sloupců
ALTER TABLE books
ADD COLUMN genre VARCHAR(100) NULL AFTER author,
ADD COLUMN published_year YEAR NULL AFTER genre;

-- Indexy pro rychlé filtrování
ALTER TABLE books
ADD INDEX idx_genre (genre, deleted_at),
ADD INDEX idx_year (published_year, deleted_at);

-- Kombinovaný index pro filtry (když filtruješ žánr + rok zároveň)
ALTER TABLE books
ADD INDEX idx_genre_year (genre, published_year, deleted_at);

-- ═══════════════════════════════════════════════════════════
-- VOLITELNĚ: Aktualizovat existující data z Google Books API
-- ═══════════════════════════════════════════════════════════

-- Pokud už máš knihy v DB a chceš je doplnit o žánr/rok,
-- můžeš spustit PHP skript který:
-- 1. Načte všechny knihy
-- 2. Pro každou zavolá fetchMetadata()
-- 3. Aktualizuje genre a published_year

-- Příklad ruční aktualizace (pokud znáš data):
-- UPDATE books SET genre = 'Fantasy', published_year = 2001 WHERE isbn = '9780439708180';
-- UPDATE books SET genre = 'Sci-Fi', published_year = 1949 WHERE isbn = '9780451524935';

-- ═══════════════════════════════════════════════════════════
-- KONTROLA
-- ═══════════════════════════════════════════════════════════

-- Zobrazit sloupce tabulky books
DESCRIBE books;

-- Zobrazit všechny indexy
SHOW INDEX FROM books;

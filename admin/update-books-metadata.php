<?php
// Update all books with Google Books API metadata

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes

require __DIR__ . '/../config.php';

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$cache = new app\Cache(CACHE_DIR, CACHE_MAX_SIZE_MB, CACHE_MAX_FILES);
$bookModel = new app\Models\Book($db, $cache);

echo "<h1>📚 Aktualizace metadat knih z Google Books API</h1>";
echo "<p>Tento skript stáhne metadata (obrázky, popisy, atd.) pro všechny knihy v databázi.</p>";
echo "<hr>";

// Get all books
$books = $db->fetchAll("SELECT id, isbn, title FROM books WHERE deleted_at IS NULL");

echo "<p>Nalezeno <strong>" . count($books) . "</strong> knih k aktualizaci...</p>";
echo "<div style='background: #1e293b; padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 12px;'>";

$updated = 0;
$failed = 0;

foreach ($books as $book) {
    echo "<br><strong>➤ {$book['title']}</strong> (ISBN: {$book['isbn']})<br>";

    // Fetch metadata from API
    $metadata = $bookModel->fetchMetadata($book['isbn']);

    if ($metadata && !empty($metadata['thumbnail'])) {
        // Update database
        $db->query(
            "UPDATE books SET
                description = ?,
                thumbnail = ?
             WHERE id = ?",
            [
                $metadata['description'],
                $metadata['thumbnail'],
                $book['id']
            ]
        );

        echo "  ✓ Aktualizováno! Thumbnail: <a href='{$metadata['thumbnail']}' target='_blank'>Zobrazit</a><br>";
        $updated++;

        // Small delay to avoid API rate limiting
        usleep(200000); // 0.2 second
    } else {
        echo "  ✗ Metadata nenalezena nebo bez obrázku<br>";
        $failed++;
    }

    flush();
    ob_flush();
}

echo "</div>";

echo "<hr>";
echo "<h2>📊 Výsledky:</h2>";
echo "<ul>";
echo "<li><strong style='color: #10b981;'>✓ Aktualizováno:</strong> {$updated} knih</li>";
echo "<li><strong style='color: #ef4444;'>✗ Selhalo:</strong> {$failed} knih</li>";
echo "</ul>";

echo "<p><a href='/booklend/public/'>← Zpět na katalog</a></p>";
echo "<p><em>Poznámka: Některé knihy nemusí mít obrázky v Google Books API.</em></p>";

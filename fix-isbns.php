<?php
// Fix ISBN for books without covers

require __DIR__ . '/config.php';

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);

echo "<h1>🔧 Fixing ISBN for books</h1>";
echo "<hr>";

// Update Alchymista
$result1 = $db->query(
    "UPDATE books SET isbn = ? WHERE slug = ?",
    ['9780062315007', 'alchymista']
);
echo "<p>✓ Alchymista ISBN updated to <strong>9780062315007</strong> (The Alchemist - 10th Anniversary Edition)</p>";

// Update Malý princ
$result2 = $db->query(
    "UPDATE books SET isbn = ? WHERE slug = ?",
    ['9780156012195', 'maly-princ']
);
echo "<p>✓ Malý princ ISBN updated to <strong>9780156012195</strong> (The Little Prince)</p>";

echo "<hr>";
echo "<h2>📋 Current books:</h2>";
$books = $db->fetchAll("SELECT title, isbn, slug FROM books WHERE deleted_at IS NULL ORDER BY title");
echo "<ul>";
foreach ($books as $book) {
    echo "<li><strong>{$book['title']}</strong>: ISBN {$book['isbn']}</li>";
}
echo "</ul>";

echo "<hr>";
echo "<p><a href='/booklend/update-books-metadata.php'>→ Run update-books-metadata.php</a></p>";
echo "<p><a href='/booklend/public/'>← Back to catalog</a></p>";

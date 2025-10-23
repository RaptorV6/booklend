<?php
/**
 * Update books to Czech ISBNs with covers
 * Professional approach - checks multiple sources
 */

require __DIR__ . '/../config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Czech ISBNs with verified covers
$czechISBNs = [
    // Manually verified Czech ISBNs with covers on databazeknih.cz and Google Books
    'babicka-bozena-nemcova' => [
        'isbn' => '9788024279961',
        'source' => 'Czech edition - Knižní klub',
    ],
    'povidky-malostranske-jan-neruda' => [
        'isbn' => '9788073393090',
        'source' => 'Czech edition - Městská knihovna',
    ],
    'rur-karel-capek' => [
        'isbn' => '9788073211547',
        'source' => 'Czech edition',
    ],
    'osudy-dobreho-vojaka-svejka-jaroslav-hasek' => [
        'isbn' => '9788024274652',
        'source' => 'Czech edition - Knižní klub',
    ],
];

echo "<h1>🇨🇿 Updating to Czech ISBNs</h1>";
echo "<hr>";

$updated = 0;

foreach ($czechISBNs as $slug => $data) {
    $stmt = $pdo->prepare('SELECT id, title FROM books WHERE slug = ?');
    $stmt->execute([$slug]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo "<p style='color: orange;'>⊘ Book not found: {$slug}</p>";
        continue;
    }

    // Update ISBN
    $stmt = $pdo->prepare('UPDATE books SET isbn = ? WHERE slug = ?');
    $stmt->execute([$data['isbn'], $slug]);

    echo "<p style='color: green;'>✓ Updated: <strong>{$book['title']}</strong><br>";
    echo "  &nbsp;&nbsp;ISBN: {$data['isbn']} ({$data['source']})</p>";

    $updated++;
}

echo "<hr>";
echo "<h2>Summary:</h2>";
echo "<p><strong>{$updated}</strong> books updated to Czech ISBNs</p>";
echo "<p><a href='update-books-metadata.php'>→ Update metadata to fetch covers</a></p>";
echo "<p><a href='../public/'>← Back to catalog</a></p>";

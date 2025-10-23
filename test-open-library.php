<?php
// Test Open Library API integration

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config.php';

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$cache = new app\Cache(CACHE_DIR, CACHE_MAX_SIZE_MB, CACHE_MAX_FILES);
$bookModel = new app\Models\Book($db, $cache);

echo "<h1>🧪 Test Open Library API + Fallback System</h1>";
echo "<hr>";

// Test ISBNs
$testISBNs = [
    '9780451524935' => '1984 by George Orwell',
    '9788025703673' => 'Alchymista (bad ISBN)',
    '9780156012188' => 'The Little Prince',
    '9780439708180' => 'Harry Potter',
    '9780547928227' => 'The Hobbit',
];

foreach ($testISBNs as $isbn => $title) {
    echo "<div style='background: #1e293b; padding: 1rem; margin: 1rem 0; border-radius: 8px;'>";
    echo "<h3 style='color: #60a5fa;'>📖 {$title}</h3>";
    echo "<p style='color: #94a3b8;'>ISBN: {$isbn}</p>";

    $metadata = $bookModel->fetchMetadata($isbn);

    if ($metadata && !empty($metadata['thumbnail'])) {
        echo "<p style='color: #10b981;'>✓ SUCCESS</p>";
        echo "<p><strong>Source:</strong> " . ($metadata['source'] ?? 'Unknown') . "</p>";
        echo "<p><strong>Thumbnail URL:</strong><br><a href='{$metadata['thumbnail']}' target='_blank' style='font-size: 11px; word-break: break-all;'>{$metadata['thumbnail']}</a></p>";
        echo "<p><strong>Preview:</strong></p>";
        echo "<img src='{$metadata['thumbnail']}' style='max-width: 300px; border: 2px solid #60a5fa; border-radius: 4px;'>";

        if (!empty($metadata['description'])) {
            echo "<p><strong>Description:</strong><br>" . substr($metadata['description'], 0, 200) . "...</p>";
        }
    } else {
        echo "<p style='color: #ef4444;'>✗ NO THUMBNAIL FOUND</p>";
    }

    echo "</div>";
}

echo "<p><a href='/booklend/public/'>← Zpět na katalog</a></p>";

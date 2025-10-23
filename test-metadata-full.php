<?php
// Test what metadata we get from APIs

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

echo "<h1>📖 Test Metadata from APIs</h1>";
echo "<hr>";

$testBooks = [
    '9780156012195' => 'The Little Prince (English)',
    '9780062315007' => 'The Alchemist (English)',
];

foreach ($testBooks as $isbn => $name) {
    echo "<div style='background: #1e293b; padding: 1rem; margin: 1rem 0; border-radius: 8px;'>";
    echo "<h3 style='color: #60a5fa;'>📚 {$name}</h3>";
    echo "<p style='color: #94a3b8;'>ISBN: {$isbn}</p>";

    $meta = $bookModel->fetchMetadata($isbn);

    if ($meta) {
        echo "<p><strong>Source:</strong> " . ($meta['source'] ?? 'Unknown') . "</p>";
        echo "<p><strong>Title:</strong> " . ($meta['title'] ?? 'NULL') . "</p>";
        echo "<p><strong>Authors:</strong> " . ($meta['authors'] ?? 'NULL') . "</p>";

        if (!empty($meta['description'])) {
            echo "<p><strong>Description:</strong><br>";
            echo "<div style='background: #0f172a; padding: 0.5rem; margin-top: 0.5rem; max-height: 200px; overflow-y: auto;'>";
            echo nl2br(htmlspecialchars($meta['description']));
            echo "</div></p>";
        } else {
            echo "<p><strong>Description:</strong> <span style='color: #ef4444;'>NULL</span></p>";
        }

        if (!empty($meta['thumbnail'])) {
            echo "<p><strong>Thumbnail:</strong><br><img src='{$meta['thumbnail']}' style='max-width: 200px; border: 2px solid #60a5fa;'></p>";
        }
    } else {
        echo "<p style='color: #ef4444;'>No metadata found</p>";
    }

    echo "</div>";
}

echo "<hr>";
echo "<h2>🇨🇿 Searching for Czech editions:</h2>";

// Try to find Czech ISBNs
$czechSearches = [
    'maly+princ+cesky' => 'Malý princ',
    'alchymista+paulo+coelho+cesky' => 'Alchymista',
];

foreach ($czechSearches as $query => $bookName) {
    echo "<h3>{$bookName}:</h3>";

    // Search Google Books
    $url = "https://www.googleapis.com/books/v1/volumes?q=" . $query . "&langRestrict=cs";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['items']) && count($data['items']) > 0) {
            echo "<ul>";
            foreach (array_slice($data['items'], 0, 3) as $item) {
                $vol = $item['volumeInfo'];
                $title = $vol['title'] ?? 'Unknown';
                $authors = isset($vol['authors']) ? implode(', ', $vol['authors']) : 'Unknown';
                $lang = $vol['language'] ?? 'Unknown';
                $hasImage = isset($vol['imageLinks']['thumbnail']) ? '✓' : '✗';

                $isbn = 'N/A';
                if (isset($vol['industryIdentifiers'])) {
                    foreach ($vol['industryIdentifiers'] as $id) {
                        if ($id['type'] === 'ISBN_13') {
                            $isbn = $id['identifier'];
                            break;
                        }
                    }
                }

                echo "<li><strong>{$title}</strong> by {$authors} (Lang: {$lang}, ISBN: {$isbn}, Image: {$hasImage})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No results found</p>";
        }
    }
}

echo "<p><a href='/booklend/public/'>← Back to catalog</a></p>";

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(120);

require __DIR__ . '/../config.php';

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$cache = new app\Cache(CACHE_DIR, CACHE_MAX_SIZE_MB, CACHE_MAX_FILES);
$bookModel = new app\Models\Book($db, $cache);

echo "<h1>🔍 Test Czech Books ISBNs</h1>";
echo "<style>
    .book-test { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
    .book-test.success { background: #d4edda; border-color: #c3e6cb; }
    .book-test.fail { background: #f8d7da; border-color: #f5c6cb; }
    .book-test img { max-width: 200px; margin: 10px 0; }
</style>";

// Test popular Czech editions with good covers
$testBooks = [
    // Harry Potter série (víme že funguje)
    ['9781781107508', 'Harry Potter a Kámen mudrců'],
    ['9781781107515', 'Harry Potter a Tajemná komnata'],
    ['9781781107522', 'Harry Potter a Vězeň z Azkabanu'],

    // Pán prstenů série
    ['9788025704745', 'Pán prstenů: Společenstvo prstenu'],
    ['9788025704752', 'Pán prstenů: Dvě věže'],

    // Hobit
    ['9788024283012', 'Hobit'],

    // Moderní YA bestsellery (Albatros vydání)
    ['9788000047935', 'Malý princ'],
    ['9788000061917', 'Harry Potter (Albatros)'],
    ['9788000057361', 'Čtyřlístek - kniha'],

    // Populární fantasy
    ['9788025718223', 'Hra o trůny'],
    ['9788074914515', 'Stmívání'],
    ['9788025719718', 'Hunger Games'],

    // České romány a klasika s novými vydáními
    ['9788024279985', 'Krakatit'],
    ['9788024283005', 'Válka s Mloky'],
    ['9788025707333', 'Saturnin'],
];

echo "<p>Testování " . count($testBooks) . " knih...</p><hr>";

$working = [];
$failed = [];

foreach ($testBooks as $book) {
    list($isbn, $expectedTitle) = $book;

    echo "<div class='book-test'>";
    echo "<h3>📖 {$expectedTitle}</h3>";
    echo "<p><small>ISBN: {$isbn}</small></p>";

    $metadata = $bookModel->fetchMetadata($isbn);

    if ($metadata && !empty($metadata['thumbnail'])) {
        echo "<div class='book-test success'>";
        echo "<p><strong style='color: green;'>✓ FUNGUJE!</strong></p>";
        echo "<p>Název: {$metadata['title']}</p>";
        echo "<p>Autor: {$metadata['authors']}</p>";
        echo "<p><img src='{$metadata['thumbnail']}' alt='Cover'></p>";
        echo "</div>";

        $working[] = [$isbn, $metadata['title'], $metadata['authors']];
    } else {
        echo "<div class='book-test fail'>";
        echo "<p><strong style='color: red;'>✗ NEFUNGUJE</strong> - žádný obrázek</p>";
        echo "</div>";

        $failed[] = $expectedTitle;
    }

    echo "</div>";

    usleep(100000); // 0.1s delay
}

echo "<hr>";
echo "<h2>📊 Výsledky:</h2>";
echo "<p><strong style='color: green;'>✓ Fungující:</strong> " . count($working) . " knih</p>";
echo "<p><strong style='color: red;'>✗ Nefungující:</strong> " . count($failed) . " knih</p>";

if (!empty($working)) {
    echo "<hr><h3>✅ Použitelné knihy pro reset-books.php:</h3>";
    echo "<pre>";
    foreach ($working as $book) {
        list($isbn, $title, $author) = $book;
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-',
            iconv('UTF-8', 'ASCII//TRANSLIT', $title . '-' . $author)));
        $slug = trim($slug, '-');

        echo "    ['{$isbn}', '{$title}', '{$author}', '{$slug}'],\n";
    }
    echo "</pre>";
}

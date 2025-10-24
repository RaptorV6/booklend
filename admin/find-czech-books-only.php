<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(180);

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

echo "<h1>🇨🇿 Hledání POUZE českých knih s obrázky</h1>";
echo "<style>
    .book { margin: 15px; padding: 15px; border: 2px solid #ddd; border-radius: 8px; display: inline-block; width: 300px; }
    .book.czech { border-color: #10b981; background: #d1fae5; }
    .book.foreign { border-color: #ef4444; background: #fee2e2; }
    .book img { max-width: 150px; display: block; margin: 10px 0; }
</style>";

// Knihy které VYPADAJÍ jako české
$czechCandidates = [
    // Harry Potter - české vydání Albatros
    ['9788000047768', 'Harry Potter a Kámen mudrců (Albatros 2015)'],
    ['9788000047720', 'Harry Potter a Tajemná komnata (Albatros)'],
    ['9788000061917', 'Harry Potter a Kámen mudrců (Albatros 2021)'],
    ['9788000061924', 'Harry Potter a Tajemná komnata (Albatros 2021)'],

    // Narnie - české vydání Portál
    ['9788025326800', 'Narnie: Lev, čarodějnice a skříň'],
    ['9788025326923', 'Narnie: Stříbrná židle'],
    ['9788025326831', 'Narnie: Kůň a jeho chlapec'],
    ['9788025326770', 'Narnie: Čarodějův synovec'],

    // Malý princ
    ['9788000047935', 'Malý princ (Albatros)'],
    ['9788025347478', 'Malý princ (Portál)'],

    // Deník malého poseroutky
    ['9788000022253', 'Deník malého poseroutky'],
    ['9788075447210', 'Deník malého poseroutky: Radosti zimy'],
    ['9788000061214', 'Deník malého poseroutky: Psí život'],

    // Pán prstenů - české vydání
    ['9788025707227', 'Pán prstenů: Společenstvo prstenu'],
    ['9788025705735', 'Hobit'],

    // Eragon
    ['9788025315217', 'Eragon (Portál)'],

    // Dívka v modrém - česká autorka
    ['9788025357927', 'Dívka v modrém'],

    // Čtyřlístek
    ['9788000057361', 'Čtyřlístek: Velká kniha'],
    ['9788000061238', 'Čtyřlístek: Příběhy'],
];

$czechWithCovers = [];
$failed = [];

foreach ($czechCandidates as $candidate) {
    list($isbn, $expectedTitle) = $candidate;

    echo "<div class='book'>";
    echo "<h3>{$expectedTitle}</h3>";
    echo "<small>ISBN: {$isbn}</small><br>";

    $metadata = $bookModel->fetchMetadata($isbn);

    if ($metadata && !empty($metadata['thumbnail'])) {
        $lang = '??';
        $title = $metadata['title'] ?? '??';
        $author = $metadata['authors'] ?? '??';

        // Check if Czech (has Czech characters or known Czech publishers)
        $isCzech = (
            preg_match('/[ěščřžýáíéúůďťň]/iu', $title . $author) ||
            stripos($title, 'Albatros') !== false ||
            stripos($title, 'Portál') !== false ||
            stripos($title, 'Fragment') !== false
        );

        if ($isCzech) {
            echo "<div style='color: #10b981; font-weight: bold;'>✓ ČESKÁ KNIHA S OBRÁZKEM!</div>";
            echo "<p><strong>{$title}</strong><br>{$author}</p>";
            echo "<img src='{$metadata['thumbnail']}'>";

            $czechWithCovers[] = [
                'isbn' => $isbn,
                'title' => $title,
                'author' => $author,
                'thumbnail' => $metadata['thumbnail']
            ];

            echo "</div>";
            echo "<script>document.querySelector('.book:last-child').classList.add('czech');</script>";
        } else {
            echo "<div style='color: #f59e0b;'>⚠ Má obrázek, ale možná není česká</div>";
            echo "<p>{$title}<br>{$author}</p>";
            echo "<img src='{$metadata['thumbnail']}'>";
            echo "</div>";
        }
    } else {
        echo "<div style='color: #ef4444;'>✗ Žádný obrázek</div>";
        echo "</div>";
        echo "<script>document.querySelector('.book:last-child').classList.add('foreign');</script>";
        $failed[] = $expectedTitle;
    }

    usleep(250000); // 0.25s delay
}

echo "<hr>";
echo "<h2>📊 Výsledek: " . count($czechWithCovers) . " českých knih s obrázky</h2>";

if (!empty($czechWithCovers)) {
    echo "<h3>✅ Pro reset-books.php:</h3>";
    echo "<pre>";
    foreach ($czechWithCovers as $book) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-',
            iconv('UTF-8', 'ASCII//TRANSLIT', $book['title'])));
        $slug = trim($slug, '-');

        echo "    ['{$book['isbn']}', '{$book['title']}', '{$book['author']}', '{$slug}'],\n";
    }
    echo "</pre>";
}

if (!empty($failed)) {
    echo "<h3 style='color: #ef4444;'>❌ Bez obrázku (" . count($failed) . " knih):</h3>";
    echo "<ul>";
    foreach ($failed as $title) {
        echo "<li>{$title}</li>";
    }
    echo "</ul>";
}

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

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

function checkImageSize($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $imageData = curl_exec($ch);
    $size = strlen($imageData);
    curl_close($ch);
    return $size;
}

echo "<h1>🔍 Hledání knih podle žánrů (s ověřenými obrázky)</h1>";
echo "<style>
    .book { margin: 15px; padding: 15px; border: 2px solid #10b981; border-radius: 8px; background: #d1fae5; display: inline-block; width: 300px; }
    .book.failed { border-color: #ef4444; background: #fee2e2; }
    .book img { max-width: 150px; display: block; margin: 10px 0; }
</style>";

// Knihy podle žánrů - česká vydání nebo mezinárodní s českými názvy
$genreBooks = [
    // === FANTASY ===
    ['genre' => 'Fantasy', 'isbn' => '9781781107539', 'czechTitle' => 'Harry Potter a Ohnivý pohár', 'czechAuthor' => 'J.K. Rowling'],
    ['genre' => 'Fantasy', 'isbn' => '9781781107546', 'czechTitle' => 'Harry Potter a Fénixův řád', 'czechAuthor' => 'J.K. Rowling'],
    ['genre' => 'Fantasy', 'isbn' => '9788025326848', 'czechTitle' => 'Narnie: Princ Kaspian', 'czechAuthor' => 'C.S. Lewis'],
    ['genre' => 'Fantasy', 'isbn' => '9788025326855', 'czechTitle' => 'Narnie: Plavba Jitřního poutníka', 'czechAuthor' => 'C.S. Lewis'],

    // === DETEKTIVKY ===
    ['genre' => 'Detektivka', 'isbn' => '9780062073501', 'czechTitle' => 'Sherlock Holmes: Studie v šarlatové', 'czechAuthor' => 'Arthur Conan Doyle'],
    ['genre' => 'Detektivka', 'isbn' => '9780062073488', 'czechTitle' => 'Sherlock Holmes: Pes baskervillský', 'czechAuthor' => 'Arthur Conan Doyle'],

    // === SCI-FI ===
    ['genre' => 'Sci-Fi', 'isbn' => '9780441172719', 'czechTitle' => 'Duna', 'czechAuthor' => 'Frank Herbert'],
    ['genre' => 'Sci-Fi', 'isbn' => '9780345391803', 'czechTitle' => 'Stopařův průvodce Galaxií', 'czechAuthor' => 'Douglas Adams'],

    // === DOBRODRUŽNÉ ===
    ['genre' => 'Dobrodružné', 'isbn' => '9780141192482', 'czechTitle' => 'Ostrov pokladů', 'czechAuthor' => 'Robert Louis Stevenson'],
    ['genre' => 'Dobrodružné', 'isbn' => '9780140449037', 'czechTitle' => 'Tři mušketýři', 'czechAuthor' => 'Alexandre Dumas'],

    // === KLASIKA ===
    ['genre' => 'Klasika', 'isbn' => '9780141439518', 'czechTitle' => 'Velký Gatsby', 'czechAuthor' => 'F. Scott Fitzgerald'],
    ['genre' => 'Klasika', 'isbn' => '9780141439556', 'czechTitle' => 'Na západní frontě klid', 'czechAuthor' => 'Erich Maria Remarque'],

    // === HOROR ===
    ['genre' => 'Horor', 'isbn' => '9780307743657', 'czechTitle' => 'Osvícení', 'czechAuthor' => 'Stephen King'],
    ['genre' => 'Horor', 'isbn' => '9780141439471', 'czechTitle' => 'Dracula', 'czechAuthor' => 'Bram Stoker'],
];

$verified = [];
$failed = [];

foreach ($genreBooks as $bookData) {
    $isbn = $bookData['isbn'];
    $genre = $bookData['genre'];
    $czechTitle = $bookData['czechTitle'];
    $czechAuthor = $bookData['czechAuthor'];

    echo "<div class='book'>";
    echo "<h3>{$genre}: {$czechTitle}</h3>";
    echo "<small>ISBN: {$isbn}</small><br>";

    $metadata = $bookModel->fetchMetadata($isbn);

    if ($metadata && !empty($metadata['thumbnail'])) {
        $imageSize = checkImageSize($metadata['thumbnail']);
        $sizeKB = round($imageSize / 1024, 2);

        if ($imageSize > 10000) { // > 10KB = real cover
            echo "<p style='color: #10b981;'><strong>✓ SKUTEČNÝ OBRÁZEK ({$sizeKB} KB)</strong></p>";
            echo "<p>{$metadata['title']}<br>{$metadata['authors']}</p>";
            echo "<img src='{$metadata['thumbnail']}'>";

            $verified[] = [
                'isbn' => $isbn,
                'title' => $czechTitle,
                'author' => $czechAuthor,
                'genre' => $genre
            ];
        } else {
            echo "<p style='color: #f59e0b;'><strong>⚠ PLACEHOLDER ({$sizeKB} KB)</strong></p>";
            $failed[] = $czechTitle;
            echo "</div><script>document.querySelector('.book:last-child').classList.add('failed');</script>";
        }
    } else {
        echo "<p style='color: #ef4444;'><strong>✗ BEZ OBRÁZKU</strong></p>";
        $failed[] = $czechTitle;
        echo "</div><script>document.querySelector('.book:last-child').classList.add('failed');</script>";
    }

    echo "</div>";
    usleep(250000); // 0.25s delay
}

echo "<hr>";
echo "<h2>📊 Výsledek: " . count($verified) . " ověřených knih s obrázky</h2>";

if (!empty($verified)) {
    echo "<h3>✅ Pro reset-books.php:</h3>";
    echo "<pre>";
    foreach ($verified as $book) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-',
            iconv('UTF-8', 'ASCII//TRANSLIT', $book['title'])));
        $slug = trim($slug, '-');

        echo "    // {$book['genre']}\n";
        echo "    ['{$book['isbn']}', '{$book['title']}', '{$book['author']}', '{$slug}'],\n";
    }
    echo "</pre>";
}

if (!empty($failed)) {
    echo "<h3 style='color: #ef4444;'>❌ Bez obrázku/placeholder (" . count($failed) . " knih):</h3>";
    echo "<ul>";
    foreach ($failed as $title) {
        echo "<li>{$title}</li>";
    }
    echo "</ul>";
}

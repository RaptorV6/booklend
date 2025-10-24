<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

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

function searchBook($title, $author = '') {
    $query = urlencode($title . ' ' . $author);
    $url = "https://www.googleapis.com/books/v1/volumes?q={$query}&langRestrict=cs&maxResults=3";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!isset($data['items'])) return null;

    foreach ($data['items'] as $item) {
        $vol = $item['volumeInfo'] ?? [];

        if (!empty($vol['imageLinks']['thumbnail'])) {
            $isbn = null;
            foreach ($vol['industryIdentifiers'] ?? [] as $id) {
                if ($id['type'] === 'ISBN_13') {
                    $isbn = $id['identifier'];
                    break;
                }
            }

            if (!$isbn) continue;

            $thumbnail = str_replace('http://', 'https://', $vol['imageLinks']['thumbnail']);
            $thumbnail = preg_replace('/[&?]zoom=\d+/', '', $thumbnail) . '&zoom=0';

            $size = checkImageSize($thumbnail);

            if ($size > 10000) { // Real cover
                return [
                    'isbn' => $isbn,
                    'title' => $vol['title'],
                    'author' => isset($vol['authors']) ? implode(', ', $vol['authors']) : 'N/A',
                    'thumbnail' => $thumbnail,
                    'size' => round($size / 1024, 2)
                ];
            }
        }
    }

    return null;
}

echo "<h1>🔍 Hledání dalších knih s HD obrázky</h1>";
echo "<style>
    .book { margin: 15px; padding: 15px; border: 2px solid #10b981; border-radius: 8px; background: #d1fae5; display: inline-block; width: 300px; }
    .book img { max-width: 150px; display: block; margin: 10px 0; }
</style>";

$wantedBooks = [
    // Různé žánry - hledáme české názvy
    ['Hobit', 'Tolkien'],
    ['Pán prstenů', ''],
    ['Šifra mistra Leonarda', 'Dan Brown'],
    ['1984', 'George Orwell'],
    ['Farmář', 'George Orwell'],
    ['Stopařův průvodce galaxií', 'Douglas Adams'],
    ['Sherlock Holmes', 'Conan Doyle'],
    ['Robinson Crusoe', 'Daniel Defoe'],
    ['Alenka v říši divů', 'Lewis Carroll'],
    ['Honzíkova cesta', 'Bojana Němcová'],
    ['Vánoční koleda', 'Charles Dickens'],
    ['Malá mořská víla', 'Hans Christian Andersen'],
];

$found = [];

foreach ($wantedBooks as $book) {
    list($title, $author) = $book;

    echo "<p>Hledám: <strong>{$title}</strong> ({$author})...</p>";

    $result = searchBook($title, $author);

    if ($result) {
        echo "<div class='book'>";
        echo "<h3>{$result['title']}</h3>";
        echo "<p>{$result['author']}</p>";
        echo "<p>ISBN: {$result['isbn']}</p>";
        echo "<p style='color: #10b981;'><strong>✓ {$result['size']} KB</strong></p>";
        echo "<img src='{$result['thumbnail']}'>";
        echo "</div>";

        $found[] = $result;
    } else {
        echo "<p style='color: #ef4444;'>✗ Nenalezeno s HD obrázkem</p>";
    }

    usleep(500000); // 0.5s delay
}

echo "<hr>";
echo "<h2>📊 Nalezeno: " . count($found) . " knih s HD obrázky</h2>";

if (!empty($found)) {
    echo "<h3>✅ Pro reset-books.php:</h3>";
    echo "<pre>";
    foreach ($found as $book) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-',
            iconv('UTF-8', 'ASCII//TRANSLIT', $book['title'])));
        $slug = trim($slug, '-');

        echo "    ['{$book['isbn']}', '{$book['title']}', '{$book['author']}', '{$slug}'],\n";
    }
    echo "</pre>";
}

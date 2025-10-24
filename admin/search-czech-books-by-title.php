<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(120);

echo "<h1>Hledání českých knih podle názvu</h1>";
echo "<style>.book { margin: 15px 0; padding: 10px; border: 1px solid #ddd; } .book img { max-width: 150px; }</style>";

function searchBookByTitle($title, $author = '') {
    $query = urlencode("intitle:{$title} inauthor:{$author}");
    $url = "https://www.googleapis.com/books/v1/volumes?q={$query}&langRestrict=cs&maxResults=5";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) return [];

    $data = json_decode($response, true);
    if (!isset($data['items'])) return [];

    $results = [];
    foreach ($data['items'] as $item) {
        $vol = $item['volumeInfo'] ?? [];

        // Only return books with images
        if (!empty($vol['imageLinks']['thumbnail'])) {
            $thumbnail = $vol['imageLinks']['thumbnail'];
            $thumbnail = preg_replace('/[&?]zoom=\d+/', '', $thumbnail) . '&zoom=0';

            $isbn = null;
            if (!empty($vol['industryIdentifiers'])) {
                foreach ($vol['industryIdentifiers'] as $id) {
                    if ($id['type'] === 'ISBN_13') {
                        $isbn = $id['identifier'];
                        break;
                    }
                }
            }

            $results[] = [
                'title' => $vol['title'] ?? 'N/A',
                'authors' => isset($vol['authors']) ? implode(', ', $vol['authors']) : 'N/A',
                'isbn' => $isbn,
                'thumbnail' => $thumbnail,
                'description' => $vol['description'] ?? '',
            ];
        }
    }

    return $results;
}

// Populární knihy co by mohly mít české vydání s obrázky
$searches = [
    ['Hobit', 'Tolkien'],
    ['Pán prstenů', 'Tolkien'],
    ['Percy Jackson', ''],
    ['Hunger Games', 'Collins'],
    ['Divergence', 'Roth'],
    ['Labyrint', 'Dashner'],
    ['Twilight', 'Meyer'],
    ['Narnie', 'Lewis'],
    ['Eragon', 'Paolini'],
    ['Artemis Fowl', 'Colfer'],
    ['Čarodějův učeň', ''],
    ['Deník malého poseroutky', 'Kinney'],
    ['Živí mrtví', ''],
];

$found = [];

foreach ($searches as $search) {
    list($title, $author) = $search;

    echo "<h2>🔍 {$title}" . ($author ? " - {$author}" : "") . "</h2>";

    $results = searchBookByTitle($title, $author);

    if (empty($results)) {
        echo "<p style='color: red;'>Žádné knihy s obrázky</p>";
        continue;
    }

    foreach ($results as $book) {
        if (!$book['isbn']) continue; // Skip books without ISBN

        echo "<div class='book' style='background: #d4edda;'>";
        echo "<strong>{$book['title']}</strong><br>";
        echo "Autor: {$book['authors']}<br>";
        echo "ISBN: {$book['isbn']}<br>";
        echo "<img src='{$book['thumbnail']}'><br>";
        echo "</div>";

        $found[] = $book;
    }

    usleep(200000); // 0.2s delay between searches
}

echo "<hr><h2>📋 Výsledek (" . count($found) . " knih s obrázky):</h2>";
echo "<pre>";
foreach ($found as $book) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-',
        iconv('UTF-8', 'ASCII//TRANSLIT', $book['title'])));
    $slug = trim($slug, '-');

    echo "['{$book['isbn']}', '{$book['title']}', '{$book['authors']}', '{$slug}'],\n";
}
echo "</pre>";

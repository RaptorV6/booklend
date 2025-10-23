<?php
// Search for Czech ISBNs for our books

echo "<h1>🇨🇿 Searching for Czech ISBN</h1>";
echo "<hr>";

$books = [
    'Alchymista' => 'Paulo Coelho',
    'Malý princ' => 'Antoine de Saint-Exupéry',
    '1984' => 'George Orwell',
    'Harry Potter a Kámen mudrců' => 'J.K. Rowling',
    'Hobit' => 'J.R.R. Tolkien',
];

foreach ($books as $title => $author) {
    echo "<h2>📚 {$title} - {$author}</h2>";

    // Try different search queries
    $queries = [
        urlencode($title . ' ' . $author . ' česky'),
        urlencode($title . ' ' . $author . ' czech'),
        urlencode($title . ' ' . $author),
    ];

    $found = false;

    foreach ($queries as $query) {
        if ($found) break;

        $url = "https://www.googleapis.com/books/v1/volumes?q={$query}&langRestrict=cs";

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
                echo "<h3>Results for query: " . urldecode($query) . "</h3>";
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background: #1e293b; color: white;'><th>Title</th><th>Authors</th><th>Publisher</th><th>Lang</th><th>ISBN-13</th><th>Image</th><th>Preview</th></tr>";

                foreach (array_slice($data['items'], 0, 5) as $item) {
                    $vol = $item['volumeInfo'];

                    $itemTitle = $vol['title'] ?? 'N/A';
                    $itemAuthors = isset($vol['authors']) ? implode(', ', $vol['authors']) : 'N/A';
                    $publisher = $vol['publisher'] ?? 'N/A';
                    $lang = $vol['language'] ?? 'N/A';

                    $isbn13 = 'N/A';
                    if (isset($vol['industryIdentifiers'])) {
                        foreach ($vol['industryIdentifiers'] as $id) {
                            if ($id['type'] === 'ISBN_13') {
                                $isbn13 = $id['identifier'];
                                break;
                            }
                        }
                    }

                    $hasImage = isset($vol['imageLinks']['thumbnail']) ? '✓' : '✗';
                    $thumbnail = $vol['imageLinks']['thumbnail'] ?? '';

                    $previewImg = '';
                    if ($thumbnail) {
                        $previewImg = "<img src='{$thumbnail}' style='max-height: 80px;'>";
                    }

                    // Highlight Czech language and matching titles
                    $rowStyle = '';
                    if ($lang === 'cs' && stripos($itemTitle, $title) !== false) {
                        $rowStyle = "style='background: #d1fae5;'";
                        $found = true;
                    } elseif ($lang === 'cs') {
                        $rowStyle = "style='background: #fef3c7;'";
                    }

                    echo "<tr {$rowStyle}>";
                    echo "<td>{$itemTitle}</td>";
                    echo "<td>{$itemAuthors}</td>";
                    echo "<td>{$publisher}</td>";
                    echo "<td><strong>{$lang}</strong></td>";
                    echo "<td><strong>{$isbn13}</strong></td>";
                    echo "<td>{$hasImage}</td>";
                    echo "<td>{$previewImg}</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
        }

        usleep(200000); // Rate limiting
    }

    echo "<hr>";
}

echo "<p><strong>Legend:</strong></p>";
echo "<ul>";
echo "<li style='background: #d1fae5; padding: 5px;'>Green = Czech language + matching title (BEST MATCH)</li>";
echo "<li style='background: #fef3c7; padding: 5px;'>Yellow = Czech language but different title</li>";
echo "<li>White = Other languages</li>";
echo "</ul>";

echo "<p><a href='/booklend/public/'>← Back to catalog</a></p>";

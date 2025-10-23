<?php
// Find alternative ISBNs for books

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Hledání alternativních ISBN</h1>";

$books = [
    'Alchymista' => [
        '9788025703673', // České vydání
        '9780062315007', // Anglické vydání
        '9780061122415', // Jiné anglické vydání
        '0061122416',
    ],
    'Malý princ' => [
        '9780156012188',
        '9780156013987',
        '9782070612758', // Francouzské původní
        '9788025331682', // České vydání
    ]
];

foreach ($books as $title => $isbns) {
    echo "<h2>{$title}</h2>";

    foreach ($isbns as $isbn) {
        $url = "https://www.googleapis.com/books/v1/volumes?q=isbn:{$isbn}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);

            if (isset($data['items']) && !empty($data['items'])) {
                $hasThumbnail = false;
                $thumbnailUrl = null;

                foreach ($data['items'] as $item) {
                    if (isset($item['volumeInfo']['imageLinks']['thumbnail'])) {
                        $hasThumbnail = true;
                        $thumbnailUrl = $item['volumeInfo']['imageLinks']['thumbnail'];
                        break;
                    }
                }

                if ($hasThumbnail) {
                    echo "<div style='background: #e8f5e9; padding: 10px; margin: 10px 0; border-left: 4px solid #10b981;'>";
                    echo "<strong>✓ ISBN: {$isbn}</strong> - MÁ OBRÁZEK!<br>";
                    echo "Items: " . count($data['items']) . "<br>";
                    echo "Thumbnail: <a href='{$thumbnailUrl}' target='_blank'>Zobrazit</a><br>";
                    echo "<img src='{$thumbnailUrl}' style='max-height: 100px; margin-top: 5px;'>";
                    echo "</div>";
                } else {
                    echo "<div style='background: #ffebee; padding: 10px; margin: 10px 0; border-left: 4px solid #ef4444;'>";
                    echo "<strong>✗ ISBN: {$isbn}</strong> - BEZ OBRÁZKU<br>";
                    echo "Items: " . count($data['items']) . " (ale žádný nemá thumbnail)";
                    echo "</div>";
                }
            } else {
                echo "<div style='background: #fef3c7; padding: 10px; margin: 10px 0; border-left: 4px solid #f59e0b;'>";
                echo "<strong>⚠ ISBN: {$isbn}</strong> - NENALEZENO v Google Books";
                echo "</div>";
            }
        }

        usleep(300000); // 0.3s delay mezi požadavky
    }

    echo "<hr>";
}

echo "<p><a href='/booklend/public/'>← Zpět na katalog</a></p>";

<?php
// Test if Google Books returns placeholder images

$testISBNs = [
    '9788000022253' => 'Deník malého poseroutky (může mít placeholder)',
    '9788075447210' => 'Deník: Radosti zimy (měl by mít obrázek)',
    '9781781107508' => 'Harry Potter (měl by mít obrázek)',
];

echo "<h1>Testing Google Books Placeholder Detection</h1>";
echo "<style>img { max-width: 200px; border: 2px solid #ddd; margin: 10px; }</style>";

foreach ($testISBNs as $isbn => $desc) {
    echo "<hr>";
    echo "<h2>{$desc}</h2>";
    echo "<p>ISBN: {$isbn}</p>";

    // Get Google Books API data
    $apiUrl = "https://www.googleapis.com/books/v1/volumes?q=isbn:{$isbn}";
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);

    if (isset($data['items'][0]['volumeInfo']['imageLinks'])) {
        $imageLinks = $data['items'][0]['volumeInfo']['imageLinks'];
        echo "<pre>";
        print_r($imageLinks);
        echo "</pre>";

        // Try thumbnail
        if (isset($imageLinks['thumbnail'])) {
            $thumbUrl = str_replace('http://', 'https://', $imageLinks['thumbnail']);
            $thumbUrl = preg_replace('/[&?]zoom=\d+/', '', $thumbUrl) . '&zoom=0';

            echo "<p><strong>Thumbnail URL:</strong> {$thumbUrl}</p>";

            // Download and check size
            $imageData = file_get_contents($thumbUrl);
            $size = strlen($imageData);
            $sizeKB = round($size / 1024, 2);

            echo "<p>Image size: <strong>{$sizeKB} KB</strong></p>";

            if ($size < 2000) {
                echo "<p style='color: red;'><strong>⚠ PLACEHOLDER!</strong> (velmi malý obrázek)</p>";
            } elseif ($size < 5000) {
                echo "<p style='color: orange;'><strong>⚠ Možný placeholder</strong> (malý obrázek)</p>";
            } else {
                echo "<p style='color: green;'><strong>✓ Skutečný obrázek</strong></p>";
            }

            // Show image
            $base64 = base64_encode($imageData);
            echo "<img src='data:image/png;base64,{$base64}' alt='Cover'>";
        }
    } else {
        echo "<p style='color: red;'>Žádné imageLinks v API!</p>";
    }
}

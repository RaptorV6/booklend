<?php
// Test Google Books zoom levels

$isbn = '9780451524935'; // 1984

$url = "https://www.googleapis.com/books/v1/volumes?q=isbn:{$isbn}";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['items'][0]['volumeInfo']['imageLinks'])) {
    $images = $data['items'][0]['volumeInfo']['imageLinks'];

    echo "<h1>Test Google Books Zoom Levels</h1>";

    foreach ($images as $type => $url) {
        echo "<h2>{$type}</h2>";
        echo "<p>Original URL: {$url}</p>";

        // Test different zoom levels
        for ($zoom = 0; $zoom <= 5; $zoom++) {
            $testUrl = preg_replace('/zoom=\d+/', "zoom={$zoom}", $url);

            echo "<div style='margin: 20px 0; padding: 10px; background: #f0f0f0;'>";
            echo "<strong>Zoom {$zoom}:</strong><br>";
            echo "<img src='{$testUrl}' style='max-width: 300px; border: 1px solid #ccc;' onerror=\"this.alt='NOT AVAILABLE'\">";
            echo "<br><small>{$testUrl}</small>";
            echo "</div>";
        }

        echo "<hr>";
    }
} else {
    echo "No images found";
}

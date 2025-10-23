<?php
// Test Google Books API

$isbn = '9780451524935'; // 1984 by George Orwell

$url = "https://www.googleapis.com/books/v1/volumes?q=isbn:{$isbn}";

echo "<h1>Testing Google Books API</h1>";
echo "<p>ISBN: {$isbn}</p>";
echo "<p>URL: <a href='{$url}' target='_blank'>{$url}</a></p>";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h2>Response (HTTP {$httpCode}):</h2>";

if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);

    echo "<h3>Total items found: " . ($data['totalItems'] ?? 0) . "</h3>";

    if (isset($data['items']) && !empty($data['items'])) {
        // Show all items with thumbnails
        echo "<h3>All Items:</h3>";
        foreach ($data['items'] as $index => $item) {
            $vol = $item['volumeInfo'] ?? [];
            $hasThumbnail = isset($vol['imageLinks']['thumbnail']);

            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: " . ($hasThumbnail ? '#e8f5e9' : '#ffebee') . "'>";
            echo "<strong>Item #{$index}:</strong> " . ($vol['title'] ?? 'N/A') . "<br>";
            echo "Has thumbnail: " . ($hasThumbnail ? '✓ YES' : '✗ NO') . "<br>";
            if ($hasThumbnail) {
                echo "<img src='{$vol['imageLinks']['thumbnail']}' style='max-width: 100px; margin-top: 10px;'><br>";
                echo "URL: {$vol['imageLinks']['thumbnail']}";
            }
            echo "</div>";
        }

        // Use our smart selection logic
        echo "<h3>Selected Item (using our logic):</h3>";
        $book = null;
        foreach ($data['items'] as $item) {
            if (isset($item['volumeInfo'])) {
                if (isset($item['volumeInfo']['imageLinks']['thumbnail'])) {
                    $book = $item['volumeInfo'];
                    echo "<p style='color: green;'>✓ Found item WITH thumbnail!</p>";
                    break;
                }
                if ($book === null) {
                    $book = $item['volumeInfo'];
                }
            }
        }

        if ($book) {
            echo "<ul>";
            echo "<li><strong>Title:</strong> " . ($book['title'] ?? 'N/A') . "</li>";
            echo "<li><strong>Authors:</strong> " . (isset($book['authors']) ? implode(', ', $book['authors']) : 'N/A') . "</li>";
            echo "<li><strong>Published:</strong> " . ($book['publishedDate'] ?? 'N/A') . "</li>";
            echo "<li><strong>Pages:</strong> " . ($book['pageCount'] ?? 'N/A') . "</li>";
            echo "<li><strong>Description:</strong> " . substr($book['description'] ?? 'N/A', 0, 200) . "...</li>";
            echo "</ul>";

            if (isset($book['imageLinks']['thumbnail'])) {
                echo "<h3>✓ Thumbnail Found:</h3>";
                echo "<img src='{$book['imageLinks']['thumbnail']}' alt='Book cover' style='max-width: 200px;'>";
                echo "<p>URL: {$book['imageLinks']['thumbnail']}</p>";
            } else {
                echo "<h3 style='color: red;'>✗ No thumbnail in selected item!</h3>";
            }
        }
    } else {
        echo "<p style='color: red;'>No items found in response!</p>";
    }
} else {
    echo "<p style='color: red;'>API call failed!</p>";
    echo "<p>Response: {$response}</p>";
}

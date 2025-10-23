<?php
// Test book detail fetch

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require __DIR__ . '/config.php';

$db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$cache = new app\Cache(CACHE_DIR, CACHE_MAX_SIZE_MB, CACHE_MAX_FILES);

$bookModel = new app\Models\Book($db, $cache);

echo "<h1>Test Book Detail Fetch</h1>";

$slug = '1984-george-orwell';

echo "<h2>1. Database Query:</h2>";
$bookFromDB = $bookModel->findBySlug($slug);
echo "<pre>";
print_r($bookFromDB);
echo "</pre>";

echo "<h2>2. API/Cache Metadata:</h2>";
if ($bookFromDB) {
    $metadata = $bookModel->fetchMetadata($bookFromDB['isbn']);
    echo "<pre>";
    print_r($metadata);
    echo "</pre>";

    echo "<h2>3. Complete Book (merged):</h2>";
    $complete = $bookModel->getComplete($slug);
    echo "<pre>";
    print_r($complete);
    echo "</pre>";

    echo "<h2>4. Thumbnail Check:</h2>";
    if (!empty($complete['thumbnail'])) {
        echo "<p style='color: green;'>✓ Thumbnail exists: {$complete['thumbnail']}</p>";
        echo "<img src='{$complete['thumbnail']}' style='max-width: 200px;'>";
    } else {
        echo "<p style='color: red;'>✗ No thumbnail!</p>";
    }
} else {
    echo "<p style='color: red;'>Book not found in database!</p>";
}

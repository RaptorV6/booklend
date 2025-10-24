<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../config.php';

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$cache = new app\Cache(CACHE_DIR, CACHE_MAX_SIZE_MB, CACHE_MAX_FILES);
$bookModel = new app\Models\Book($db, $cache);

echo "<h1>Test Czech ISBN Metadata</h1>";
echo "<hr>";

// Test Malý princ
$isbn = '9788000047935';
echo "<h2>Testing: {$isbn} (Malý princ)</h2>";

$metadata = $bookModel->fetchMetadata($isbn);

if ($metadata) {
    echo "<pre>";
    print_r($metadata);
    echo "</pre>";

    if (!empty($metadata['thumbnail'])) {
        echo "<img src='{$metadata['thumbnail']}' style='max-width: 300px;'>";
    }
} else {
    echo "<p style='color: red;'>No metadata found!</p>";
}

echo "<hr>";

// Check error log
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    echo "<h2>Recent Error Log:</h2>";
    echo "<pre>";
    echo htmlspecialchars(shell_exec("tail -n 30 " . escapeshellarg($logFile)));
    echo "</pre>";
}

<?php
echo "<h1>Index Test</h1>";

// Test autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    echo "Trying to load: $file<br>";
    if (file_exists($file)) {
        echo "✓ File exists!<br>";
        require_once $file;
    } else {
        echo "✗ File NOT found!<br>";
    }
});

// Test config load
echo "<h2>Loading config...</h2>";
if (file_exists(__DIR__ . '/../config.php')) {
    echo "✓ config.php exists<br>";
    require __DIR__ . '/../config.php';
    echo "BASE_URL: " . BASE_URL . "<br>";
} else {
    echo "✗ config.php NOT found<br>";
}

// Test Database class
echo "<h2>Testing Database class...</h2>";
try {
    $db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    echo "✓ Database class loaded successfully<br>";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>All tests completed!</h2>";

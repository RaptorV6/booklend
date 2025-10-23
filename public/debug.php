<?php
// ═══════════════════════════════════════════════════════════
// DEBUG - Test index.php step by step
// ═══════════════════════════════════════════════════════════

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>DEBUG MODE</h1>";

// Step 1: Autoloader
echo "<h2>1. Autoloader</h2>";
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
        echo "✓ Loaded: $class<br>";
    }
});

// Step 2: Config
echo "<h2>2. Config</h2>";
require __DIR__ . '/../config.php';
echo "✓ Config loaded<br>";
echo "BASE_URL: " . BASE_URL . "<br>";

// Step 3: Helpers
echo "<h2>3. Helpers</h2>";
require __DIR__ . '/../app/helpers.php';
echo "✓ Helpers loaded<br>";

// Step 4: Session
echo "<h2>4. Session</h2>";
session_start();
echo "✓ Session started<br>";

// Step 5: Database
echo "<h2>5. Database</h2>";
try {
    $db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    echo "✓ Database connected<br>";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
    die();
}

// Step 6: Cache
echo "<h2>6. Cache</h2>";
try {
    $cache = new app\Cache(CACHE_DIR, CACHE_MAX_SIZE_MB, CACHE_MAX_FILES);
    echo "✓ Cache initialized<br>";
} catch (Exception $e) {
    echo "✗ Cache error: " . $e->getMessage() . "<br>";
}

// Step 7: Router
echo "<h2>7. Router</h2>";
try {
    $router = new app\Router($db, $cache);
    echo "✓ Router created<br>";
} catch (Exception $e) {
    echo "✗ Router error: " . $e->getMessage() . "<br>";
    die();
}

// Step 8: Routes
echo "<h2>8. Routes</h2>";
require __DIR__ . '/../routes.php';
echo "✓ Routes loaded<br>";

// Step 9: Request info
echo "<h2>9. Request Info</h2>";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";

// Step 10: Dispatch
echo "<h2>10. Dispatching...</h2>";
try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    echo "✓ Dispatched successfully<br>";
} catch (Exception $e) {
    echo "✗ Dispatch error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>✓ All steps completed!</h2>";

<?php
// ═══════════════════════════════════════════════════════════
// BOOKLEND - Front Controller
// ═══════════════════════════════════════════════════════════

// Error reporting (development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader (PSR-4 style)
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load config
require __DIR__ . '/../config.php';
require __DIR__ . '/../app/helpers.php';

// Start session
session_start();

// Database
$db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);

// Cache
$cache = new app\Cache(CACHE_DIR, CACHE_MAX_SIZE_MB, CACHE_MAX_FILES);

// Router
$router = new app\Router($db, $cache);

// Load routes
require __DIR__ . '/../routes.php';

// Dispatch
try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require __DIR__ . '/../app/Views/errors/500.php';
}

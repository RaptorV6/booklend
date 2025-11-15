<?php
// ═══════════════════════════════════════════════════════════
// BOOKLEND - Configuration
// ═══════════════════════════════════════════════════════════

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'book');
define('DB_USER', 'root');
define('DB_PASS', '');

// Cache
define('CACHE_DIR', __DIR__ . '/public/cache');
define('CACHE_TTL', 2592000);        // 30 days
define('CACHE_MAX_SIZE_MB', 100);
define('CACHE_MAX_FILES', 10000);

// App
define('APP_NAME', 'BookLend');

// Auto-detect BASE_URL (works for both localhost and production)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

// Remove /public from path if present (production setup)
$scriptPath = str_replace('/public', '', $scriptPath);

define('BASE_URL', $protocol . $host . $scriptPath);

// Google Books API (high-resolution images + metadata)
define('GOOGLE_BOOKS_API', 'https://www.googleapis.com/books/v1/volumes');
define('GOOGLE_BOOKS_API_KEY', 'AIzaSyCRGRFxGdpwlp96t_ZaUgr43D8XSTwx_tA');

// Timezone
date_default_timezone_set('Europe/Prague');

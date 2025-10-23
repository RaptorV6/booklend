<?php
// ═══════════════════════════════════════════════════════════
// BOOKLEND - Configuration
// ═══════════════════════════════════════════════════════════

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'booklend');
define('DB_USER', 'root');
define('DB_PASS', '');

// Cache
define('CACHE_DIR', __DIR__ . '/public/cache');
define('CACHE_TTL', 2592000);        // 30 days
define('CACHE_MAX_SIZE_MB', 100);
define('CACHE_MAX_FILES', 10000);

// App
define('APP_NAME', 'BookLend');
define('BASE_URL', 'http://localhost/booklend/public');

// Google Books API
define('GOOGLE_BOOKS_API', 'https://www.googleapis.com/books/v1/volumes');

// Timezone
date_default_timezone_set('Europe/Prague');

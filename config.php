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

// Google Books API (fallback)
define('GOOGLE_BOOKS_API', 'https://www.googleapis.com/books/v1/volumes');
define('GOOGLE_BOOKS_API_KEY', 'AIzaSyCRGRFxGdpwlp96t_ZaUgr43D8XSTwx_tA');

// Open Library API (primary - better images, more reliable)
define('OPEN_LIBRARY_API', 'https://openlibrary.org/api/books');
define('OPEN_LIBRARY_COVERS', 'https://covers.openlibrary.org/b/isbn');

// Timezone
date_default_timezone_set('Europe/Prague');

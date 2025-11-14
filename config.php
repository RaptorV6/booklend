<?php
// ═══════════════════════════════════════════════════════════
// BOOKLEND - Configuration
// ═══════════════════════════════════════════════════════════

// Database
define('DB_HOST', 'sql113.infinityfree.com');
define('DB_NAME', 'if0_40416561_booklend');
define('DB_USER', 'if0_40416561');
define('DB_PASS', 'ModernWeb321');

// Cache
define('CACHE_DIR', __DIR__ . '/public/cache');
define('CACHE_TTL', 2592000);        // 30 days
define('CACHE_MAX_SIZE_MB', 100);
define('CACHE_MAX_FILES', 10000);

// App
define('APP_NAME', 'BookLend');
define('BASE_URL', 'http://booklend.ct.ws/');

// Google Books API (high-resolution images + metadata)
define('GOOGLE_BOOKS_API', 'https://www.googleapis.com/books/v1/volumes');
define('GOOGLE_BOOKS_API_KEY', 'AIzaSyCRGRFxGdpwlp96t_ZaUgr43D8XSTwx_tA');

// Timezone
date_default_timezone_set('Europe/Prague');

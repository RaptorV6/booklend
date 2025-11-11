<?php
// Load configuration and dependencies
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/Cache.php';
require_once __DIR__ . '/../app/Models/Book.php';

use app\Database;
use app\Cache;
use app\Models\Book;

// Set XML header
header('Content-Type: application/xml; charset=utf-8');

// Initialize database and models
$db = new Database();
$cache = new Cache();
$bookModel = new Book($db, $cache);

// Get all books (without limit for sitemap)
$books = $db->fetchAll(
    "SELECT slug, updated_at FROM books WHERE deleted_at IS NULL ORDER BY updated_at DESC"
);

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Homepage
echo '  <url>' . "\n";
echo '    <loc>' . htmlspecialchars(BASE_URL . '/') . '</loc>' . "\n";
echo '    <changefreq>daily</changefreq>' . "\n";
echo '    <priority>1.0</priority>' . "\n";
echo '  </url>' . "\n";

// Book detail pages
foreach ($books as $book) {
    $lastmod = !empty($book['updated_at']) ? date('Y-m-d', strtotime($book['updated_at'])) : date('Y-m-d');

    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars(BASE_URL . '/kniha/' . $book['slug']) . '</loc>' . "\n";
    echo '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
    echo '    <changefreq>weekly</changefreq>' . "\n";
    echo '    <priority>0.8</priority>' . "\n";
    echo '  </url>' . "\n";
}

echo '</urlset>';

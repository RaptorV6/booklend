<?php
// Load configuration
require_once __DIR__ . '/../config.php';

// Set plain text header
header('Content-Type: text/plain; charset=utf-8');

// Generate robots.txt content
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin\n";
echo "Disallow: /api/\n";
echo "\n";
echo "Sitemap: " . BASE_URL . "/sitemap.xml\n";

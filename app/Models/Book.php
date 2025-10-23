<?php
namespace app\Models;

use app\Database;
use app\Cache;

class Book {
    private Database $db;
    private Cache $cache;

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    // ════════════════════════════════════════════════════════
    // DATABASE QUERIES
    // ════════════════════════════════════════════════════════

    public function getAll(int $limit = 20): array {
        return $this->db->fetchAll(
            "SELECT * FROM books
             WHERE deleted_at IS NULL
             ORDER BY added_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function findBySlug(string $slug): ?array {
        return $this->db->fetch(
            "SELECT * FROM books WHERE slug = ? AND deleted_at IS NULL",
            [$slug]
        );
    }

    public function findById(int $id): ?array {
        return $this->db->fetch(
            "SELECT * FROM books WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function search(string $query): array {
        $like = "%{$query}%";

        return $this->db->fetchAll(
            "SELECT * FROM books
             WHERE (title LIKE ? OR author LIKE ?)
             AND deleted_at IS NULL
             LIMIT 20",
            [$like, $like]
        );
    }

    public function paginate(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;

        return $this->db->fetchAll(
            "SELECT * FROM books
             WHERE deleted_at IS NULL
             ORDER BY title ASC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    public function getTotalCount(): int {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM books WHERE deleted_at IS NULL");
        return $result['total'] ?? 0;
    }

    public function incrementViews(int $id): void {
        $this->db->query(
            "UPDATE books SET views_count = views_count + 1 WHERE id = ?",
            [$id]
        );
    }

    // ════════════════════════════════════════════════════════
    // API INTEGRATION (POUZE TU!)
    // ════════════════════════════════════════════════════════

    public function fetchMetadata(string $isbn): ?array {
        // Check cache (shared!)
        $cacheKey = "book:metadata:{$isbn}";

        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        // Hybrid approach: Get best of both APIs
        // - Open Library: Better quality images (when available)
        // - Google Books: Descriptions, metadata
        error_log("Fetching metadata for ISBN: {$isbn}");

        $openLibData = $this->callOpenLibraryAPI($isbn);
        $googleData = $this->callGoogleBooksAPI($isbn);

        // Combine: prefer Open Library image, Google Books description
        $combined = [
            'title' => null,
            'authors' => null,
            'description' => null,
            'thumbnail' => null,
            'published_date' => null,
            'page_count' => null,
            'source' => null,
        ];

        // Prefer Open Library thumbnail (better quality)
        if ($openLibData && !empty($openLibData['thumbnail'])) {
            $combined['thumbnail'] = $openLibData['thumbnail'];
            $combined['source'] = 'Open Library (image)';
            error_log("  ✓ Open Library: Using thumbnail");
        }

        // Get description and metadata from Google Books
        if ($googleData) {
            if (empty($combined['thumbnail']) && !empty($googleData['thumbnail'])) {
                $combined['thumbnail'] = $googleData['thumbnail'];
                $combined['source'] = 'Google Books';
                error_log("  ✓ Google Books: Using thumbnail");
            }

            // Always prefer Google Books description (Open Library usually has none)
            $combined['title'] = $googleData['title'] ?? $combined['title'];
            $combined['authors'] = $googleData['authors'] ?? $combined['authors'];
            $combined['description'] = $googleData['description'] ?? $combined['description'];
            $combined['published_date'] = $googleData['published_date'] ?? $combined['published_date'];
            $combined['page_count'] = $googleData['page_count'] ?? $combined['page_count'];

            if (!empty($googleData['description'])) {
                error_log("  ✓ Google Books: Found description (" . strlen($googleData['description']) . " chars)");
                if ($combined['source'] === 'Open Library (image)') {
                    $combined['source'] = 'Hybrid (OL image + GB data)';
                }
            }
        }

        // Only cache if we have at least a thumbnail
        if (!empty($combined['thumbnail'])) {
            $this->cache->set($cacheKey, $combined, CACHE_TTL);
            return $combined;
        }

        error_log("  ✗ No metadata found");
        return null;
    }

    private function callGoogleBooksAPI(string $isbn): ?array {
        // Note: API key is blocked, using without key for now
        // To use key: activate Google Books API in Google Cloud Console
        $url = GOOGLE_BOOKS_API . "?q=isbn:{$isbn}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            error_log("Google Books API failed for ISBN: {$isbn}");
            return null;
        }

        $data = json_decode($response, true);

        if (!isset($data['items']) || empty($data['items'])) {
            error_log("Google Books API: No items found for ISBN: {$isbn}");
            return null;
        }

        // DEBUG: Log what we got
        error_log("Google Books API: Found " . count($data['items']) . " items for ISBN: {$isbn}");

        // Find first item with thumbnail, or fall back to first item
        $book = null;
        $foundWithThumbnail = false;

        foreach ($data['items'] as $index => $item) {
            if (isset($item['volumeInfo'])) {
                $hasThumbnail = isset($item['volumeInfo']['imageLinks']['thumbnail']);

                // DEBUG: Log each item
                error_log("  Item #{$index}: " . ($item['volumeInfo']['title'] ?? 'No title') .
                         " | Thumbnail: " . ($hasThumbnail ? 'YES' : 'NO'));

                // Prefer items with thumbnail
                if ($hasThumbnail && !$foundWithThumbnail) {
                    $book = $item['volumeInfo'];
                    $foundWithThumbnail = true;
                    error_log("  → SELECTED (has thumbnail)");
                    break;
                }

                // Fallback to first item if no thumbnail found yet
                if ($book === null) {
                    $book = $item['volumeInfo'];
                }
            }
        }

        if (!$book) {
            error_log("Google Books API: No valid volumeInfo found");
            return null;
        }

        // Get best quality image available
        $thumbnail = null;
        if (isset($book['imageLinks'])) {
            // Priority: extraLarge > large > medium > thumbnail > smallThumbnail
            if (isset($book['imageLinks']['extraLarge'])) {
                $thumbnail = $book['imageLinks']['extraLarge'];
            } elseif (isset($book['imageLinks']['large'])) {
                $thumbnail = $book['imageLinks']['large'];
            } elseif (isset($book['imageLinks']['medium'])) {
                $thumbnail = $book['imageLinks']['medium'];
            } elseif (isset($book['imageLinks']['thumbnail'])) {
                $thumbnail = $book['imageLinks']['thumbnail'];
            } elseif (isset($book['imageLinks']['smallThumbnail'])) {
                $thumbnail = $book['imageLinks']['smallThumbnail'];
            }

            // Upgrade to maximum resolution by changing zoom parameter
            if ($thumbnail) {
                // Remove existing zoom parameter and add zoom=0 for highest quality
                $thumbnail = preg_replace('/[&?]zoom=\d+/', '', $thumbnail);
                $thumbnail .= (strpos($thumbnail, '?') !== false ? '&' : '?') . 'zoom=0';
            }
        }

        $result = [
            'title' => $book['title'] ?? null,
            'authors' => isset($book['authors']) ? implode(', ', $book['authors']) : null,
            'description' => $book['description'] ?? null,
            'thumbnail' => $thumbnail,
            'published_date' => $book['publishedDate'] ?? null,
            'page_count' => $book['pageCount'] ?? null,
        ];

        // DEBUG: Log final result
        error_log("Final metadata: " . json_encode($result));

        return $result;
    }

    private function callOpenLibraryAPI(string $isbn): ?array {
        // Open Library Direct Cover URL (much faster and more reliable than API)
        // Format: https://covers.openlibrary.org/b/isbn/{ISBN}-L.jpg
        // They return a default 1x1 pixel (43 bytes) image if cover doesn't exist
        // We'll download first 100 bytes to check if it's a real cover

        $coverUrl = OPEN_LIBRARY_COVERS . "/{$isbn}-L.jpg";

        $ch = curl_init($coverUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RANGE => '0-99', // Download only first 100 bytes
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        curl_close($ch);

        // HTTP 206 = Partial Content (success)
        // HTTP 200 = Full file (also success, if file is < 100 bytes)
        // Real covers are always > 43 bytes (1x1 pixel default image)
        if (($httpCode === 206 || $httpCode === 200) && $responseSize > 50) {
            error_log("Open Library: Found cover for ISBN: {$isbn} ({$responseSize} bytes sample)");

            return [
                'title' => null,
                'authors' => null,
                'description' => null,
                'thumbnail' => $coverUrl,
                'published_date' => null,
                'page_count' => null,
            ];
        }

        error_log("Open Library: No cover for ISBN: {$isbn} (HTTP: {$httpCode}, Size: {$responseSize} bytes)");
        return null;
    }

    // ════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════

    public function getComplete(string $slug): ?array {
        $book = $this->findBySlug($slug);

        if (!$book) {
            return null;
        }

        // Fetch metadata from API/cache
        $metadata = $this->fetchMetadata($book['isbn']);

        if ($metadata) {
            // Merge metadata but DON'T overwrite title and author from DB
            // (DB has correct Czech names, API might have different editions)
            $originalTitle = $book['title'];
            $originalAuthor = $book['author'];

            $book = array_merge($book, $metadata);

            // Restore original title and author from DB
            $book['title'] = $originalTitle;
            $book['author'] = $originalAuthor;
        }

        return $book;
    }
}

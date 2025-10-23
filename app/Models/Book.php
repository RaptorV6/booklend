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

        // API call
        $data = $this->callGoogleBooksAPI($isbn);

        // Save to cache (30 days)
        if ($data) {
            $this->cache->set($cacheKey, $data, CACHE_TTL);
        }

        return $data;
    }

    private function callGoogleBooksAPI(string $isbn): ?array {
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

        if (!isset($data['items'][0]['volumeInfo'])) {
            return null;
        }

        $book = $data['items'][0]['volumeInfo'];

        return [
            'title' => $book['title'] ?? null,
            'authors' => isset($book['authors']) ? implode(', ', $book['authors']) : null,
            'description' => $book['description'] ?? null,
            'thumbnail' => $book['imageLinks']['thumbnail'] ?? null,
            'published_date' => $book['publishedDate'] ?? null,
            'page_count' => $book['pageCount'] ?? null,
        ];
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

        // Merge
        return array_merge($book, $metadata ?? []);
    }
}

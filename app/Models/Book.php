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

    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array {
        $offset = ($page - 1) * $perPage;

        // Build WHERE clause
        $where = ["deleted_at IS NULL"];
        $params = [];

        // Genre filter - support multiple values
        if (!empty($filters['genre'])) {
            if (is_array($filters['genre'])) {
                $placeholders = implode(',', array_fill(0, count($filters['genre']), '?'));
                $where[] = "genre IN ($placeholders)";
                foreach ($filters['genre'] as $genre) {
                    $params[] = $genre;
                }
            } else {
                $where[] = "genre = ?";
                $params[] = $filters['genre'];
            }
        }

        // Published year filter - support multiple values
        if (!empty($filters['year'])) {
            if (is_array($filters['year'])) {
                $placeholders = implode(',', array_fill(0, count($filters['year']), '?'));
                $where[] = "published_year IN ($placeholders)";
                foreach ($filters['year'] as $year) {
                    $params[] = (int)$year;
                }
            } else {
                $where[] = "published_year = ?";
                $params[] = (int)$filters['year'];
            }
        }

        // Language filter - support multiple values
        if (!empty($filters['language'])) {
            if (is_array($filters['language'])) {
                $placeholders = implode(',', array_fill(0, count($filters['language']), '?'));
                $where[] = "language IN ($placeholders)";
                foreach ($filters['language'] as $lang) {
                    $params[] = $lang;
                }
            } else {
                $where[] = "language = ?";
                $params[] = $filters['language'];
            }
        }

        // Build ORDER BY clause
        $orderBy = "id DESC"; // default - newest first
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'newest':
                    $orderBy = "id DESC";
                    break;
                case 'oldest':
                    $orderBy = "id ASC";
                    break;
                case 'title-asc':
                    $orderBy = "title ASC";
                    break;
                case 'title-desc':
                    $orderBy = "title DESC";
                    break;
                case 'author-asc':
                    $orderBy = "author ASC";
                    break;
                case 'author-desc':
                    $orderBy = "author DESC";
                    break;
                case 'year-asc':
                    $orderBy = "published_year ASC";
                    break;
                case 'year-desc':
                    $orderBy = "published_year DESC";
                    break;
            }
        }

        $whereClause = implode(" AND ", $where);
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll(
            "SELECT * FROM books
             WHERE {$whereClause}
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?",
            $params
        );
    }

    public function getTotalCount(array $filters = []): int {
        // Build WHERE clause (same as paginate)
        $where = ["deleted_at IS NULL"];
        $params = [];

        // Genre filter - support multiple values
        if (!empty($filters['genre'])) {
            if (is_array($filters['genre'])) {
                $placeholders = implode(',', array_fill(0, count($filters['genre']), '?'));
                $where[] = "genre IN ($placeholders)";
                foreach ($filters['genre'] as $genre) {
                    $params[] = $genre;
                }
            } else {
                $where[] = "genre = ?";
                $params[] = $filters['genre'];
            }
        }

        // Published year filter - support multiple values
        if (!empty($filters['year'])) {
            if (is_array($filters['year'])) {
                $placeholders = implode(',', array_fill(0, count($filters['year']), '?'));
                $where[] = "published_year IN ($placeholders)";
                foreach ($filters['year'] as $year) {
                    $params[] = (int)$year;
                }
            } else {
                $where[] = "published_year = ?";
                $params[] = (int)$filters['year'];
            }
        }

        // Language filter - support multiple values
        if (!empty($filters['language'])) {
            if (is_array($filters['language'])) {
                $placeholders = implode(',', array_fill(0, count($filters['language']), '?'));
                $where[] = "language IN ($placeholders)";
                foreach ($filters['language'] as $lang) {
                    $params[] = $lang;
                }
            } else {
                $where[] = "language = ?";
                $params[] = $filters['language'];
            }
        }

        $whereClause = implode(" AND ", $where);

        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM books WHERE {$whereClause}",
            $params
        );

        return $result['total'] ?? 0;
    }

    public function getGenres(): array {
        return $this->db->fetchAll(
            "SELECT genre, COUNT(*) as count
             FROM books
             WHERE deleted_at IS NULL
             GROUP BY genre
             ORDER BY count DESC"
        );
    }

    public function getPublishedYears(): array {
        return $this->db->fetchAll(
            "SELECT published_year as year, COUNT(*) as count
             FROM books
             WHERE deleted_at IS NULL AND published_year IS NOT NULL
             GROUP BY published_year
             ORDER BY published_year DESC"
        );
    }

    public function getLanguages(): array {
        return $this->db->fetchAll(
            "SELECT language, COUNT(*) as count
             FROM books
             WHERE deleted_at IS NULL AND language IS NOT NULL
             GROUP BY language
             ORDER BY count DESC"
        );
    }

    public function incrementViews(int $id): void {
        $this->db->query(
            "UPDATE books SET views_count = views_count + 1 WHERE id = ?",
            [$id]
        );
    }

    public function create(array $data): int {
        $slug = $this->generateSlug($data['title']);

        return $this->db->insert(
            "INSERT INTO books (title, author, isbn, slug, genre, language, description, published_year, total_copies, available_copies, thumbnail)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['title'],
                $data['author'],
                $data['isbn'],
                $slug,
                $data['genre'] ?? null,
                $data['language'] ?? 'cs',
                $data['description'] ?? null,
                $data['published_year'] ?? null,
                $data['total_copies'] ?? 1,
                $data['available_copies'] ?? 1,
                $data['thumbnail'] ?? null,
            ]
        );
    }

    public function update(int $id, array $data): bool {
        $slug = $this->generateSlug($data['title']);

        return $this->db->execute(
            "UPDATE books
             SET title = ?, author = ?, isbn = ?, slug = ?, genre = ?,
                 published_year = ?, total_copies = ?, available_copies = ?, thumbnail = ?
             WHERE id = ?",
            [
                $data['title'],
                $data['author'],
                $data['isbn'],
                $slug,
                $data['genre'] ?? null,
                $data['published_year'] ?? null,
                $data['total_copies'] ?? 1,
                $data['available_copies'] ?? 1,
                $data['thumbnail'] ?? null,
                $id
            ]
        );
    }

    public function delete(int $id): bool {
        return $this->db->execute(
            "UPDATE books SET deleted_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    public function updateStock(int $id, int $totalCopies, int $availableCopies): bool {
        return $this->db->execute(
            "UPDATE books SET total_copies = ?, available_copies = ? WHERE id = ?",
            [$totalCopies, $availableCopies, $id]
        );
    }

    public function findByIsbn(string $isbn): ?array {
        return $this->db->fetch(
            "SELECT * FROM books WHERE isbn = ? AND deleted_at IS NULL",
            [$isbn]
        );
    }

    public function existsByIsbn(string $isbn, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM books WHERE isbn = ? AND deleted_at IS NULL";
        $params = [$isbn];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetch($query, $params);
        return ($result['count'] ?? 0) > 0;
    }

    public function findDeletedByIsbn(string $isbn): ?array {
        return $this->db->fetch(
            "SELECT * FROM books WHERE isbn = ? AND deleted_at IS NOT NULL ORDER BY deleted_at DESC LIMIT 1",
            [$isbn]
        );
    }

    public function restore(int $id, array $data): bool {
        // Update the soft-deleted book with new data and clear deleted_at
        return $this->db->execute(
            "UPDATE books
             SET title = ?, author = ?, slug = ?, genre = ?, language = ?,
                 description = ?, published_year = ?, total_copies = ?, available_copies = ?,
                 thumbnail = ?, deleted_at = NULL, updated_at = NOW()
             WHERE id = ?",
            [
                $data['title'],
                $data['author'],
                $this->generateSlug($data['title']),
                $data['genre'] ?? null,
                $data['language'] ?? 'cs',
                $data['description'] ?? null,
                $data['published_year'] ?? null,
                $data['total_copies'] ?? 1,
                $data['available_copies'] ?? 1,
                $data['thumbnail'] ?? null,
                $id
            ]
        );
    }

    private function generateSlug(string $title): string {
        $slug = mb_strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
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

        // Use Google Books API only (has everything: high-res images + Czech descriptions)
        $googleData = $this->callGoogleBooksAPI($isbn);

        if ($googleData && !empty($googleData['thumbnail'])) {
            // Cache and return
            $this->cache->set($cacheKey, $googleData, CACHE_TTL);
            return $googleData;
        }

        return null;
    }

    private function callGoogleBooksAPI(string $isbn): ?array {
        // Request books from Czech market (don't restrict language - many translations not marked as 'cs')
        $url = GOOGLE_BOOKS_API . "?q=isbn:{$isbn}&country=CZ";
        $response = $this->httpGet($url);

        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);

        if (!isset($data['items']) || empty($data['items'])) {
            return null;
        }

        // Find first item with thumbnail, or fall back to first item
        $book = null;
        $foundWithThumbnail = false;

        foreach ($data['items'] as $index => $item) {
            if (isset($item['volumeInfo'])) {
                $hasThumbnail = isset($item['volumeInfo']['imageLinks']['thumbnail']);

                // Prefer items with thumbnail
                if ($hasThumbnail && !$foundWithThumbnail) {
                    $book = $item['volumeInfo'];
                    $foundWithThumbnail = true;
                    break;
                }

                // Fallback to first item if no thumbnail found yet
                if ($book === null) {
                    $book = $item['volumeInfo'];
                }
            }
        }

        if (!$book) {
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

            // Use high-quality images for better user experience
            if ($thumbnail) {
                // Remove existing zoom parameter and add zoom=0 for maximum quality
                $thumbnail = preg_replace('/[&?]zoom=\d+/', '', $thumbnail);
                $thumbnail .= (strpos($thumbnail, '?') !== false ? '&' : '?') . 'zoom=0';

                // Convert HTTP to HTTPS to avoid mixed content warnings in browsers
                $thumbnail = str_replace('http://', 'https://', $thumbnail);
            }
        }

        return [
            'title' => $book['title'] ?? null,
            'authors' => isset($book['authors']) ? implode(', ', $book['authors']) : null,
            'description' => $book['description'] ?? null,
            'thumbnail' => $thumbnail,
            'published_date' => $book['publishedDate'] ?? null,
            'page_count' => $book['pageCount'] ?? null,
            'source' => 'Google Books (zoom=0 - max quality)',
        ];
    }

    // ════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════

    /**
     * HTTP GET request helper - DRY principle
     */
    private function httpGet(string $url, int $timeout = 5): ?string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, // For XAMPP compatibility
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200 && $response) ? $response : null;
    }

    public function getComplete(string $slug): ?array {
        $book = $this->findBySlug($slug);

        if (!$book) {
            return null;
        }

        // If description is missing in DB, fetch from API as fallback
        if (empty($book['description'])) {
            $metadata = $this->fetchMetadata($book['isbn']);

            if ($metadata && !empty($metadata['description'])) {
                $book['description'] = $metadata['description'];
            }
        }

        return $book;
    }
}

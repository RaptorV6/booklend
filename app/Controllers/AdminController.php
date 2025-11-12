<?php
namespace app\Controllers;

use app\Database;
use app\Cache;
use app\Models\Book;
use app\Auth;

class AdminController {
    private Database $db;
    private Cache $cache;
    private Book $bookModel;

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
        $this->bookModel = new Book($db, $cache);
    }

    // ════════════════════════════════════════════════════════
    // ADMIN PAGES
    // ════════════════════════════════════════════════════════

    public function dashboard(): void {
        if (!Auth::isAdmin()) {
            redirect('/');
        }

        // Prevent caching of admin page
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $books = $this->bookModel->getAll(100);
        $title = 'Admin - Správa knih';
        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    // ════════════════════════════════════════════════════════
    // AJAX API
    // ════════════════════════════════════════════════════════

    public function apiCreate(): void {
        if (!Auth::isAdmin()) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }

        $data = jsonInput();

        if (empty($data['title']) || empty($data['author']) || empty($data['isbn'])) {
            jsonResponse(['error' => 'Název, autor a ISBN jsou povinné'], 400);
        }

        // Check for duplicate ISBN
        if ($this->bookModel->existsByIsbn($data['isbn'])) {
            jsonResponse(['error' => 'Kniha s tímto ISBN již existuje'], 409);
        }

        try {
            $id = $this->bookModel->create($data);
            jsonResponse(['success' => true, 'message' => 'Kniha přidána', 'id' => $id]);
        } catch (\Exception $e) {
            error_log("Admin create error: " . $e->getMessage());
            jsonResponse(['error' => 'Chyba při vytváření: ' . $e->getMessage()], 500);
        }
    }

    public function apiUpdate(): void {
        if (!Auth::isAdmin()) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }

        $data = jsonInput();
        $bookId = $data['id'] ?? null;

        if (!$bookId) {
            jsonResponse(['error' => 'Book ID required'], 400);
        }

        if (empty($data['title']) || empty($data['author']) || empty($data['isbn'])) {
            jsonResponse(['error' => 'Název, autor a ISBN jsou povinné'], 400);
        }

        try {
            $this->bookModel->update($bookId, $data);
            jsonResponse(['success' => true, 'message' => 'Kniha aktualizována']);
        } catch (\Exception $e) {
            error_log("Admin update error: " . $e->getMessage());
            jsonResponse(['error' => 'Chyba při aktualizaci: ' . $e->getMessage()], 500);
        }
    }

    public function apiDelete(): void {
        if (!Auth::isAdmin()) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }

        $data = jsonInput();

        $bookId = $data['id'] ?? null;

        if (!$bookId) {
            jsonResponse(['error' => 'Book ID required'], 400);
        }

        try {
            $result = $this->bookModel->delete($bookId);
            jsonResponse(['success' => true, 'message' => 'Kniha smazána']);
        } catch (\Exception $e) {
            error_log("Admin delete error: " . $e->getMessage());
            jsonResponse(['error' => 'Chyba při mazání: ' . $e->getMessage()], 500);
        }
    }

    public function apiGetBook(): void {
        if (!Auth::isAdmin()) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }

        $bookId = $_GET['id'] ?? null;

        if (!$bookId) {
            jsonResponse(['error' => 'Book ID required'], 400);
        }

        $book = $this->bookModel->findById($bookId);

        if (!$book) {
            jsonResponse(['error' => 'Kniha nenalezena'], 404);
        }

        jsonResponse(['success' => true, 'book' => $book]);
    }

    public function apiGetBooks(): void {
        if (!Auth::isAdmin()) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }

        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);

        // Build filters from query parameters
        $filters = [];

        if (!empty($_GET['sort'])) {
            // SECURITY: Whitelist allowed sort values (column-direction format)
            $allowedSorts = [
                'id-asc', 'id-desc',
                'title-asc', 'title-desc',
                'author-asc', 'author-desc',
                'year-asc', 'year-desc'
            ];
            if (in_array($_GET['sort'], $allowedSorts, true)) {
                $filters['sort'] = $_GET['sort'];
            } else {
                // Default to id-desc if invalid sort provided
                $filters['sort'] = 'id-desc';
            }
        } else {
            // Default sort: newest books first (by id descending)
            $filters['sort'] = 'id-desc';
        }

        $books = $this->bookModel->paginate($page, $limit, $filters);
        $total = $this->bookModel->getTotalCount($filters);

        jsonResponse([
            'success' => true,
            'books' => $books,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'hasMore' => ($page * $limit) < $total,
            'filters' => $filters
        ]);
    }

    public function apiSearchBooks(): void {
        if (!Auth::isAdmin()) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }

        $query = $_GET['q'] ?? '';

        error_log("Admin search books: query = '$query'");

        if (strlen($query) < 2) {
            jsonResponse(['items' => []]);
            return;
        }

        // Search in Google Books API using cURL (more reliable than file_get_contents)
        $url = GOOGLE_BOOKS_API . "?q=" . urlencode($query) . "&maxResults=20&key=" . GOOGLE_BOOKS_API_KEY;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, // For XAMPP compatibility
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!$response || $httpCode !== 200) {
            error_log("Google Books API failed: HTTP $httpCode, Error: $error");
            error_log("Response body: " . substr($response, 0, 1000));

            // Try to decode error message from Google
            $errorData = json_decode($response, true);
            $errorMsg = "API Error: HTTP $httpCode";
            if (isset($errorData['error']['message'])) {
                $errorMsg .= " - " . $errorData['error']['message'];
                error_log("Google API Error: " . $errorData['error']['message']);
            }

            jsonResponse(['items' => [], 'debug' => $errorMsg]);
            return;
        }

        $data = json_decode($response, true);

        if (!isset($data['items'])) {
            jsonResponse(['items' => []]);
            return;
        }

        // Format results
        $items = [];
        foreach ($data['items'] as $item) {
            $volumeInfo = $item['volumeInfo'] ?? [];

            // Extract ISBN (prefer ISBN-13, fallback to ISBN-10)
            $isbn = null;
            if (isset($volumeInfo['industryIdentifiers'])) {
                foreach ($volumeInfo['industryIdentifiers'] as $identifier) {
                    if ($identifier['type'] === 'ISBN_13') {
                        $isbn = $identifier['identifier'];
                        break;
                    }
                }
                // Fallback to ISBN-10 if no ISBN-13
                if (!$isbn) {
                    foreach ($volumeInfo['industryIdentifiers'] as $identifier) {
                        if ($identifier['type'] === 'ISBN_10') {
                            $isbn = $identifier['identifier'];
                            break;
                        }
                    }
                }
            }

            // Skip only if NO ISBN at all
            if (!$isbn) {
                continue;
            }

            // Get thumbnail (allow null if missing)
            $thumbnail = null;
            if (!empty($volumeInfo['imageLinks']['thumbnail'])) {
                $thumbnail = str_replace('http://', 'https://', $volumeInfo['imageLinks']['thumbnail']);
                $thumbnail = preg_replace('/[&?]zoom=\d+/', '', $thumbnail);
                $thumbnail .= (strpos($thumbnail, '?') !== false ? '&' : '?') . 'zoom=0';
            }

            $items[] = [
                'title' => $volumeInfo['title'] ?? '',
                'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '',
                'isbn' => $isbn,
                'genre' => isset($volumeInfo['categories'][0]) ? $volumeInfo['categories'][0] : null,
                'published_year' => isset($volumeInfo['publishedDate']) ? (int)substr($volumeInfo['publishedDate'], 0, 4) : null,
                'thumbnail' => $thumbnail,
                'description' => $volumeInfo['description'] ?? null
            ];
        }

        jsonResponse(['items' => $items]);
    }

    public function apiCheckIsbn(): void {
        if (!Auth::isAdmin()) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }

        $isbn = $_GET['isbn'] ?? '';
        $excludeId = $_GET['exclude'] ?? null;

        if (empty($isbn)) {
            jsonResponse(['exists' => false]);
            return;
        }

        $exists = $this->bookModel->existsByIsbn($isbn, $excludeId);
        jsonResponse(['exists' => $exists]);
    }

    public function apiUpdateStock(): void {
        if (!Auth::isAdmin()) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }

        $data = jsonInput();

        $bookId = $data['id'] ?? null;
        $totalCopies = $data['total_copies'] ?? null;
        $availableCopies = $data['available_copies'] ?? null;

        if (!$bookId || $totalCopies === null || $availableCopies === null) {
            jsonResponse(['error' => 'ID, total_copies a available_copies jsou povinné'], 400);
        }

        if ($availableCopies > $totalCopies) {
            jsonResponse(['error' => 'Dostupných kopií nemůže být více než celkem'], 400);
        }

        try {
            $result = $this->bookModel->updateStock($bookId, $totalCopies, $availableCopies);
            jsonResponse(['success' => true, 'message' => 'Skladové stavy aktualizovány']);
        } catch (\Exception $e) {
            error_log("Admin update stock error: " . $e->getMessage());
            jsonResponse(['error' => 'Chyba při aktualizaci: ' . $e->getMessage()], 500);
        }
    }
}

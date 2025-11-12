<?php
namespace app\Controllers;

use app\Database;
use app\Cache;
use app\Models\Book;
use app\Models\Rental;
use app\Auth;

class BookController {
    private Database $db;
    private Cache $cache;
    private Book $bookModel;
    private Rental $rentalModel;

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
        $this->bookModel = new Book($db, $cache);
        $this->rentalModel = new Rental($db);
    }

    // ════════════════════════════════════════════════════════
    // PUBLIC PAGES
    // ════════════════════════════════════════════════════════

    public function catalog(): void {
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;

        // Build filters array from query parameters (using Czech parameter names)
        // Support multiple values: ?zanr=Fantasy-Horor (hyphen-separated, RFC 3986 unreserved = no encoding)
        // SECURITY: All values are validated and sanitized
        $filters = [];

        if (!empty($_GET['zanr'])) {
            // Handle both array format (?zanr[]=...) and hyphen-separated (?zanr=Fantasy-Horor)
            $genres = is_array($_GET['zanr'])
                ? $_GET['zanr']
                : array_map('trim', explode('-', $_GET['zanr']));

            // SECURITY: Filter out empty values and limit length
            $genres = array_filter($genres, function($g) {
                return !empty($g) && strlen($g) <= 50;
            });

            if (!empty($genres)) {
                $filters['genre'] = array_values($genres); // re-index array
            }
        }

        if (!empty($_GET['rok'])) {
            // Handle both array format (?rok[]=...) and hyphen-separated (?rok=2020-2021)
            $years = is_array($_GET['rok'])
                ? array_map('intval', $_GET['rok'])
                : array_map('intval', array_map('trim', explode('-', $_GET['rok'])));

            // SECURITY: Filter out invalid years (must be 1800-2100)
            $years = array_filter($years, function($y) {
                return $y >= 1800 && $y <= 2100;
            });

            if (!empty($years)) {
                $filters['year'] = array_values($years); // re-index array
            }
        }

        if (!empty($_GET['sort'])) {
            // SECURITY: Whitelist allowed sort values
            $allowedSorts = ['newest', 'oldest', 'title-asc', 'title-desc', 'author-asc', 'author-desc', 'year-asc', 'year-desc'];
            if (in_array($_GET['sort'], $allowedSorts, true)) {
                $filters['sort'] = $_GET['sort'];
            }
        }

        // Get filter options
        $genres = $this->bookModel->getGenres();
        $years = $this->bookModel->getPublishedYears();

        // Get books for current page
        $books = $this->bookModel->paginate($page, $perPage, $filters);
        $totalBooks = $this->bookModel->getTotalCount($filters);
        $totalPages = (int)ceil($totalBooks / $perPage);

        // Pagination data
        $pagination = [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'totalBooks' => $totalBooks,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
        ];

        // Current filters for frontend
        $currentFilters = $filters;

        // SEO: Dynamic title and description based on filters
        // BEST PRACTICE: Only customize for single filters (indexable pages)
        // Combinations/multiple values keep default (canonical points elsewhere)
        $title = 'Katalog knih';
        $description = 'Objevte náš online katalog knih. Moderní půjčovna knih s širokou nabídkou titulů pro každého čtenáře.';

        $hasGenre = !empty($filters['genre']);
        $hasYear = !empty($filters['year']);
        $singleGenre = $hasGenre && count($filters['genre']) === 1;
        $singleYear = $hasYear && count($filters['year']) === 1;

        // Only customize meta for single filter pages (these will be indexed)
        if ($singleGenre && !$hasYear) {
            // Single genre only → indexable
            $genre = ucfirst($filters['genre'][0]);
            $title = $genre . ' knihy - Online půjčovna | BookLend';
            $description = 'Objevte nejlepší ' . strtolower($genre) . ' knihy v naší online půjčovně. Široký výběr kvalitních titulů žánru ' . $genre . '.';
        } elseif ($singleYear && !$hasGenre) {
            // Single year only → indexable
            $year = $filters['year'][0];
            $title = 'Knihy z roku ' . $year . ' - BookLend';
            $description = 'Prohlédněte si knihy vydané v roce ' . $year . '. Aktuální novinky i klasické tituly v naší online půjčovně.';
        }
        // else: keep default for combinations/multiple values (canonical to homepage or first value)

        require __DIR__ . '/../Views/books/catalog.php';
    }

    public function detail(string $slug): void {
        // Find book
        $book = $this->bookModel->getComplete($slug);

        if (!$book) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        // Increment views
        $this->bookModel->incrementViews($book['id']);

        // Check if user has rented this book
        $isRented = false;
        if (Auth::check()) {
            $isRented = $this->rentalModel->isBookRentedByUser(Auth::id(), $book['id']);
        }

        $title = $book['title'];
        require __DIR__ . '/../Views/books/detail.php';
    }

    public function search(): void {
        $query = $_GET['q'] ?? '';

        if (strlen($query) < 2) {
            jsonResponse(['items' => []]);
        }

        $books = $this->bookModel->search($query);

        jsonResponse(['items' => $books]);
    }

    public function apiGetBooks(): void {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;

        // Validate
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 50) $limit = 12;

        // Build filters from query parameters (using Czech parameter names)
        // Support multiple values: ?zanr=Fantasy-Horor (hyphen-separated, RFC 3986 unreserved = no encoding)
        // SECURITY: All values are validated and sanitized
        $filters = [];

        if (!empty($_GET['zanr'])) {
            // Handle both array format (?zanr[]=...) and hyphen-separated (?zanr=Fantasy-Horor)
            $genres = is_array($_GET['zanr'])
                ? $_GET['zanr']
                : array_map('trim', explode('-', $_GET['zanr']));

            // SECURITY: Filter out empty values and limit length
            $genres = array_filter($genres, function($g) {
                return !empty($g) && strlen($g) <= 50;
            });

            if (!empty($genres)) {
                $filters['genre'] = array_values($genres); // re-index array
            }
        }

        if (!empty($_GET['rok'])) {
            // Handle both array format (?rok[]=...) and hyphen-separated (?rok=2020-2021)
            $years = is_array($_GET['rok'])
                ? array_map('intval', $_GET['rok'])
                : array_map('intval', array_map('trim', explode('-', $_GET['rok'])));

            // SECURITY: Filter out invalid years (must be 1800-2100)
            $years = array_filter($years, function($y) {
                return $y >= 1800 && $y <= 2100;
            });

            if (!empty($years)) {
                $filters['year'] = array_values($years); // re-index array
            }
        }

        if (!empty($_GET['sort'])) {
            // SECURITY: Whitelist allowed sort values
            $allowedSorts = ['newest', 'oldest', 'title-asc', 'title-desc', 'author-asc', 'author-desc', 'year-asc', 'year-desc'];
            if (in_array($_GET['sort'], $allowedSorts, true)) {
                $filters['sort'] = $_GET['sort'];
            }
        }

        $books = $this->bookModel->paginate($page, $limit, $filters);
        $total = $this->bookModel->getTotalCount($filters);
        $hasMore = ($page * $limit) < $total;

        jsonResponse([
            'success' => true,
            'books' => $books,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'hasMore' => $hasMore,
            'filters' => $filters
        ]);
    }

    // ════════════════════════════════════════════════════════
    // AJAX API
    // ════════════════════════════════════════════════════════

    public function apiRent(): void {
        $data = jsonInput();
        $bookId = $data['book_id'] ?? null;

        if (!$bookId) {
            jsonResponse(['error' => 'Book ID required'], 400);
        }

        $book = $this->bookModel->findById($bookId);

        if (!$book) {
            jsonResponse(['error' => 'Kniha nenalezena'], 404);
        }

        if ($book['available_copies'] < 1) {
            jsonResponse(['error' => 'Kniha není dostupná'], 409);
        }

        // Check if already rented
        if ($this->rentalModel->isBookRentedByUser(Auth::id(), $bookId)) {
            jsonResponse(['error' => 'Už jste si tuto knihu půjčili'], 409);
        }

        try {
            $this->rentalModel->create(Auth::id(), $bookId, 14);
            jsonResponse(['success' => true, 'message' => 'Kniha půjčena']);
        } catch (\Exception $e) {
            error_log("Rent error: " . $e->getMessage());
            jsonResponse(['error' => 'Chyba při půjčování: ' . $e->getMessage()], 500);
        }
    }

    public function apiReturn(): void {
        $data = jsonInput();
        $rentalId = $data['rental_id'] ?? null;

        if (!$rentalId) {
            jsonResponse(['error' => 'Rental ID required'], 400);
        }

        $rental = $this->rentalModel->findById($rentalId);

        if (!$rental || $rental['user_id'] != Auth::id()) {
            jsonResponse(['error' => 'Výpůjčka nenalezena'], 404);
        }

        if ($rental['returned_at']) {
            jsonResponse(['error' => 'Kniha už byla vrácena'], 409);
        }

        try {
            $this->rentalModel->returnBook($rentalId);
            jsonResponse(['success' => true, 'message' => 'Kniha vrácena']);
        } catch (\Exception $e) {
            error_log("Return error: " . $e->getMessage());
            jsonResponse(['error' => 'Chyba při vracení: ' . $e->getMessage()], 500);
        }
    }
}

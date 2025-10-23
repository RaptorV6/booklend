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

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
        $this->bookModel = new Book($db, $cache);
    }

    // ════════════════════════════════════════════════════════
    // PUBLIC PAGES
    // ════════════════════════════════════════════════════════

    public function catalog(): void {
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;

        // Get books for current page
        $books = $this->bookModel->paginate($page, $perPage);
        $totalBooks = $this->bookModel->getTotalCount();
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

        $title = 'Katalog knih';
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
            $rentalModel = new Rental($this->db);
            $isRented = $rentalModel->isBookRentedByUser(Auth::id(), $book['id']);
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

        $books = $this->bookModel->paginate($page, $limit);
        $total = $this->bookModel->getTotalCount();
        $hasMore = ($page * $limit) < $total;

        jsonResponse([
            'books' => $books,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'hasMore' => $hasMore
        ]);
    }

    // ════════════════════════════════════════════════════════
    // AJAX API
    // ════════════════════════════════════════════════════════

    public function apiRent(): void {
        $data = json_decode(file_get_contents('php://input'), true);
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

        $rentalModel = new Rental($this->db);

        // Check if already rented
        if ($rentalModel->isBookRentedByUser(Auth::id(), $bookId)) {
            jsonResponse(['error' => 'Už jste si tuto knihu půjčili'], 409);
        }

        try {
            $rentalModel->create(Auth::id(), $bookId, 14);
            jsonResponse(['success' => true, 'message' => 'Kniha půjčena']);
        } catch (\Exception $e) {
            error_log("Rent error: " . $e->getMessage());
            jsonResponse(['error' => 'Chyba při půjčování: ' . $e->getMessage()], 500);
        }
    }

    public function apiReturn(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $rentalId = $data['rental_id'] ?? null;

        if (!$rentalId) {
            jsonResponse(['error' => 'Rental ID required'], 400);
        }

        $rentalModel = new Rental($this->db);
        $rental = $rentalModel->findById($rentalId);

        if (!$rental || $rental['user_id'] != Auth::id()) {
            jsonResponse(['error' => 'Výpůjčka nenalezena'], 404);
        }

        if ($rental['returned_at']) {
            jsonResponse(['error' => 'Kniha už byla vrácena'], 409);
        }

        try {
            $rentalModel->returnBook($rentalId);
            jsonResponse(['success' => true, 'message' => 'Kniha vrácena']);
        } catch (\Exception $e) {
            error_log("Return error: " . $e->getMessage());
            jsonResponse(['error' => 'Chyba při vracení: ' . $e->getMessage()], 500);
        }
    }
}

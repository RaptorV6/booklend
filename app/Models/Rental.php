<?php
namespace app\Models;

use app\Database;

class Rental {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getUserRentals(int $userId): array {
        return $this->db->fetchAll(
            "SELECT r.*, b.title, b.author, b.slug, b.thumbnail,
             CASE WHEN r.returned_at IS NULL AND r.due_at < NOW() THEN 1 ELSE 0 END as is_overdue
             FROM rentals r
             JOIN books b ON r.book_id = b.id
             WHERE r.user_id = ?
             ORDER BY r.rented_at DESC",
            [$userId]
        );
    }

    public function getActiveRentals(int $userId): array {
        return $this->db->fetchAll(
            "SELECT r.*, b.title, b.author, b.slug
             FROM rentals r
             JOIN books b ON r.book_id = b.id
             WHERE r.user_id = ? AND r.is_active = 1
             ORDER BY r.due_at ASC",
            [$userId]
        );
    }

    public function isBookRentedByUser(int $userId, int $bookId): bool {
        $result = $this->db->fetch(
            "SELECT id FROM rentals
             WHERE user_id = ? AND book_id = ? AND is_active = 1",
            [$userId, $bookId]
        );

        return $result !== null;
    }

    public function create(int $userId, int $bookId, int $days = 14): int {
        $dueDate = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        $this->db->query(
            "INSERT INTO rentals (user_id, book_id, due_at) VALUES (?, ?, ?)",
            [$userId, $bookId, $dueDate]
        );

        return $this->db->lastInsertId();
    }

    public function returnBook(int $rentalId): void {
        $this->db->query(
            "UPDATE rentals SET returned_at = NOW() WHERE id = ?",
            [$rentalId]
        );
    }

    public function findById(int $id): ?array {
        return $this->db->fetch(
            "SELECT * FROM rentals WHERE id = ?",
            [$id]
        );
    }
}

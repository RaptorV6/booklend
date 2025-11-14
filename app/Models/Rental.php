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

    public function create(int $userId, int $bookId, int $days = 30): int {
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

    /**
     * Extend rental by 15 days (unlimited extensions allowed, but paid)
     * @param int $rentalId
     * @return bool Success
     */
    public function extendRental(int $rentalId): bool {
        $rental = $this->findById($rentalId);

        if (!$rental) {
            return false; // Rental not found
        }

        if ($rental['returned_at'] !== null) {
            return false; // Already returned
        }

        // Save original due date on first extension
        $originalDue = $rental['original_due_at'] ?? $rental['due_at'];

        // New due date = current due_at + 15 days
        $newDueAt = date('Y-m-d H:i:s', strtotime($rental['due_at'] . ' +15 days'));

        $this->db->query(
            "UPDATE rentals
             SET due_at = ?,
                 original_due_at = ?,
                 extension_count = extension_count + 1,
                 extended_at = NOW()
             WHERE id = ?",
            [$newDueAt, $originalDue, $rentalId]
        );

        return true;
    }

    /**
     * Calculate fine for overdue rental
     * 100,000 CZK per week (7 days)
     * @param array $rental
     * @return float Fine amount in CZK
     */
    public function calculateFine(array $rental): float {
        // If returned, return the stored fine amount
        if ($rental['returned_at'] !== null) {
            return (float)$rental['fine_amount'];
        }

        $dueAt = new \DateTime($rental['due_at']);
        $now = new \DateTime();

        // Not overdue yet
        if ($now <= $dueAt) {
            return 0.00;
        }

        // Calculate days overdue
        $diff = $now->diff($dueAt);
        $daysOverdue = $diff->days;

        // Calculate weeks overdue (rounded up)
        $weeksOverdue = ceil($daysOverdue / 7);

        // 100,000 CZK per week
        return $weeksOverdue * 100000.00;
    }

    /**
     * Mark fine as paid
     * @param int $rentalId
     * @param float $amount
     * @return void
     */
    public function payFine(int $rentalId, float $amount): void {
        $this->db->query(
            "UPDATE rentals
             SET fine_amount = ?, fine_paid = 1
             WHERE id = ?",
            [$amount, $rentalId]
        );
    }
}

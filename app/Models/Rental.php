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

    /**
     * Create a new rental with atomic inventory management
     * Replaces MySQL trigger logic with PHP transaction
     *
     * @param int $userId User ID
     * @param int $bookId Book ID
     * @param int $days Rental duration (default 30)
     * @return int Rental ID
     * @throws \Exception If no available copies or database error
     */
    public function create(int $userId, int $bookId, int $days = 30): int {
        $dueDate = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        try {
            // Start transaction for atomic operation
            $this->db->beginTransaction();

            // Lock book row and check available copies
            // FOR UPDATE prevents race conditions between concurrent requests
            $book = $this->db->fetch(
                "SELECT id, available_copies FROM books WHERE id = ? FOR UPDATE",
                [$bookId]
            );

            if (!$book) {
                throw new \Exception("Kniha nenalezena");
            }

            if ($book['available_copies'] < 1) {
                throw new \Exception("No available copies");
            }

            // Decrease available copies
            $this->db->execute(
                "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?",
                [$bookId]
            );

            // Create rental record
            $this->db->query(
                "INSERT INTO rentals (user_id, book_id, due_at) VALUES (?, ?, ?)",
                [$userId, $bookId, $dueDate]
            );

            $rentalId = $this->db->lastInsertId();

            // Commit transaction
            $this->db->commit();

            return $rentalId;

        } catch (\Exception $e) {
            // Rollback on any error
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    /**
     * Return a book with atomic inventory management
     * Replaces MySQL trigger logic with PHP transaction
     *
     * @param int $rentalId Rental ID
     * @return void
     * @throws \Exception If rental not found or database error
     */
    public function returnBook(int $rentalId): void {
        try {
            // Start transaction for atomic operation
            $this->db->beginTransaction();

            // Get rental details
            $rental = $this->db->fetch(
                "SELECT id, book_id, returned_at FROM rentals WHERE id = ?",
                [$rentalId]
            );

            if (!$rental) {
                throw new \Exception("Výpůjčka nenalezena");
            }

            if ($rental['returned_at'] !== null) {
                throw new \Exception("Kniha už byla vrácena");
            }

            // Mark rental as returned
            $this->db->execute(
                "UPDATE rentals SET returned_at = NOW() WHERE id = ?",
                [$rentalId]
            );

            // Increase available copies
            $this->db->execute(
                "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?",
                [$rental['book_id']]
            );

            // Commit transaction
            $this->db->commit();

        } catch (\Exception $e) {
            // Rollback on any error
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
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

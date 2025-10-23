<?php
namespace app\Models;

use app\Database;

class User {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function findById(int $id): ?array {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function findByEmail(string $email): ?array {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );
    }

    public function findByUsername(string $username): ?array {
        return $this->db->fetch(
            "SELECT * FROM users WHERE username = ? AND deleted_at IS NULL",
            [$username]
        );
    }

    public function findByEmailOrUsername(string $login): ?array {
        return $this->db->fetch(
            "SELECT * FROM users
             WHERE (email = ? OR username = ?)
             AND deleted_at IS NULL",
            [$login, $login]
        );
    }

    public function create(array $data): int {
        $this->db->query(
            "INSERT INTO users (username, email, password_hash, role)
             VALUES (?, ?, ?, ?)",
            [
                $data['username'],
                $data['email'],
                $data['password_hash'],
                $data['role'] ?? 'user'
            ]
        );

        return $this->db->lastInsertId();
    }

    public function updateLastLogin(int $id): void {
        $this->db->query(
            "UPDATE users SET last_login_at = NOW() WHERE id = ?",
            [$id]
        );
    }
}

<?php
namespace app;

class Auth {
    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? 'user',
        ];
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function login(array $user): void {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        session_regenerate_id(true);
    }

    public static function logout(): void {
        session_unset();
        session_destroy();
        session_start();
    }

    public static function isAdmin(): bool {
        return self::check() && ($_SESSION['role'] ?? null) === 'admin';
    }
}

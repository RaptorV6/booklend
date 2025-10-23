<?php
namespace app\Controllers;

use app\Database;
use app\Cache;
use app\Models\User;
use app\Auth;

class AuthController {
    private Database $db;
    private Cache $cache;

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    // ════════════════════════════════════════════════════════
    // LOGIN
    // ════════════════════════════════════════════════════════

    public function showLogin(): void {
        if (Auth::check()) {
            redirect('/profil');
        }

        $title = 'Přihlášení';
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void {
        $validated = validate($_POST, [
            'login' => 'required',
            'password' => 'required|min:6'
        ]);

        if (empty($validated)) {
            redirect('/login');
        }

        $userModel = new User($this->db);
        $user = $userModel->findByEmailOrUsername($validated['login']);

        if (!$user || !password_verify($validated['password'], $user['password_hash'])) {
            $_SESSION['errors'] = ['login' => ['Neplatné přihlašovací údaje']];
            $_SESSION['old'] = $_POST;
            redirect('/login');
        }

        if (!$user['is_active']) {
            $_SESSION['errors'] = ['login' => ['Účet je deaktivován']];
            redirect('/login');
        }

        // Update last login
        $userModel->updateLastLogin($user['id']);

        // Login
        Auth::login($user);

        redirect('/profil');
    }

    // ════════════════════════════════════════════════════════
    // REGISTER
    // ════════════════════════════════════════════════════════

    public function showRegister(): void {
        if (Auth::check()) {
            redirect('/profil');
        }

        $title = 'Registrace';
        require __DIR__ . '/../Views/auth/register.php';
    }

    public function register(): void {
        $validated = validate($_POST, [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirm' => 'required'
        ]);

        if (empty($validated)) {
            redirect('/register');
        }

        // Check password match
        if ($validated['password'] !== $validated['password_confirm']) {
            $_SESSION['errors'] = ['password_confirm' => ['Hesla se neshodují']];
            $_SESSION['old'] = $_POST;
            redirect('/register');
        }

        $userModel = new User($this->db);

        // Check if email exists
        if ($userModel->findByEmail($validated['email'])) {
            $_SESSION['errors'] = ['email' => ['Email je již registrován']];
            $_SESSION['old'] = $_POST;
            redirect('/register');
        }

        // Check if username exists
        if ($userModel->findByUsername($validated['username'])) {
            $_SESSION['errors'] = ['username' => ['Uživatelské jméno je obsazeno']];
            $_SESSION['old'] = $_POST;
            redirect('/register');
        }

        // Create user
        $userId = $userModel->create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password_hash' => password_hash($validated['password'], PASSWORD_DEFAULT),
            'role' => 'user'
        ]);

        $_SESSION['success'] = 'Registrace proběhla úspěšně! Můžete se přihlásit.';
        redirect('/login');
    }

    // ════════════════════════════════════════════════════════
    // LOGOUT
    // ════════════════════════════════════════════════════════

    public function logout(): void {
        Auth::logout();
        redirect('/');
    }
}

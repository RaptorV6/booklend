<?php
namespace app\Controllers;

use app\Database;
use app\Cache;
use app\Models\User;
use app\Auth;

class AuthController {
    private Database $db;
    private Cache $cache;
    private User $userModel;

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
        $this->userModel = new User($db);
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

        // Check if it's an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (empty($validated)) {
            if ($isAjax) {
                jsonResponse(['success' => false, 'errors' => $_SESSION['errors'] ?? []], 400);
            }
            redirect('/login');
        }

        $user = $this->userModel->findByEmailOrUsername($validated['login']);

        if (!$user || !password_verify($validated['password'], $user['password_hash'])) {
            if ($isAjax) {
                jsonResponse(['success' => false, 'error' => 'Neplatné přihlašovací údaje'], 401);
            }
            $_SESSION['errors'] = ['login' => ['Neplatné přihlašovací údaje']];
            $_SESSION['old'] = $_POST;
            redirect('/login');
        }

        if (!$user['is_active']) {
            if ($isAjax) {
                jsonResponse(['success' => false, 'error' => 'Účet je deaktivován'], 403);
            }
            $_SESSION['errors'] = ['login' => ['Účet je deaktivován']];
            redirect('/login');
        }

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Login
        Auth::login($user);

        if ($isAjax) {
            jsonResponse(['success' => true, 'redirect' => BASE_URL . '/profil']);
        }

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

        // Check if it's an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (empty($validated)) {
            if ($isAjax) {
                jsonResponse(['success' => false, 'errors' => $_SESSION['errors'] ?? []], 400);
            }
            redirect('/register');
        }

        // Check password match
        if ($validated['password'] !== $validated['password_confirm']) {
            if ($isAjax) {
                jsonResponse(['success' => false, 'errors' => ['password_confirm' => 'Hesla se neshodují']], 400);
            }
            $_SESSION['errors'] = ['password_confirm' => ['Hesla se neshodují']];
            $_SESSION['old'] = $_POST;
            redirect('/register');
        }

        // Check if email exists
        if ($this->userModel->findByEmail($validated['email'])) {
            if ($isAjax) {
                jsonResponse(['success' => false, 'errors' => ['email' => 'Email je již registrován']], 400);
            }
            $_SESSION['errors'] = ['email' => ['Email je již registrován']];
            $_SESSION['old'] = $_POST;
            redirect('/register');
        }

        // Check if username exists
        if ($this->userModel->findByUsername($validated['username'])) {
            if ($isAjax) {
                jsonResponse(['success' => false, 'errors' => ['username' => 'Uživatelské jméno je obsazeno']], 400);
            }
            $_SESSION['errors'] = ['username' => ['Uživatelské jméno je obsazeno']];
            $_SESSION['old'] = $_POST;
            redirect('/register');
        }

        // Create user
        $userId = $this->userModel->create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password_hash' => password_hash($validated['password'], PASSWORD_DEFAULT),
            'role' => 'user'
        ]);

        if ($isAjax) {
            jsonResponse(['success' => true, 'message' => 'Registrace proběhla úspěšně! Můžete se přihlásit.']);
        }

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

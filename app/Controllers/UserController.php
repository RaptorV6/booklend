<?php
namespace app\Controllers;

use app\Database;
use app\Cache;
use app\Models\User;
use app\Models\Rental;
use app\Auth;

class UserController {
    private Database $db;
    private Cache $cache;

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    public function profile(): void {
        $userModel = new User($this->db);
        $user = $userModel->findById(Auth::id());

        if (!$user) {
            redirect('/login');
        }

        $title = 'Můj profil';
        require __DIR__ . '/../Views/user/profile.php';
    }

    public function loans(): void {
        $rentalModel = new Rental($this->db);
        $rentals = $rentalModel->getUserRentals(Auth::id());

        $title = 'Moje výpůjčky';
        require __DIR__ . '/../Views/user/loans.php';
    }
}

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
    private User $userModel;
    private Rental $rentalModel;

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
        $this->userModel = new User($db);
        $this->rentalModel = new Rental($db);
    }

    public function profile(): void {
        $user = $this->userModel->findById(Auth::id());

        if (!$user) {
            redirect('/login');
        }

        $title = 'Můj profil';
        require __DIR__ . '/../Views/user/profile.php';
    }

    public function loans(): void {
        $rentals = $this->rentalModel->getUserRentals(Auth::id());

        $title = 'Moje výpůjčky';
        require __DIR__ . '/../Views/user/loans.php';
    }
}

<?php

// ═══════════════════════════════════════════════════════════
// ROUTES
// ═══════════════════════════════════════════════════════════

// Public routes
$router->get('/', 'BookController', 'catalog');
$router->get('/kniha/{slug}', 'BookController', 'detail');
$router->get('/api/search', 'BookController', 'search');

// Auth routes
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'showRegister');
$router->post('/register', 'AuthController', 'register');
$router->post('/logout', 'AuthController', 'logout');

// User routes (protected)
$router->get('/profil', 'UserController', 'profile', 'auth');
$router->get('/moje-vypujcky', 'UserController', 'loans', 'auth');

// AJAX routes (protected)
$router->post('/api/rent', 'BookController', 'apiRent', 'auth');
$router->post('/api/return', 'BookController', 'apiReturn', 'auth');

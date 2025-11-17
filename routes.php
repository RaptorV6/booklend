<?php

// Public routes
$router->get('/', 'BookController', 'catalog');
$router->get('/kniha/{slug}', 'BookController', 'detail');
$router->get('/api/search', 'BookController', 'search');
$router->get('/api/books', 'BookController', 'apiGetBooks');

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
$router->post('/api/extend', 'BookController', 'apiExtend', 'auth');

// Admin routes (protected - admin only)
$router->get('/admin', 'AdminController', 'dashboard', 'admin');
$router->get('/api/admin/book', 'AdminController', 'apiGetBook', 'admin');
$router->get('/api/admin/books', 'AdminController', 'apiGetBooks', 'admin');
$router->get('/api/admin/search-books', 'AdminController', 'apiSearchBooks', 'admin');
$router->get('/api/admin/check-isbn', 'AdminController', 'apiCheckIsbn', 'admin');
$router->post('/api/admin/create', 'AdminController', 'apiCreate', 'admin');
$router->post('/api/admin/update', 'AdminController', 'apiUpdate', 'admin');
$router->post('/api/admin/update-stock', 'AdminController', 'apiUpdateStock', 'admin');
$router->post('/api/admin/delete', 'AdminController', 'apiDelete', 'admin');

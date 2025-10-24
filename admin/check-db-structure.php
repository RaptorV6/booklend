<?php
require __DIR__ . '/../config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get books table structure
$columns = $pdo->query("DESCRIBE books")->fetchAll(PDO::FETCH_ASSOC);

echo "Books table structure:\n";
echo str_repeat("=", 60) . "\n";
foreach ($columns as $col) {
    echo sprintf("%-20s %-20s %-10s\n",
        $col['Field'],
        $col['Type'],
        $col['Null']
    );
}

// Sample book data
echo "\n\nSample book data:\n";
echo str_repeat("=", 60) . "\n";
$book = $pdo->query("SELECT * FROM books LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($book) {
    foreach ($book as $key => $value) {
        echo sprintf("%-20s: %s\n", $key, $value ?? 'NULL');
    }
}

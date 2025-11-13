<?php
$pdo = new PDO('mysql:host=localhost;dbname=book', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Show all books (including soft-deleted)
$stmt = $pdo->query('
    SELECT id, isbn, title, deleted_at
    FROM books
    ORDER BY id DESC
    LIMIT 10
');
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Posledních 10 knih v databázi:\n";
echo str_repeat('=', 100) . "\n";

foreach ($books as $book) {
    $status = $book['deleted_at'] ? "SMAZÁNO ({$book['deleted_at']})" : "AKTIVNÍ";
    echo "ID: {$book['id']}\n";
    echo "  ISBN: {$book['isbn']}\n";
    echo "  Title: {$book['title']}\n";
    echo "  Status: $status\n";
    echo str_repeat('-', 100) . "\n";
}

// Count active vs deleted
$stmt = $pdo->query('SELECT
    COUNT(*) as total,
    SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) as deleted
    FROM books
');
$counts = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nCelkem: {$counts['total']} knih\n";
echo "Aktivní: {$counts['active']}\n";
echo "Smazané: {$counts['deleted']}\n";

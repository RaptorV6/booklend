<?php
$pdo = new PDO('mysql:host=localhost;dbname=book', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if there are any soft-deleted books
$stmt = $pdo->query('SELECT id, isbn, title, deleted_at FROM books WHERE deleted_at IS NOT NULL');
$deleted = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($deleted) > 0) {
    echo "Soft-deleted books in database:\n";
    echo str_repeat('-', 80) . "\n";
    foreach ($deleted as $book) {
        echo "ID: " . $book['id'] . " | ISBN: " . $book['isbn'] . " | Title: " . $book['title'] . "\n";
        echo "Deleted at: " . $book['deleted_at'] . "\n";
        echo str_repeat('-', 80) . "\n";
    }
} else {
    echo "No soft-deleted books found.\n";
}

// Check ISBN uniqueness
$stmt = $pdo->query('SELECT isbn, COUNT(*) as count FROM books GROUP BY isbn HAVING count > 1');
$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($duplicates) > 0) {
    echo "\nISBN duplicates (including soft-deleted):\n";
    foreach ($duplicates as $dup) {
        echo "ISBN " . $dup['isbn'] . " appears " . $dup['count'] . " times\n";
    }
} else {
    echo "\n✓ No ISBN duplicates found (soft-delete works correctly)\n";
}

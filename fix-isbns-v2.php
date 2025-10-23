<?php
// Fix ISBN for books without covers - Version 2

require __DIR__ . '/config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h1>🔧 Fixing ISBN for books</h1>";
echo "<hr>";

echo "<h2>Before:</h2>";
$stmt = $pdo->query("SELECT id, title, isbn, slug FROM books WHERE deleted_at IS NULL ORDER BY title");
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Title</th><th>ISBN</th><th>Slug</th></tr>";
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $book) {
    echo "<tr><td>{$book['id']}</td><td>{$book['title']}</td><td>{$book['isbn']}</td><td>{$book['slug']}</td></tr>";
}
echo "</table>";

echo "<hr><h2>Updating...</h2>";

// Update Alchymista
$stmt = $pdo->prepare("UPDATE books SET isbn = ? WHERE slug = ?");
$result1 = $stmt->execute(['9780062315007', 'alchymista']);
$rows1 = $stmt->rowCount();
echo "<p>✓ Alchymista: Updated {$rows1} row(s) to ISBN <strong>9780062315007</strong></p>";

// Update Malý princ
$stmt = $pdo->prepare("UPDATE books SET isbn = ? WHERE slug = ?");
$result2 = $stmt->execute(['9780156012195', 'maly-princ']);
$rows2 = $stmt->rowCount();
echo "<p>✓ Malý princ: Updated {$rows2} row(s) to ISBN <strong>9780156012195</strong></p>";

echo "<hr><h2>After:</h2>";
$stmt = $pdo->query("SELECT id, title, isbn, slug FROM books WHERE deleted_at IS NULL ORDER BY title");
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Title</th><th>ISBN</th><th>Slug</th></tr>";
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $book) {
    echo "<tr><td>{$book['id']}</td><td>{$book['title']}</td><td>{$book['isbn']}</td><td>{$book['slug']}</td></tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><a href='/booklend/clear-cache.php'>→ Clear cache</a></p>";
echo "<p><a href='/booklend/update-books-metadata.php'>→ Update metadata</a></p>";
echo "<p><a href='/booklend/public/'>← Back to catalog</a></p>";

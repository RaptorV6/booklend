<?php
/**
 * Migration: Přidání žánrů do books tabulky
 */

require __DIR__ . '/../config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h1>Migration: Přidání žánrů</h1>";
echo "<hr>";

try {
    // 1. Přidat sloupec genre
    echo "<h2>Krok 1: Přidání sloupce 'genre'</h2>";

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM books LIKE 'genre'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE books ADD COLUMN genre VARCHAR(50) DEFAULT 'Ostatní' AFTER author");
        echo "<p style='color: green;'>✓ Sloupec 'genre' přidán</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Sloupec 'genre' již existuje</p>";
    }

    // 2. Naplnit žánry podle knih
    echo "<h2>Krok 2: Přiřazení žánrů knihám</h2>";

    $genreMapping = [
        // Fantasy
        'Harry Potter' => 'Fantasy',
        'Narnie' => 'Fantasy',
        'Eragon' => 'Fantasy',

        // Děti
        'Deník malého poseroutky' => 'Pro děti',
        'Malý princ' => 'Pro děti',

        // Klasika
        '1984' => 'Klasika',
        'Robinson Crusoe' => 'Klasika',
        'Alenka v říši divů' => 'Klasika',
        'Vánoční příběhy' => 'Klasika',
    ];

    $updated = 0;
    foreach ($genreMapping as $titlePattern => $genre) {
        $stmt = $pdo->prepare("UPDATE books SET genre = ? WHERE title LIKE ? AND deleted_at IS NULL");
        $stmt->execute([$genre, "%$titlePattern%"]);
        $count = $stmt->rowCount();

        if ($count > 0) {
            echo "<p>✓ <strong>{$genre}</strong>: {$count} knih ('{$titlePattern}')</p>";
            $updated += $count;
        }
    }

    echo "<hr>";
    echo "<h2>Výsledek:</h2>";
    echo "<p style='color: green; font-size: 1.2em;'>✓ Aktualizováno: <strong>{$updated}</strong> knih</p>";

    // 3. Zobrazit přehled žánrů
    echo "<h2>Přehled žánrů:</h2>";
    $stmt = $pdo->query("
        SELECT genre, COUNT(*) as count
        FROM books
        WHERE deleted_at IS NULL
        GROUP BY genre
        ORDER BY count DESC
    ");

    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Žánr</th><th>Počet knih</th></tr>";

    $total = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>{$row['genre']}</strong></td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
        $total += $row['count'];
    }

    echo "<tr style='background: #f0f0f0;'>";
    echo "<td><strong>CELKEM</strong></td>";
    echo "<td><strong>{$total}</strong></td>";
    echo "</tr>";
    echo "</table>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Chyba: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='../public/'>← Zpět na katalog</a></p>";

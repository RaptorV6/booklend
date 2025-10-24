<?php
/**
 * Migration: Přidání sloupce published_year (rok vydání knihy)
 */

require __DIR__ . '/../config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h1>Migration: Přidání roku vydání (published_year)</h1>";
echo "<hr>";

try {
    // 1. Přidat sloupec published_year
    echo "<h2>Krok 1: Přidání sloupce 'published_year'</h2>";

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM books LIKE 'published_year'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE books ADD COLUMN published_year INT(4) UNSIGNED NULL AFTER description");
        echo "✓ Sloupec 'published_year' byl přidán<br>";
    } else {
        echo "⚠ Sloupec 'published_year' již existuje<br>";
    }

    // 2. Přidat nějaké roky k existujícím knihám (ukázková data)
    echo "<h2>Krok 2: Přidání ukázkových roků vydání</h2>";

    $updates = [
        // Harry Potter série
        "Harry Potter a Kámen mudrců" => 2017,
        "Harry Potter a Ohnivý pohár" => 2017,
        "Harry Potter a Tajemná komnata" => 2017,
        "Harry Potter a Vězeň z Azkabanu" => 2017,

        // Fantasy
        "Percy Jackson: Zloděj blesku" => 2010,
        "Percy Jackson: Moře nestvůr" => 2013,
        "Hobit aneb Cesta tam a zase zpátky" => 2012,
        "Pán prstenů: Společenstvo Prstenu" => 2012,

        // Klasika
        "Malý princ" => 2015,
        "Romeo a Julie" => 2020,

        // Pro děti
        "Ferda Mravenec" => 2018,
    ];

    $updated = 0;
    foreach ($updates as $title => $year) {
        $stmt = $pdo->prepare("UPDATE books SET published_year = ? WHERE title = ? AND deleted_at IS NULL");
        if ($stmt->execute([$year, $title])) {
            if ($stmt->rowCount() > 0) {
                echo "✓ '{$title}' → {$year}<br>";
                $updated++;
            }
        }
    }

    echo "<br><strong>Celkem aktualizováno: {$updated} knih</strong><br>";

    // 3. Zobrazit statistiku
    echo "<h2>Krok 3: Statistika roků vydání</h2>";
    $stmt = $pdo->query("
        SELECT published_year, COUNT(*) as count
        FROM books
        WHERE deleted_at IS NULL
        GROUP BY published_year
        ORDER BY published_year DESC
    ");

    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Rok vydání</th><th>Počet knih</th></tr>";
    foreach ($stats as $stat) {
        $year = $stat['published_year'] ?? 'Neuvedeno';
        echo "<tr><td>{$year}</td><td>{$stat['count']}</td></tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Migrace úspěšně dokončena!</h3>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Chyba: " . $e->getMessage() . "</h3>";
}

<?php
/**
 * RESET BOOKS - Smaže všechny knihy a přidá POUZE populární knihy s KRÁSNÝMI OBALY
 * Inspirováno Harry Potterem - moderní bestsellery s HD obrázky
 */

require __DIR__ . '/../config.php';

// Autoloader pro Book model
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Inicializace Book modelu pro automatické stahování obrázků
$db = new app\Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$cache = new app\Cache(CACHE_DIR, CACHE_MAX_SIZE_MB, CACHE_MAX_FILES);
$bookModel = new app\Models\Book($db, $cache);

echo "<h1>🧹 Reset Books - Knihy s krásnými obaly</h1>";
echo "<hr>";

// 1. SMAZAT RENTALS (foreign key constraint)
echo "<h2>Krok 1: Mazání výpůjček...</h2>";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM rentals");
$rentalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "<p>Nalezeno <strong>{$rentalCount}</strong> výpůjček</p>";
$pdo->exec("DELETE FROM rentals");
echo "<p style='color: green;'>✓ Všechny výpůjčky smazány</p>";

// 2. SMAZAT VŠECHNY KNIHY
echo "<h2>Krok 2: Mazání všech knih...</h2>";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM books WHERE deleted_at IS NULL");
$oldCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "<p>Nalezeno <strong>{$oldCount}</strong> knih</p>";

$pdo->exec("DELETE FROM books");
echo "<p style='color: green;'>✓ Všechny knihy smazány</p>";

// 3. PŘIDAT KNIHY S KRÁSNÝMI OBALY
echo "<hr>";
echo "<h2>Krok 3: Přidávání populárních knih s HD obrázky...</h2>";

$books = [
    // ═══════════════════════════════════════════════════════════
    // HARRY POTTER SÉRIE - Mezinárodní vydání s českým názvem
    // (České Albatros vydání nemá obrázky v API, proto používáme Pottermore)
    // ═══════════════════════════════════════════════════════════
    ['9781781107508', 'Harry Potter a Kámen mudrců', 'J.K. Rowling', 'harry-potter-kamen-mudrcu'],
    ['9781781107515', 'Harry Potter a Tajemná komnata', 'J.K. Rowling', 'harry-potter-tajemna-komnata'],
    ['9781781107522', 'Harry Potter a Vězeň z Azkabanu', 'J.K. Rowling', 'harry-potter-vezen-z-azkabanu'],
    ['9781781107539', 'Harry Potter a Ohnivý pohár', 'J.K. Rowling', 'harry-potter-ohnivy-pohar'],
    ['9781781107546', 'Harry Potter a Fénixův řád', 'J.K. Rowling', 'harry-potter-fenixuv-rad'],

    // ═══════════════════════════════════════════════════════════
    // NARNIE - České vydání od nakladatelství Portál ✓
    // ═══════════════════════════════════════════════════════════
    ['9788025326800', 'Narnie: Lev, čarodějnice a skříň', 'C. S. Lewis', 'narnie-lev-carodejnice-skrin'],
    ['9788025326923', 'Narnie: Stříbrná židle', 'C. S. Lewis', 'narnie-stribrna-zidle'],
    ['9788025326831', 'Narnie: Kůň a jeho chlapec', 'C. S. Lewis', 'narnie-kun-jeho-chlapec'],
    ['9788025326770', 'Narnie: Čarodějův synovec', 'C. S. Lewis', 'narnie-carodejuv-synovec'],

    // ═══════════════════════════════════════════════════════════
    // ERAGON - České vydání Portál ✓
    // ═══════════════════════════════════════════════════════════
    ['9788025315217', 'Eragon', 'Christopher Paolini', 'eragon'],

    // ═══════════════════════════════════════════════════════════
    // DENÍK MALÉHO POSEROUTKY - České vydání Albatros ✓
    // ═══════════════════════════════════════════════════════════
    ['9788075447210', 'Deník malého poseroutky: Radosti zimy', 'Jeff Kinney', 'denik-maleho-poseroutky-radosti-zimy'],

    // ═══════════════════════════════════════════════════════════
    // MALÝ PRINC - České vydání Albatros ✓
    // ═══════════════════════════════════════════════════════════
    ['9788000047935', 'Malý princ', 'Antoine de Saint-Exupéry', 'maly-princ'],

    // ═══════════════════════════════════════════════════════════
    // KLASICKÉ KNIHY - Ověřené HD obrázky ✓
    // ═══════════════════════════════════════════════════════════
    ['9780547249643', '1984', 'George Orwell', '1984-george-orwell'],
    ['9791191943375', 'Robinson Crusoe', 'Daniel Defoe', 'robinson-crusoe'],
    ['9788024736907', 'Alenka v říši divů', 'Lewis Carroll', 'alenka-v-risi-divu'],
    ['9788076614352', 'Vánoční příběhy', 'Charles Dickens', 'vanocni-pribehy'],
];

$added = 0;
$withCovers = 0;

echo "<div style='background: #1e293b; padding: 1rem; border-radius: 8px;'>";

foreach ($books as $book) {
    list($isbn, $title, $author, $slug) = $book;

    try {
        // 1. Vložit knihu
        $stmt = $pdo->prepare('
            INSERT INTO books (isbn, slug, title, author, total_copies, available_copies)
            VALUES (?, ?, ?, ?, 5, 5)
        ');
        $stmt->execute([$isbn, $slug, $title, $author]);
        $bookId = $pdo->lastInsertId();
        $added++;

        echo "<div style='margin: 10px 0; padding: 10px; background: #0f172a; border-radius: 4px;'>";
        echo "<strong style='color: #10b981;'>✓ {$title}</strong><br>";
        echo "<small style='color: #94a3b8;'>Autor: {$author} | ISBN: {$isbn}</small><br>";

        // 2. AUTOMATICKY stáhnout metadata a obrázek
        echo "<small style='color: #60a5fa;'>→ Stahování obrázku z API...</small><br>";
        $metadata = $bookModel->fetchMetadata($isbn);

        if ($metadata && !empty($metadata['thumbnail'])) {
            // Check if it's a placeholder by downloading and checking size
            $ch = curl_init($metadata['thumbnail']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $imageData = curl_exec($ch);
            $imageSize = strlen($imageData);
            curl_close($ch);

            $isPlaceholder = $imageSize < 10000; // < 10KB = placeholder

            if (!$isPlaceholder) {
                // Uložit thumbnail a description
                $pdo->prepare("UPDATE books SET thumbnail = ?, description = ? WHERE id = ?")
                    ->execute([$metadata['thumbnail'], $metadata['description'], $bookId]);

                $sizeKB = round($imageSize / 1024, 2);
                echo "<small style='color: #10b981;'>✓ Obrázek nalezen! ({$sizeKB} KB)</small>";
                echo "<img src='{$metadata['thumbnail']}' style='max-height: 80px; margin: 5px 0; display: block; border-radius: 4px;'><br>";
                $withCovers++;
            } else {
                // Delete book with placeholder
                $pdo->exec("DELETE FROM books WHERE id = $bookId");
                $added--;
                echo "<small style='color: #f59e0b;'>⚠ Placeholder obrázek (odstraněno)</small><br>";
            }
        } else {
            // Delete book without image
            $pdo->exec("DELETE FROM books WHERE id = $bookId");
            $added--;
            echo "<small style='color: #ef4444;'>✗ Obrázek nenalezen (odstraněno)</small><br>";
        }

        echo "</div>";

        usleep(200000); // 0.2s delay to avoid API rate limiting

    } catch (Exception $e) {
        echo "<div style='margin: 10px 0; padding: 10px; background: #450a0a; border-radius: 4px;'>";
        echo "<strong style='color: #ef4444;'>✗ Chyba: {$title}</strong><br>";
        echo "<small style='color: #fca5a5;'>" . $e->getMessage() . "</small>";
        echo "</div>";
    }
}

echo "</div>";

echo "<hr>";
echo "<h2>📊 Výsledek:</h2>";
echo "<ul>";
echo "<li>Smazáno: <strong>{$oldCount}</strong> knih</li>";
echo "<li>Přidáno: <strong style='color: green;'>{$added}</strong> knih</li>";
echo "<li>Obrázky staženy: <strong style='color: #10b981;'>{$withCovers}</strong> / {$added}</li>";
if ($withCovers < $added) {
    $missing = $added - $withCovers;
    echo "<li style='color: #f59e0b;'>⚠ Bez obrázku: <strong>{$missing}</strong> knih (nejsou v Google Books API)</li>";
}
echo "</ul>";

echo "<hr>";
echo "<h3 style='color: #10b981;'>✓ Hotovo! Obrázky byly staženy AUTOMATICKY</h3>";
echo "<p>Už <strong>není potřeba</strong> pouštět update-books-metadata.php!</p>";
echo "<hr>";
echo "<p><a href='../public/' style='font-size: 1.2em; color: #60a5fa;'>← Zpět na katalog</a></p>";
echo "<p><small><a href='clear-cache.php'>Vyčistit cache</a> (pokud máš problémy s cachováním)</small></p>";

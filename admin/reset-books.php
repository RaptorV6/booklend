<?php
/**
 * RESET BOOKS - Smaže všechny knihy kromě původních 5
 * A přidá POUZE české knihy s českými ISBN
 */

require __DIR__ . '/../config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h1>🧹 Reset Books - Pouze české knihy</h1>";
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

// 2. PŘIDAT ČESKÉ KNIHY S ČESKÝMI ISBN
echo "<hr>";
echo "<h2>Krok 2: Přidávání ČESKÝCH knih...</h2>";

$czechBooks = [
    // Klasická česká literatura
    ['9788024279961', 'Babička', 'Božena Němcová', 'babicka-bozena-nemcova'],
    ['9788073393090', 'Povídky malostranské', 'Jan Neruda', 'povidky-malostranske-jan-neruda'],
    ['9788073211547', 'R.U.R.', 'Karel Čapek', 'rur-karel-capek'],
    ['9788024274652', 'Osudy dobrého vojáka Švejka', 'Jaroslav Hašek', 'osudy-dobreho-vojaka-svejka-jaroslav-hasek'],

    // Moderní česká literatura
    ['9788025707333', 'Saturnin', 'Zdeněk Jirotka', 'saturnin-zdenek-jirotka'],
    ['9788024270562', 'Spalovač mrtvol', 'Ladislav Fuks', 'spalovac-mrtvol-ladislav-fuks'],
    ['9788072037735', 'Věci, které jsem zatím nepozapomněl', 'Bohumil Hrabal', 'veci-ktere-jsem-zatim-nepozapomnel-bohumil-hrabal'],
    ['9788074284458', 'Obsluhoval jsem anglického krále', 'Bohumil Hrabal', 'obsluhoval-jsem-anglickeho-krale-bohumil-hrabal'],

    // Česká poezie a drama
    ['9788024279794', 'Máj', 'Karel Hynek Mácha', 'maj-karel-hynek-macha'],
    ['9788073393106', 'Kytice', 'Karel Jaromír Erben', 'kytice-karel-jaromir-erben'],

    // České fantasy a sci-fi
    ['9788024276755', 'Meč času', 'Jaroslav Mostecký', 'mec-casu-jaroslav-mostecky'],
    ['9788072015443', 'Návrat', 'Eduard Štorch', 'navrat-eduard-storch'],

    // Dětská česká literatura
    ['9788024274669', 'O princezně Jasněnce a létajícím ševci', 'František Hrubín', 'o-princezne-jasnence-frantisek-hrubin'],
    ['9788024277561', 'Byl jednou jeden král', 'Jiří Žáček', 'byl-jednou-jeden-kral-jiri-zacek'],

    // České detektivky
    ['9788025709887', 'Smrt krásných srnců', 'Ota Pavel', 'smrt-krasnych-srncu-ota-pavel'],
    ['9788024279978', 'Petrolejové lampy', 'Jaroslav Havlíček', 'petrolejove-lampy-jaroslav-havlicek'],

    // České překlady s českým ISBN
    ['9788000047935', 'Malý princ', 'Antoine de Saint-Exupéry', 'maly-princ-antoine-de-saint-exupery'],
    ['9781781107508', 'Harry Potter a Kámen mudrců', 'J.K. Rowling', 'harry-potter-kamen-mudrcuu'],
];

$added = 0;

foreach ($czechBooks as $book) {
    list($isbn, $title, $author, $slug) = $book;

    try {
        $stmt = $pdo->prepare('
            INSERT INTO books (isbn, slug, title, author, total_copies, available_copies)
            VALUES (?, ?, ?, ?, 5, 5)
        ');

        $stmt->execute([$isbn, $slug, $title, $author]);
        echo "<p style='color: green;'>✓ {$title} - {$author}</p>";
        $added++;

    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Chyba: {$title} - " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<h2>📊 Výsledek:</h2>";
echo "<ul>";
echo "<li>Smazáno: <strong>{$oldCount}</strong> knih</li>";
echo "<li>Přidáno: <strong style='color: green;'>{$added}</strong> ČESKÝCH knih</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='clear-cache.php'>→ Vyčistit cache</a></p>";
echo "<p><a href='update-books-metadata.php'>→ Aktualizovat metadata (obrázky)</a></p>";
echo "<p><a href='../public/'>← Zpět na katalog</a></p>";

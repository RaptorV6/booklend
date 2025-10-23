<?php
// Clear all cache files - AGGRESSIVE VERSION

$cacheDir = __DIR__ . '/public/cache';

echo "<h1>Cache Cleaner - AGGRESSIVE MODE</h1>";
echo "<p>Cache directory: {$cacheDir}</p>";

if (!is_dir($cacheDir)) {
    echo "<p style='color: orange;'>⚠ Cache složka neexistuje. Vytvářím...</p>";
    mkdir($cacheDir, 0755, true);
    echo "<p style='color: green;'>✓ Vytvořeno!</p>";
} else {
    // Method 1: Delete all .cache files recursively
    $pattern1 = $cacheDir . '/*/*/*\.cache';
    $pattern2 = $cacheDir . '/*/*\.cache';
    $pattern3 = $cacheDir . '/*\.cache';

    $files = array_merge(
        glob($pattern1) ?: [],
        glob($pattern2) ?: [],
        glob($pattern3) ?: []
    );

    echo "<p>Nalezeno " . count($files) . " cache souborů...</p>";

    $count = 0;
    foreach ($files as $file) {
        echo "Mažu: " . basename($file) . "<br>";
        if (@unlink($file)) {
            $count++;
        }
    }

    echo "<p style='color: green;'>✓ Vymazáno {$count} cache souborů!</p>";

    // Method 2: Delete all subdirectories
    function rrmdir($dir) {
        if (!is_dir($dir)) return;

        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object == "." || $object == "..") continue;

            $path = $dir . "/" . $object;
            if (is_dir($path)) {
                rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    // Clean subdirectories but keep cache dir
    $subdirs = glob($cacheDir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs as $subdir) {
        echo "Čistím složku: " . basename($subdir) . "<br>";
        rrmdir($subdir);
    }

    echo "<p style='color: green;'>✓ Vyčištěny všechny podsložky!</p>";
}

// Verify
$remaining = glob($cacheDir . '/*/*/*\.cache');
if (empty($remaining)) {
    echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>✓✓✓ CACHE KOMPLETNĚ VYMAZÁNA! ✓✓✓</p>";
} else {
    echo "<p style='color: red;'>⚠ Zůstalo " . count($remaining) . " souborů!</p>";
}

echo "<p><a href='/booklend/public/'>← Zpět na hlavní stránku</a></p>";
echo "<p><a href='/booklend/test-book-detail.php'>→ Test book detail</a></p>";

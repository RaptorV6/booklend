<?php
// Run migration: Add language column

$host = 'localhost';
$dbname = 'book';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read migration file
    $sql = file_get_contents('migration_add_language_column.sql');

    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        // Skip comments and empty statements
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }

        echo "Executing: " . substr($statement, 0, 80) . "...\n";
        $pdo->exec($statement);
        echo "✓ Success\n\n";
    }

    echo "\n=================================\n";
    echo "Migration completed successfully!\n";
    echo "=================================\n\n";

    // Verify column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM books LIKE 'language'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($column) {
        echo "Verified: language column exists\n";
        echo "  Type: " . $column['Type'] . "\n";
        echo "  Default: " . $column['Default'] . "\n";
    } else {
        echo "ERROR: language column was not created!\n";
        exit(1);
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

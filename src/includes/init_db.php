<?php
// Run this script ONCE to initialize the database tables using PDO
require_once __DIR__ . '/../includes/db_connect.php';

try {
    $pdo = get_db_connection();

    $sql_file = __DIR__ . '/db_schema.sql';
    if (!file_exists($sql_file)) {
        die('SQL schema file not found.');
    }

    $sql = file_get_contents($sql_file);
    if (!$sql) {
        die('Failed to read SQL schema file.');
    }

    // Split SQL into individual statements (rudimentary)
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }

    echo "Database tables created successfully.<br>";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage() . "<br>";
}

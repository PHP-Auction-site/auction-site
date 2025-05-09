<?php
// Run this script ONCE to initialize the database tables
require_once __DIR__ . '/../includes/db_connect.php';

$mysqli = get_db_connection();

$sql_file = __DIR__ . '/db_schema.sql';
if (!file_exists($sql_file)) {
    die('SQL schema file not found.');
}

$sql = file_get_contents($sql_file);
if (!$sql) {
    die('Failed to read SQL schema file.');
}

if ($mysqli->multi_query($sql)) {
    do {
        // flush results for each statement
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->next_result());
    echo "Database tables created successfully.<br>";
} else {
    echo "Error creating tables: " . $mysqli->error . "<br>";
}

$mysqli->close(); 
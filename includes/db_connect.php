<?php
// Database connection helper

require_once __DIR__ . '/../config.php';

function get_db_connection() {
    static $mysqli = null;
    
    if ($mysqli === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            $mysqli->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    return $mysqli;
}

// Function to safely close database connection
function close_db_connection($mysqli) {
    if ($mysqli instanceof mysqli && !$mysqli->connect_error) {
        $mysqli->close();
    }
}

// Placeholder for database connection code 
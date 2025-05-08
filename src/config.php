<?php
// Configuration settings for the auction site
// Load sensitive data from .env

// Function to parse .env file values
function parseEnvValue($value) {
    // Remove quotes if present
    if (preg_match('/^".*"$/', $value) || preg_match("/^'.*'$/", $value)) {
        $value = substr($value, 1, -1);
    }
    
    // Handle arrays (JSON format)
    if (preg_match('/^\[.*\]$/', $value)) {
        return json_decode($value, true) ?? $value;
    }
    
    // Handle comments in values
    if (strpos($value, '#') !== false) {
        $value = trim(explode('#', $value)[0]);
    }
    
    return $value;
}

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = parseEnvValue(trim($parts[1]));
            
            if (is_array($value)) {
                $_ENV[$key] = $value;
            } else {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Load environment variables
loadEnv(__DIR__ . '/../.env');

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: $_ENV['DB_HOST']);
define('DB_USER', getenv('DB_USER') ?: $_ENV['DB_USER']);
define('DB_PASS', getenv('DB_PASS') ?: $_ENV['DB_PASS']);
define('DB_NAME', getenv('DB_NAME') ?: $_ENV['DB_NAME']);
define('DB_PORT', getenv('DB_PORT') ?: $_ENV['DB_PORT']);

// Site configuration
define('SITE_NAME', getenv('SITE_NAME') ?: $_ENV['SITE_NAME']);
define('SITE_URL', rtrim(getenv('SITE_URL') ?: $_ENV['SITE_URL'], '/'));
define('TIMEZONE', getenv('TIMEZONE') ?: $_ENV['TIMEZONE']);

// Upload settings
define('MAX_UPLOAD_SIZE', (int)(getenv('MAX_UPLOAD_SIZE') ?: $_ENV['MAX_UPLOAD_SIZE']));
define('ALLOWED_IMAGE_TYPES', $_ENV['ALLOWED_IMAGE_TYPES']);
define('UPLOAD_PATH', getenv('UPLOAD_PATH') ?: $_ENV['UPLOAD_PATH']);

// Security settings
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: $_ENV['SESSION_LIFETIME']));

// Set timezone
date_default_timezone_set(TIMEZONE);

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}

// Error reporting in development
if (getenv('APP_ENV') !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Define base paths
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('PUBLIC_PATH', BASE_PATH . '/public');

// Function to get relative URL path
function getRelativePath($path) {
    return str_replace(PUBLIC_PATH, '', $path);
}
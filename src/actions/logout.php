<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Set flash message
session_start();
set_flash_message('You have been successfully logged out.', 'success');

// Redirect to home page
header('Location: ' . SITE_URL . '/public/index.php');
exit; 
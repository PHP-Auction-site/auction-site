<?php
// Session check and authentication helper functions

// No need to start session here as it's already started in config.php
require_once __DIR__ . '/../config.php';

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to require login for protected pages
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash_message'] = "Please log in to access this page.";
        $_SESSION['flash_type'] = "warning";
        header("Location: " . SITE_URL . "/public/login.php");
        exit();
    }
}

// Function to redirect if already logged in
function redirect_if_logged_in() {
    if (is_logged_in()) {
        header("Location: " . SITE_URL . "/public/dashboard.php");
        exit();
    }
}

// Function to get current user ID
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current username
function get_username() {
    return $_SESSION['username'] ?? null;
} 
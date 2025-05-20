<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    if (!is_valid_username($username)) {
        header('Location: register.php?error=Invalid+username');
        exit;
    }
    if (!is_valid_email($email)) {
        header('Location: register.php?error=Invalid+email');
        exit;
    }
    if (!is_valid_password($password)) {
        header('Location: register.php?error=Password+must+be+at+least+6+characters');
        exit;
    }

    try {
        $pdo = get_db_connection();

        // Check for existing username or email
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        if ($stmt->fetch()) {
            header('Location: register.php?error=Username+or+email+already+exists');
            exit;
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $password_hash
        ]);

        header('Location: register.php?success=Registration+successful.+You+may+now+log+in.');
        exit;

    } catch (PDOException $e) {
        // Optionally log the error
        // error_log("Registration Error: " . $e->getMessage());
        header('Location: register.php?error=Registration+failed.+Please+try+again.');
        exit;
    }
} else {
    header('Location: register.php');
    exit;
}

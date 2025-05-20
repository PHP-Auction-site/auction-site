<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!is_valid_username($username)) {
        header('Location: login.php?error=Invalid+username');
        exit;
    }
    if (!is_valid_password($password)) {
        header('Location: login.php?error=Invalid+password');
        exit;
    }

    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT user_id, username, password_hash FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Success: set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            header('Location: login.php?error=Invalid+username+or+password');
            exit;
        }
    } catch (PDOException $e) {
        // Optionally log error: error_log($e->getMessage());
        header('Location: login.php?error=Server+error');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!is_valid_username($username)) {
        header('Location: login.php?error=Invalid+username');
        exit;
    }
    if (!is_valid_password($password)) {
        header('Location: login.php?error=Invalid+password');
        exit;
    }

    $mysqli = get_db_connection();
    $stmt = $mysqli->prepare('SELECT user_id, username, password_hash FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $db_username, $password_hash);
        $stmt->fetch();
        if (password_verify($password, $password_hash)) {
            // Success: set session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $db_username;
            $stmt->close();
            $mysqli->close();
            header('Location: dashboard.php');
            exit;
        }
    }
    $stmt->close();
    $mysqli->close();
    header('Location: login.php?error=Invalid+username+or+password');
    exit;
} else {
    header('Location: login.php');
    exit;
} 

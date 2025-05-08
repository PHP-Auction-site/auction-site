<?php
require_once '../src/config.php';
require_once '../src/includes/functions.php';
require_once '../src/includes/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    set_flash_message('Please log in to access your profile.', 'warning');
    header('Location: ' . SITE_URL . '/public/login.php');
    exit;
}

$page_title = 'Edit Profile';
$current_page = 'profile';

// Initialize database connection
$mysqli = get_db_connection();

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT username, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $errors = [];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email is already taken by another user
    $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email is already taken";
    }
    $stmt->close();

    // If changing password, validate it
    if (!empty($current_password)) {
        // Verify current password
        $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($current_password, $user_data['password_hash'])) {
            $errors[] = "Current password is incorrect";
        }

        if (empty($new_password)) {
            $errors[] = "New password is required when changing password";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }

    if (empty($errors)) {
        // Start transaction
        $mysqli->begin_transaction();

        try {
            // Update user info
            $stmt = $mysqli->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $stmt->close();

            // Update password if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
                $stmt->close();
            }

            $mysqli->commit();
            set_flash_message("Profile updated successfully!", "success");
            header("Location: " . SITE_URL . "/public/profile.php");
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            $errors[] = "An error occurred while updating your profile. Please try again.";
        }
    }
}

require_once '../src/templates/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0"><i class="fas fa-user-edit me-2"></i>Edit Profile</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo h($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo h($user['username']); ?>" disabled>
                            <div class="form-text">Username cannot be changed</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo h($user['email']); ?>" required>
                        </div>

                        <hr class="my-4">

                        <h4 class="h5 mb-3">Change Password (optional)</h4>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <div class="form-text">Leave blank if you don't want to change your password</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="<?php echo SITE_URL; ?>/public/dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../src/templates/footer.php';
$mysqli->close();
?> 
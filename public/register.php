<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/includes/session_check.php';

// Redirect if already logged in
redirect_if_logged_in();

$page_title = 'Register';
$current_page = 'register';
require_once __DIR__ . '/../src/templates/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </h2>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo SITE_URL; ?>/public/actions/register_user.php" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       pattern="[a-zA-Z0-9_]{3,20}" required>
                                <div class="invalid-feedback">
                                    Username must be 3-20 characters and can only contain letters, numbers, and underscores.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       pattern=".{8,}" required>
                                <div class="invalid-feedback">
                                    Password must be at least 8 characters long.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                                <div class="invalid-feedback">
                                    Passwords must match.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? 
                            <a href="<?php echo SITE_URL; ?>/public/login.php">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Custom validation for password confirmation
document.getElementById('confirm_password').addEventListener('input', function() {
    if (this.value !== document.getElementById('password').value) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
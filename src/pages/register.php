<?php
require_once __DIR__ . '/../templates/header.php';
?>
<h2>User Registration</h2>
<?php if (!empty($_GET['error'])): ?>
    <div class="error"><?php echo sanitize_output($_GET['error']); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['success'])): ?>
    <div class="success"><?php echo sanitize_output($_GET['success']); ?></div>
<?php endif; ?>
<form action="register_user.php" method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br>
    <button type="submit">Register</button>
</form>
<?php
require_once __DIR__ . '/../templates/footer.php'; 
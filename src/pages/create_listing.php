<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../templates/header.php';

require_login();

try {
    $pdo = get_db_connection();

    $stmt = $pdo->query('SELECT category_id, category_name FROM categories ORDER BY category_name ASC');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Optionally log the error
    // error_log("Database Error: " . $e->getMessage());
    echo '<div class="error">Failed to load categories. Please try again later.</div>';
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}
?>
<h2>List a New Item for Auction</h2>
<?php if (!empty($_GET['error'])): ?>
    <div class="error"><?php echo sanitize_output($_GET['error']); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['success'])): ?>
    <div class="success"><?php echo sanitize_output($_GET['success']); ?></div>
<?php endif; ?>
<form action="create_item.php" method="post" enctype="multipart/form-data">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required><br>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea><br>

    <label for="category">Category:</label>
    <select id="category" name="category_id" required>
        <option value="">Select a category</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo (int)$cat['category_id']; ?>">
                <?php echo sanitize_output($cat['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label for="starting_price">Starting Price:</label>
    <input type="number" id="starting_price" name="starting_price" min="0.01" step="0.01" required><br>

    <label for="end_time">Auction End Time (YYYY-MM-DD HH:MM):</label>
    <input type="datetime-local" id="end_time" name="end_time" required><br>

    <label for="image">Image (JPEG, PNG, GIF, max 2MB):</label>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif" required><br>

    <button type="submit">Create Listing</button>
</form>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

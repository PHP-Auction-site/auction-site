<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../templates/header.php';

mark_ended_auctions();

$mysqli = get_db_connection();
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Fetch categories for filter dropdown
$categories = $mysqli->query('SELECT category_id, category_name FROM categories ORDER BY category_name ASC');

// Fetch active items, optionally filtered by category
if ($category_id) {
    $stmt = $mysqli->prepare('SELECT * FROM items WHERE status = "Active" AND category_id = ? ORDER BY end_time ASC');
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $items = $stmt->get_result();
    $stmt->close();
} else {
    $items = $mysqli->query('SELECT * FROM items WHERE status = "Active" ORDER BY end_time ASC');
}
?>
<h2>Active Auctions</h2>
<form method="get" action="home.php">
    <label for="category_id">Filter by Category:</label>
    <select name="category_id" id="category_id" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php while ($cat = $categories->fetch_assoc()): ?>
            <option value="<?php echo (int)$cat['category_id']; ?>" <?php if ($category_id == $cat['category_id']) echo 'selected'; ?>><?php echo sanitize_output($cat['category_name']); ?></option>
        <?php endwhile; ?>
    </select>
    <noscript><button type="submit">Filter</button></noscript>
</form>
<div class="auction-list">
<?php if ($items->num_rows > 0): ?>
    <?php while ($item = $items->fetch_assoc()): ?>
        <?php include __DIR__ . '/../templates/item_card.php'; ?>
    <?php endwhile; ?>
<?php else: ?>
    <p>No active auctions found.</p>
<?php endif; ?>
</div>
<?php
$mysqli->close();
require_once __DIR__ . '/../templates/footer.php'; 
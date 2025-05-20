<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../templates/header.php';

mark_ended_auctions();

$pdo = get_db_connection();
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Fetch categories for filter dropdown
$category_stmt = $pdo->query('SELECT category_id, category_name FROM categories ORDER BY category_name ASC');
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch active items, optionally filtered by category
if ($category_id) {
    $item_stmt = $pdo->prepare('SELECT * FROM items WHERE status = "Active" AND category_id = :category_id ORDER BY end_time ASC');
    $item_stmt->execute(['category_id' => $category_id]);
} else {
    $item_stmt = $pdo->query('SELECT * FROM items WHERE status = "Active" ORDER BY end_time ASC');
}
$items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Active Auctions</h2>
<form method="get" action="home.php">
    <label for="category_id">Filter by Category:</label>
    <select name="category_id" id="category_id" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo (int)$cat['category_id']; ?>" <?php if ($category_id == $cat['category_id']) echo 'selected'; ?>>
                <?php echo sanitize_output($cat['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Filter</button></noscript>
</form>

<div class="auction-list">
<?php if (count($items) > 0): ?>
    <?php foreach ($items as $item): ?>
        <?php include __DIR__ . '/../templates/item_card.php'; ?>
    <?php endforeach; ?>
<?php else: ?>
    <p>No active auctions found.</p>
<?php endif; ?>
</div>
<?php
require_once __DIR__ . '/../templates/footer.php';
?>

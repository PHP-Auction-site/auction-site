<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/includes/db_connect.php';

$page_title = 'Home';
$current_page = 'home';
require_once __DIR__ . '/../src/templates/header.php';

// Get database connection
$mysqli = get_db_connection();

// Get categories for filter
$categories = [];
$result = $mysqli->query('SELECT * FROM categories ORDER BY category_name');
while ($category = $result->fetch_assoc()) {
    $categories[] = $category;
}

// Get active items with their categories
$query = "SELECT i.*, c.category_name, u.username as seller_name,
                 (SELECT COUNT(*) FROM bids WHERE item_id = i.item_id) as bid_count
          FROM items i
          LEFT JOIN categories c ON i.category_id = c.category_id
          LEFT JOIN users u ON i.user_id = u.user_id
          WHERE i.end_time > NOW()";

// Add category filter if specified
if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $category_id = (int)$_GET['category'];
    $query .= " AND i.category_id = " . $category_id;
}

$query .= " ORDER BY i.item_id DESC";
$items = $mysqli->query($query);
?>

<div class="container">
    <h1 class="mb-4">Active Auctions</h1>

    <!-- Category Filter -->
    <div class="mb-4">
        <h5 class="mb-3">Filter by Category</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo SITE_URL; ?>/public/index.php" 
               class="category-badge <?php echo !isset($_GET['category']) ? 'bg-primary text-white' : ''; ?>">
                All Categories
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo SITE_URL; ?>/public/index.php?category=<?php echo $category['category_id']; ?>" 
                   class="category-badge <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'bg-primary text-white' : ''; ?>">
                    <?php echo h($category['category_name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="row">
        <?php if ($items->num_rows > 0): ?>
            <?php while ($item = $items->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card item-card h-100">
                        <?php if ($item['image_path']): ?>
                            
                            <img src="<?php echo h($item['image_path']); ?>" 
                                 class="card-img-top item-image" 
                                 alt="<?php echo h($item['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo h($item['title']); ?></h5>
                            <p class="card-text text-muted">
                                <small>
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo h($item['seller_name']); ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <span class="badge bg-secondary">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo h($item['category_name']); ?>
                                </span>
                            </p>
                            <p class="current-bid">
                                Current Price: <?php echo format_currency($item['current_price']); ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-gavel me-1"></i>
                                <?php echo $item['bid_count']; ?> bid(s)
                            </p>
                            <p class="mb-3">
                                <i class="fas fa-clock me-1"></i>
                                Ends: <span class="countdown" data-end-time="<?php echo $item['end_time']; ?>">
                                    <?php echo format_datetime($item['end_time']); ?>
                                </span>
                            </p>
                            <a href="<?php echo SITE_URL; ?>/public/item_details.php?id=<?php echo $item['item_id']; ?>" 
                               class="btn btn-primary w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No active auctions found.
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo SITE_URL; ?>/public/create_item.php">Create one now!</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../src/templates/footer.php';
$mysqli->close();
?>
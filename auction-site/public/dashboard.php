<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/includes/session_check.php';
require_once __DIR__ . '/../src/includes/db_connect.php';

// Require login for this page
require_login();

$page_title = 'Dashboard';
$current_page = 'dashboard';
require_once __DIR__ . '/../src/templates/header.php';

// Get user's items and bids
$mysqli = get_db_connection();
$user_id = $_SESSION['user_id'];

// Get user's listed items
$items_stmt = $mysqli->prepare("
    SELECT i.*, c.category_name, 
           (SELECT COUNT(*) FROM bids WHERE item_id = i.item_id) as bid_count
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.category_id
    WHERE i.user_id = ?
    ORDER BY i.end_time DESC
");
$items_stmt->bind_param("i", $user_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Get user's bids
$bids_stmt = $mysqli->prepare("
    SELECT i.*, b.bid_amount, b.bid_time,
           (SELECT MAX(bid_amount) FROM bids WHERE item_id = i.item_id) as current_highest_bid
    FROM bids b
    JOIN items i ON b.item_id = i.item_id
    WHERE b.user_id = ?
    ORDER BY b.bid_time DESC
");
$bids_stmt->bind_param("i", $user_id);
$bids_stmt->execute();
$bids_result = $bids_stmt->get_result();
?>

<div class="container">
    <h1 class="mb-4">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
    </h1>

    <!-- Stats Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-box me-2"></i>Your Items
                    </h5>
                    <p class="card-text h2"><?php echo $items_result->num_rows; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-gavel me-2"></i>Active Bids
                    </h5>
                    <p class="card-text h2"><?php echo $bids_result->num_rows; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-trophy me-2"></i>Won Auctions
                    </h5>
                    <p class="card-text h2">
                        <?php
                        $won_auctions = 0;
                        $bids_result->data_seek(0);
                        while ($bid = $bids_result->fetch_assoc()) {
                            if ($bid['end_time'] < date('Y-m-d H:i:s') && 
                                $bid['bid_amount'] == $bid['current_highest_bid']) {
                                $won_auctions++;
                            }
                        }
                        echo $won_auctions;
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Your Items -->
    <section class="mb-5">
        <h2 class="mb-4">
            <i class="fas fa-box me-2"></i>Your Items
            <a href="<?php echo SITE_URL; ?>/public/create_item.php" class="btn btn-primary float-end">
                <i class="fas fa-plus me-2"></i>List New Item
            </a>
        </h2>

        <?php if ($items_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Current Price</th>
                            <th>Bids</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/public/item_details.php?id=<?php echo $item['item_id']; ?>">
                                        <?php echo h($item['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo h($item['category_name']); ?></td>
                                <td><?php echo format_currency($item['current_price']); ?></td>
                                <td><?php echo $item['bid_count']; ?></td>
                                <td>
                                    <span class="countdown" data-end-time="<?php echo $item['end_time']; ?>">
                                        <?php echo format_datetime($item['end_time']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (strtotime($item['end_time']) > time()): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ended</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/public/edit_item.php?id=<?php echo $item['item_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>You haven't listed any items yet.
                <a href="<?php echo SITE_URL; ?>/public/create_item.php">List your first item</a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Your Bids -->
    <section>
        <h2 class="mb-4">
            <i class="fas fa-gavel me-2"></i>Your Bids
        </h2>

        <?php if ($bids_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Your Bid</th>
                            <th>Current Highest</th>
                            <th>End Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $bids_result->data_seek(0);
                        while ($bid = $bids_result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/public/item_details.php?id=<?php echo $bid['item_id']; ?>">
                                        <?php echo h($bid['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo format_currency($bid['bid_amount']); ?></td>
                                <td><?php echo format_currency($bid['current_highest_bid']); ?></td>
                                <td>
                                    <span class="countdown" data-end-time="<?php echo $bid['end_time']; ?>">
                                        <?php echo format_datetime($bid['end_time']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (strtotime($bid['end_time']) > time()): ?>
                                        <?php if ($bid['bid_amount'] == $bid['current_highest_bid']): ?>
                                            <span class="badge bg-success">Highest Bidder</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Outbid</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($bid['bid_amount'] == $bid['current_highest_bid']): ?>
                                            <span class="badge bg-primary">Won</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Lost</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>You haven't placed any bids yet.
                <a href="<?php echo SITE_URL; ?>/public/index.php">Browse items</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
$items_stmt->close();
$bids_stmt->close();
$mysqli->close();
require_once __DIR__ . '/../src/templates/footer.php';
?> 
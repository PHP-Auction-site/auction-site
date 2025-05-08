<?php
require_once __DIR__ . '/../src/includes/db_connect.php';
require_once __DIR__ . '/../src/includes/functions.php';
require_once __DIR__ . '/../src/templates/header.php';

$item_id = (int)($_GET['id'] ?? 0);
if ($item_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get item details
$mysqli = get_db_connection();
$stmt = $mysqli->prepare('
    SELECT i.*, u.username as seller_name, c.category_name,
           (SELECT COUNT(*) FROM bids WHERE item_id = i.item_id) as bid_count
    FROM items i
    JOIN users u ON i.user_id = u.user_id
    JOIN categories c ON i.category_id = c.category_id
    WHERE i.item_id = ?
');
$stmt->bind_param('i', $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    $_SESSION['flash_message'] = 'Item not found.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Get bid history
$stmt = $mysqli->prepare('
    SELECT b.*, u.username
    FROM bids b
    JOIN users u ON b.user_id = u.user_id
    WHERE b.item_id = ?
    ORDER BY b.bid_amount DESC
    LIMIT 10
');
$stmt->bind_param('i', $item_id);
$stmt->execute();
$bids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$mysqli->close();

// Check if user can bid (logged in and not their own item)
$can_bid = isset($_SESSION['user_id']) && $_SESSION['user_id'] != $item['user_id'];
$is_ended = $item['status'] === 'Ended';

// Format dates
$end_time = new DateTime($item['end_time'], new DateTimeZone('Africa/Addis_Ababa'));
$now = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
$time_remaining = $end_time > $now ? $end_time->diff($now) : null;
?>

<div class="container">
    <div class="row">
        <!-- Item Image and Details -->
        <div class="col-md-8">
            <
            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                 class="img-fluid auction-image mb-4">
            
            <h1><?php echo htmlspecialchars($item['title']); ?></h1>
            
            <div class="mb-4">
                <span class="badge bg-secondary"><?php echo htmlspecialchars($item['category_name']); ?></span>
                <span class="ms-2 text-muted">Listed by <?php echo htmlspecialchars($item['seller_name']); ?></span>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Bidding Section -->
        <div class="col-md-4">
            <div class="bid-section">
                <h3>Current Price</h3>
                <p class="price display-4 mb-3">$<?php echo number_format($item['current_price'], 2); ?></p>
                
                <?php if ($item['bid_count'] > 0): ?>
                    <p class="mb-3"><?php echo $item['bid_count']; ?> bid(s) placed</p>
                <?php endif; ?>

                <?php if ($is_ended): ?>
                    <div class="alert alert-info">
                        This auction has ended.
                        <?php if ($item['highest_bidder_id']): ?>
                            Winner: <?php echo htmlspecialchars($bids[0]['username']); ?>
                        <?php else: ?>
                            No bids were placed.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="countdown mb-3" data-end-time="<?php echo $end_time->format('c'); ?>">
                        <?php
                        if ($time_remaining) {
                            echo $time_remaining->format('%a days, %h hours, %i minutes');
                        } else {
                            echo 'Auction Ended';
                        }
                        ?>
                    </div>

                    <?php if ($can_bid): ?>
                        <form id="bid-form" action="place_bid.php" method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                            <div class="mb-3">
                                <label for="bid-amount" class="form-label">Your Bid ($)</label>
                                <input type="number" class="form-control" id="bid-amount" name="bid_amount"
                                       min="<?php echo $item['current_price'] + 1; ?>" step="0.01" required>
                                <div class="form-text">
                                    Minimum bid: $<?php echo number_format($item['current_price'] + 1, 2); ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Place Bid</button>
                        </form>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <div class="alert alert-warning">
                            Please <a href="login.php">login</a> to place a bid.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($bids)): ?>
                    <div class="mt-4">
                        <h5>Bid History</h5>
                        <div class="list-group">
                            <?php foreach ($bids as $bid): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($bid['username']); ?></span>
                                        <span>$<?php echo number_format($bid['bid_amount'], 2); ?></span>
                                    </div>
                                    <small class="text-muted">
                                        <?php
                                        $bid_time = new DateTime($bid['bid_time'], new DateTimeZone('Africa/Addis_Ababa'));
                                        echo $bid_time->format('M j, Y g:i A');
                                        ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?> 
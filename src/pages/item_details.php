<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../templates/header.php';

mark_ended_auctions();

$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
if (!$item_id) {
    echo '<p>Invalid item ID.</p>';
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}
$mysqli = get_db_connection();
// Fetch item details
$stmt = $mysqli->prepare('SELECT i.*, c.category_name, u.username AS seller_username FROM items i JOIN categories c ON i.category_id = c.category_id JOIN users u ON i.user_id = u.user_id WHERE i.item_id = ?');
$stmt->bind_param('i', $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$item) {
    echo '<p>Item not found.</p>';
    $mysqli->close();
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}
// Fetch highest bid and bidder
$bid_stmt = $mysqli->prepare('SELECT b.bid_amount, u.username FROM bids b JOIN users u ON b.user_id = u.user_id WHERE b.item_id = ? ORDER BY b.bid_amount DESC LIMIT 1');
$bid_stmt->bind_param('i', $item_id);
$bid_stmt->execute();
$bid_stmt->bind_result($highest_bid, $highest_bidder);
$has_bid = $bid_stmt->fetch();
$bid_stmt->close();
// Auction status
$is_active = ($item['status'] === 'Active') && (new DateTime($item['end_time'], new DateTimeZone('Africa/Addis_Ababa')) > new DateTime('now', new DateTimeZone('Africa/Addis_Ababa')));
?>
<h2><?php echo sanitize_output($item['title']); ?></h2>
<img src="<?php echo sanitize_output($item['image_path']); ?>" alt="Item image" style="max-width:300px;max-height:300px;"><br>
<p><strong>Description:</strong> <?php echo sanitize_output($item['description']); ?></p>
<p><strong>Category:</strong> <?php echo sanitize_output($item['category_name']); ?></p>
<p><strong>Seller:</strong> <?php echo sanitize_output($item['seller_username']); ?></p>
<p><strong>Starting Price:</strong> <?php echo format_price($item['starting_price']); ?></p>
<p><strong>Current Highest Bid:</strong> <?php echo $has_bid ? format_price($highest_bid) : 'No bids yet'; ?></p>
<p><strong>Current Highest Bidder:</strong> <?php echo $has_bid ? sanitize_output($highest_bidder) : 'N/A'; ?></p>
<p><strong>Auction Ends:</strong> <?php echo format_datetime($item['end_time']); ?></p>
<?php if (!$is_active): ?>
    <p style="color:red;"><strong>Auction Ended.</strong></p>
    <?php if ($has_bid): ?>
        <p><strong>Winner:</strong> <?php echo sanitize_output($highest_bidder); ?> (<?php echo format_price($highest_bid); ?>)</p>
    <?php else: ?>
        <p><strong>No winner (no bids placed).</strong></p>
    <?php endif; ?>
<?php else:
    session_start();
    if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] != $item['user_id']): ?>
    <h3>Place a Bid</h3>
    <?php if (!empty($_GET['error'])): ?>
        <div class="error"><?php echo sanitize_output($_GET['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['success'])): ?>
        <div class="success"><?php echo sanitize_output($_GET['success']); ?></div>
    <?php endif; ?>
    <form action="place_bid.php" method="post">
        <input type="hidden" name="item_id" value="<?php echo (int)$item['item_id']; ?>">
        <label for="bid_amount">Your Bid (min <?php echo format_price(max($item['starting_price'], $has_bid ? $highest_bid + 1 : $item['starting_price'])); ?>):</label>
        <input type="number" id="bid_amount" name="bid_amount" min="<?php echo max($item['starting_price'], $has_bid ? $highest_bid + 1 : $item['starting_price']); ?>" step="0.01" required>
        <button type="submit">Place Bid</button>
    </form>
    <?php elseif (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
        <p><em>You cannot bid on your own item.</em></p>
    <?php else: ?>
        <p><a href="/src/pages/login.php">Log in</a> to place a bid.</p>
    <?php endif; ?>
<?php endif; ?>
<?php
$mysqli->close();
require_once __DIR__ . '/../templates/footer.php'; 
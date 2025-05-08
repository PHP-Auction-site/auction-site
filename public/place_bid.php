<?php
require_once __DIR__ . '/../src/includes/db_connect.php';
require_once __DIR__ . '/../src/includes/functions.php';
require_once __DIR__ . '/../src/includes/session_check.php';

// Require login
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$item_id = (int)($_POST['item_id'] ?? 0);
$bid_amount = (float)($_POST['bid_amount'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($item_id <= 0 || $bid_amount <= 0) {
    header('Location: item_details.php?id=' . $item_id . '&error=' . urlencode('Invalid bid data'));
    exit;
}

$mysqli = get_db_connection();

// Get item details and check if user can bid
$stmt = $mysqli->prepare('
    SELECT i.*, u.username as seller_name 
    FROM items i 
    JOIN users u ON i.user_id = u.user_id 
    WHERE i.item_id = ? AND i.status = "Active"
');
$stmt->bind_param('i', $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    $mysqli->close();
    header('Location: index.php');
    exit;
}

// Check if user is trying to bid on their own item
if ($item['user_id'] == $user_id) {
    $mysqli->close();
    header('Location: item_details.php?id=' . $item_id . '&error=' . urlencode('You cannot bid on your own item'));
    exit;
}

// Check if bid amount is high enough
if ($bid_amount <= $item['current_price']) {
    $mysqli->close();
    header('Location: item_details.php?id=' . $item_id . '&error=' . urlencode('Bid must be higher than current price'));
    exit;
}

// Check if auction has ended
$end_time = new DateTime($item['end_time'], new DateTimeZone('Africa/Addis_Ababa'));
$now = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
if ($now > $end_time) {
    $mysqli->close();
    header('Location: item_details.php?id=' . $item_id . '&error=' . urlencode('This auction has ended'));
    exit;
}

// Start transaction
$mysqli->begin_transaction();

try {
    // Insert bid
    $stmt = $mysqli->prepare('INSERT INTO bids (item_id, user_id, bid_amount) VALUES (?, ?, ?)');
    $stmt->bind_param('iid', $item_id, $user_id, $bid_amount);
    $stmt->execute();

    // Update item's current price and highest bidder
    $stmt = $mysqli->prepare('UPDATE items SET current_price = ?, highest_bidder_id = ? WHERE item_id = ?');
    $stmt->bind_param('dii', $bid_amount, $user_id, $item_id);
    $stmt->execute();

    $mysqli->commit();
    
    $_SESSION['flash_message'] = 'Bid placed successfully!';
    $_SESSION['flash_type'] = 'success';
} catch (Exception $e) {
    $mysqli->rollback();
    header('Location: item_details.php?id=' . $item_id . '&error=' . urlencode('Failed to place bid. Please try again.'));
    exit;
}

$mysqli->close();
header('Location: item_details.php?id=' . $item_id);
exit; 
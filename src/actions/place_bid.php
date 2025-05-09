<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $item_id = (int)($_POST['item_id'] ?? 0);
    $bid_amount = floatval($_POST['bid_amount'] ?? 0);
    if (!$item_id || !$bid_amount) {
        header('Location: /src/pages/item_details.php?item_id=' . $item_id . '&error=Invalid+input');
        exit;
    }
    $mysqli = get_db_connection();
    // Fetch item
    $stmt = $mysqli->prepare('SELECT * FROM items WHERE item_id = ?');
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$item) {
        $mysqli->close();
        header('Location: /src/pages/home.php?error=Item+not+found');
        exit;
    }
    // Check auction status
    $now = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $end_time = new DateTime($item['end_time'], new DateTimeZone('Africa/Addis_Ababa'));
    if ($item['status'] !== 'Active' || $end_time <= $now) {
        $mysqli->close();
        header('Location: /src/pages/item_details.php?item_id=' . $item_id . '&error=Auction+has+ended');
        exit;
    }
    // Prevent self-bidding
    if ($item['user_id'] == $user_id) {
        $mysqli->close();
        header('Location: /src/pages/item_details.php?item_id=' . $item_id . '&error=You+cannot+bid+on+your+own+item');
        exit;
    }
    // Get current highest bid
    $stmt = $mysqli->prepare('SELECT MAX(bid_amount) FROM bids WHERE item_id = ?');
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    $stmt->bind_result($max_bid);
    $stmt->fetch();
    $stmt->close();
    $min_bid = $max_bid ? $max_bid + 1 : $item['starting_price'];
    if ($bid_amount < $min_bid) {
        $mysqli->close();
        header('Location: /src/pages/item_details.php?item_id=' . $item_id . '&error=Bid+must+be+at+least+' . $min_bid);
        exit;
    }
    // Insert bid
    $stmt = $mysqli->prepare('INSERT INTO bids (item_id, user_id, bid_amount, bid_time) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iid', $item_id, $user_id, $bid_amount);
    if ($stmt->execute()) {
        $stmt->close();
        // Update item's current price and highest bidder
        $stmt2 = $mysqli->prepare('UPDATE items SET current_price = ?, highest_bidder_id = ? WHERE item_id = ?');
        $stmt2->bind_param('dii', $bid_amount, $user_id, $item_id);
        $stmt2->execute();
        $stmt2->close();
        $mysqli->close();
        header('Location: /src/pages/item_details.php?item_id=' . $item_id . '&success=Bid+placed+successfully');
        exit;
    } else {
        $stmt->close();
        $mysqli->close();
        header('Location: /src/pages/item_details.php?item_id=' . $item_id . '&error=Failed+to+place+bid');
        exit;
    }
} else {
    header('Location: /src/pages/home.php');
    exit;
} 
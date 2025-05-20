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
        header("Location: /src/pages/item_details.php?item_id=$item_id&error=Invalid+input");
        exit;
    }

    try {
        $pdo = get_db_connection();

        // Fetch item
        $stmt = $pdo->prepare('SELECT * FROM items WHERE item_id = :item_id');
        $stmt->execute([':item_id' => $item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            header("Location: /src/pages/home.php?error=Item+not+found");
            exit;
        }

        // Check auction status
        $now = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
        $end_time = new DateTime($item['end_time'], new DateTimeZone('Africa/Addis_Ababa'));

        if ($item['status'] !== 'Active' || $end_time <= $now) {
            header("Location: /src/pages/item_details.php?item_id=$item_id&error=Auction+has+ended");
            exit;
        }

        // Prevent self-bidding
        if ((int)$item['user_id'] === $user_id) {
            header("Location: /src/pages/item_details.php?item_id=$item_id&error=You+cannot+bid+on+your+own+item");
            exit;
        }

        // Get current highest bid
        $stmt = $pdo->prepare('SELECT MAX(bid_amount) AS max_bid FROM bids WHERE item_id = :item_id');
        $stmt->execute([':item_id' => $item_id]);
        $max_bid = $stmt->fetchColumn();

        $min_bid = $max_bid ? $max_bid + 1 : $item['starting_price'];
        if ($bid_amount < $min_bid) {
            header("Location: /src/pages/item_details.php?item_id=$item_id&error=Bid+must+be+at+least+" . $min_bid);
            exit;
        }

        // Insert bid
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO bids (item_id, user_id, bid_amount, bid_time) VALUES (:item_id, :user_id, :bid_amount, NOW())');
        $stmt->execute([
            ':item_id' => $item_id,
            ':user_id' => $user_id,
            ':bid_amount' => $bid_amount,
        ]);

        // Update item's current price and highest bidder
        $stmt = $pdo->prepare('UPDATE items SET current_price = :bid_amount, highest_bidder_id = :user_id WHERE item_id = :item_id');
        $stmt->execute([
            ':bid_amount' => $bid_amount,
            ':user_id' => $user_id,
            ':item_id' => $item_id,
        ]);

        $pdo->commit();

        header("Location: /src/pages/item_details.php?item_id=$item_id&success=Bid+placed+successfully");
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Optionally log the error
        // error_log("Bid Error: " . $e->getMessage());
        header("Location: /src/pages/item_details.php?item_id=$item_id&error=Failed+to+place+bid");
        exit;
    }
} else {
    header('Location: /src/pages/home.php');
    exit;
}

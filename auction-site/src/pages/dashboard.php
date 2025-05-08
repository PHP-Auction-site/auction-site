<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../templates/header.php';
require_login();
$user_id = $_SESSION['user_id'];
$mysqli = get_db_connection();

mark_ended_auctions();

// Fetch items the user is selling
$stmt = $mysqli->prepare('SELECT * FROM items WHERE user_id = ? ORDER BY end_time DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$selling_result = $stmt->get_result();
$stmt->close();

// Fetch items the user has bid on (distinct items)
$stmt = $mysqli->prepare('SELECT DISTINCT i.* FROM items i JOIN bids b ON i.item_id = b.item_id WHERE b.user_id = ? ORDER BY i.end_time DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$bidding_result = $stmt->get_result();
$stmt->close();

?>
<h2>User Dashboard</h2>
<p>Welcome, <?php echo sanitize_output($_SESSION['username']); ?>!</p>

<h3>Your Listed Items</h3>
<?php if ($selling_result->num_rows > 0): ?>
    <?php while ($item = $selling_result->fetch_assoc()): ?>
        <?php include __DIR__ . '/../templates/item_card.php'; ?>
    <?php endwhile; ?>
<?php else: ?>
    <p>You have not listed any items yet.</p>
<?php endif; ?>

<h3>Your Bids</h3>
<?php if ($bidding_result->num_rows > 0): ?>
    <?php while ($item = $bidding_result->fetch_assoc()): ?>
        <?php include __DIR__ . '/../templates/item_card.php'; ?>
    <?php endwhile; ?>
<?php else: ?>
    <p>You have not placed any bids yet.</p>
<?php endif; ?>

<?php
$mysqli->close();
require_once __DIR__ . '/../templates/footer.php'; 
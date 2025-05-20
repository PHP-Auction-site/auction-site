<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../templates/header.php';

require_login();
$user_id = $_SESSION['user_id'];

$pdo = get_db_connection();

mark_ended_auctions();

// Fetch items the user is selling
$selling_stmt = $pdo->prepare('SELECT * FROM items WHERE user_id = :user_id ORDER BY end_time DESC');
$selling_stmt->execute(['user_id' => $user_id]);
$selling_items = $selling_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch items the user has bid on (distinct items)
$bidding_stmt = $pdo->prepare('
    SELECT DISTINCT i.* 
    FROM items i 
    JOIN bids b ON i.item_id = b.item_id 
    WHERE b.user_id = :user_id 
    ORDER BY i.end_time DESC
');
$bidding_stmt->execute(['user_id' => $user_id]);
$bidding_items = $bidding_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>User Dashboard</h2>
<p>Welcome, <?php echo sanitize_output($_SESSION['username']); ?>!</p>

<h3>Your Listed Items</h3>
<?php if (count($selling_items) > 0): ?>
    <?php foreach ($selling_items as $item): ?>
        <?php include __DIR__ . '/../templates/item_card.php'; ?>
    <?php endforeach; ?>
<?php else: ?>
    <p>You have not listed any items yet.</p>
<?php endif; ?>

<h3>Your Bids</h3>
<?php if (count($bidding_items) > 0): ?>
    <?php foreach ($bidding_items as $item): ?>
        <?php include __DIR__ . '/../templates/item_card.php'; ?>
    <?php endforeach; ?>
<?php else: ?>
    <p>You have not placed any bids yet.</p>
<?php endif; ?>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>

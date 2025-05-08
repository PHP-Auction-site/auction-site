<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config.php';

// Create a fresh database connection specifically for the footer
$footer_db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($footer_db->connect_error) {
    // Silently handle connection error - don't break the page
    $categories = [];
} else {
    $footer_db->set_charset('utf8mb4');
    
    // Get categories for footer
    $categories = [];
    $result = $footer_db->query('SELECT c.category_id, c.category_name, COUNT(i.item_id) as item_count 
                                FROM categories c 
                                LEFT JOIN items i ON c.category_id = i.category_id 
                                GROUP BY c.category_id, c.category_name 
                                ORDER BY c.category_name');
    if ($result) {
        while ($category = $result->fetch_assoc()) {
            $categories[] = $category;
        }
        $result->close();
    }
}
?>

<footer class="bg-dark text-light mt-5 py-4">
    <div class="container">
        <div class="row">
            <!-- Site Info -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">About Auction Site</h5>
                <p>Your trusted platform for online auctions. Find unique items and great deals!</p>
                <div class="social-links">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo SITE_URL; ?>/public/index.php" class="text-light"><i class="fas fa-home me-2"></i>Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo SITE_URL; ?>/public/dashboard.php" class="text-light"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/public/create_item.php" class="text-light"><i class="fas fa-plus me-2"></i>List Item</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/public/login.php" class="text-light"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/public/register.php" class="text-light"><i class="fas fa-user-plus me-2"></i>Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Categories -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Categories</h5>
                <ul class="list-unstyled">
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/public/index.php?category=<?php echo h($category['category_id']); ?>" class="text-light">
                                <?php echo h($category['category_name']); ?> 
                                <span class="badge bg-primary"><?php echo h($category['item_count']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <hr class="my-4">

        <!-- Copyright -->
        <div class="text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Auction Site. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo SITE_URL; ?>/public/js/main.js"></script>

<?php
// Close the footer's database connection
if (isset($footer_db) && $footer_db instanceof mysqli) {
    $footer_db->close();
}
?> 
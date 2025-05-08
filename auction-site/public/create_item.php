<?php
require_once __DIR__ . '/../src/includes/db_connect.php';
require_once __DIR__ . '/../src/includes/functions.php';
require_once __DIR__ . '/../src/includes/session_check.php';
require_once __DIR__ . '/../src/templates/header.php';

// Require login
require_login();

// Get categories for dropdown
$mysqli = get_db_connection();
$categories = [];
$result = $mysqli->query('SELECT * FROM categories ORDER BY category_name');
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$mysqli->close();
?>

<div class="form-container">
    <h2 class="mb-4">List New Item for Auction</h2>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <form action="/auction_site/public/actions/create_item.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="title" class="form-label required">Item Title</label>
            <input type="text" class="form-control" id="title" name="title" required maxlength="255">
            <div class="invalid-feedback">
                Please enter an item title.
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label required">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
            <div class="invalid-feedback">
                Please enter an item description.
            </div>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label required">Category</label>
            <select class="form-select" id="category" name="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">
                Please select a category.
            </div>
        </div>

        <div class="mb-3">
            <label for="starting_price" class="form-label required">Starting Price ($)</label>
            <input type="number" class="form-control" id="starting_price" name="starting_price" 
                   min="0.01" step="0.01" required>
            <div class="invalid-feedback">
                Please enter a valid starting price.
            </div>
        </div>

        <div class="mb-3">
            <label for="end_time" class="form-label required">Auction End Time (GMT+3)</label>
            <?php
            $min_end_time = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
            $min_end_time->modify('+1 hour');
            $max_end_time = clone $min_end_time;
            $max_end_time->modify('+30 days');
            ?>
            <input type="datetime-local" class="form-control" id="end_time" name="end_time"
                   min="<?php echo $min_end_time->format('Y-m-d\TH:i'); ?>"
                   max="<?php echo $max_end_time->format('Y-m-d\TH:i'); ?>"
                   required>
            <div class="invalid-feedback">
                Please select a valid end time (between 1 hour and 30 days from now).
            </div>
        </div>

        <div class="mb-3">
            <label for="item-image" class="form-label required">Item Image</label>
            <input type="file" class="form-control" id="item-image" name="image" 
                   accept="image/jpeg,image/png,image/gif" required>
            <div class="invalid-feedback">
                Please select an image file (JPEG, PNG, or GIF, max 2MB).
            </div>
            <div class="form-text">
                Maximum file size: 2MB. Allowed formats: JPEG, PNG, GIF
            </div>
            <img id="image-preview" class="mt-2" style="max-width: 200px; display: none;">
        </div>

        <button type="submit" class="btn btn-primary">Create Auction</button>
    </form>
</div>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?> 
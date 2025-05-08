<?php
// Template for displaying an auction item card
function render_item_card($item) {
    $item_link = "/public/item_details.php?id=" . $item['item_id'];
    $image_path = htmlspecialchars($item['image_path']);
    $title = htmlspecialchars($item['title']);
    $current_price = number_format($item['current_price'], 2);
    $end_time = new DateTime($item['end_time'], new DateTimeZone('Africa/Addis_Ababa'));
?>
    <div class="col-md-4 mb-4">
        <div class="card item-card h-100">
            <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo $title; ?>">
            <div class="card-body">
                <h5 class="card-title"><?php echo $title; ?></h5>
                <p class="price mb-2">Current Bid: $<?php echo $current_price; ?></p>
                <div class="countdown mb-2" data-end-time="<?php echo $end_time->format('c'); ?>">
                    Loading...
                </div>
                <a href="<?php echo $item_link; ?>" class="btn btn-primary">View Details</a>
            </div>
        </div>
    </div>
<?php
}
?> 
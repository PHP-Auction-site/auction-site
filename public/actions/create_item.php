<?php
require_once __DIR__ . '/../../src/includes/session_check.php';
require_once __DIR__ . '/../../src/includes/db_connect.php';
require_once __DIR__ . '/../../src/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $starting_price = floatval($_POST['starting_price'] ?? 0);
    $end_time = $_POST['end_time'] ?? '';

    // Validate required fields
    if (!$title || !$description || !$category_id || !$starting_price || !$end_time) {
        header('Location: /auction_site/public/create_item.php?error=All+fields+are+required');
        exit;
    }
    if ($starting_price < 0.01) {
        header('Location: /auction_site/public/create_item.php?error=Invalid+starting+price');
        exit;
    }
    // Validate end time (must be in the future)
    $end_dt = DateTime::createFromFormat('Y-m-d\TH:i', $end_time, new DateTimeZone('Africa/Addis_Ababa'));
    if (!$end_dt || $end_dt <= new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'))) {
        header('Location: /auction_site/public/create_item.php?error=End+time+must+be+in+the+future');
        exit;
    }
    // Validate image
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        header('Location: /auction_site/public/create_item.php?error=Image+upload+failed');
        exit;
    }
    $image = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($image['type'], $allowed_types)) {
        header('Location: /auction_site/public/create_item.php?error=Invalid+image+type');
        exit;
    }
    if ($image['size'] > 2 * 1024 * 1024) {
        header('Location: /auction_site/public/create_item.php?error=Image+file+too+large');
        exit;
    }
    // Secure file upload
    $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
    $safe_name = uniqid('item_', true) . '.' . $ext;
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $upload_path = $upload_dir . $safe_name;
    if (!move_uploaded_file($image['tmp_name'], $upload_path)) {
        header('Location: /auction_site/public/create_item.php?error=Failed+to+save+image');
        exit;
    }
    $image_path = '/auction_site/public/uploads/' . $safe_name;

    // Insert item into DB
    $mysqli = get_db_connection();
    $stmt = $mysqli->prepare('INSERT INTO items (user_id, category_id, title, description, starting_price, current_price, start_time, end_time, image_path, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, "Active")');
    $current_price = $starting_price;
    $end_time_mysql = $end_dt->format('Y-m-d H:i:s');
    $stmt->bind_param('iissddss', $user_id, $category_id, $title, $description, $starting_price, $current_price, $end_time_mysql, $image_path);
    if ($stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        header('Location: /auction_site/public/create_item.php?success=Item+listed+successfully');
        exit;
    } else {
        $stmt->close();
        $mysqli->close();
        header('Location: /auction_site/public/create_item.php?error=Failed+to+list+item');
        exit;
    }
} else {
    header('Location: /auction_site/public/create_item.php');
    exit;
} 
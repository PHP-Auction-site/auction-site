<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
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
        header('Location: /public/create_item.php?error=All+fields+are+required');
        exit;
    }

    if ($starting_price < 0.01) {
        header('Location: /public/create_item.php?error=Invalid+starting+price');
        exit;
    }

    // Validate end time (must be in the future)
    $end_dt = DateTime::createFromFormat('Y-m-d\TH:i', $end_time, new DateTimeZone('Africa/Addis_Ababa'));
    if (!$end_dt || $end_dt <= new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'))) {
        header('Location: /public/create_item.php?error=End+time+must+be+in+the+future');
        exit;
    }

    // Validate image
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        header('Location: /public/create_item.php?error=Image+upload+failed');
        exit;
    }

    $image = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($image['type'], $allowed_types)) {
        header('Location: /public/create_item.php?error=Invalid+image+type');
        exit;
    }

    if ($image['size'] > 2 * 1024 * 1024) {
        header('Location: /public/create_item.php?error=Image+file+too+large');
        exit;
    }

    // Secure file upload
    $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
    $safe_name = uniqid('item_', true) . '.' . $ext;
    $upload_dir = __DIR__ . '/../../public/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $upload_path = $upload_dir . $safe_name;
    if (!move_uploaded_file($image['tmp_name'], $upload_path)) {
        header('Location: /public/create_item.php?error=Failed+to+save+image');
        exit;
    }

    $image_path = '/public/uploads/' . $safe_name;

    // Insert item into DB using PDO
    try {
        $pdo = get_db_connection();
        $sql = 'INSERT INTO items (
                    user_id, category_id, title, description,
                    starting_price, current_price, start_time,
                    end_time, image_path, status
                ) VALUES (
                    :user_id, :category_id, :title, :description,
                    :starting_price, :current_price, NOW(),
                    :end_time, :image_path, "Active"
                )';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':category_id' => $category_id,
            ':title' => $title,
            ':description' => $description,
            ':starting_price' => $starting_price,
            ':current_price' => $starting_price,
            ':end_time' => $end_dt->format('Y-m-d H:i:s'),
            ':image_path' => $image_path,
        ]);

        header('Location: /public/create_item.php?success=Item+listed+successfully');
        exit;
    } catch (PDOException $e) {
        // Optionally log error: error_log($e->getMessage());
        header('Location: /public/create_item.php?error=Failed+to+list+item');
        exit;
    }
} else {
    header('Location: /public/create_item.php');
    exit;
}

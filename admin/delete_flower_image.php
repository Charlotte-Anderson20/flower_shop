<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
$image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';

if ($image_id <= 0 || empty($image_url)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Delete from database first
if ($con->query("DELETE FROM Flower_Images WHERE image_id = $image_id")) {
    // Delete image file if it exists
    $image_path = "../uploads/flowers/" . $image_url;
    if (file_exists($image_path)) {
        unlink($image_path);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $con->error]);
}
?>
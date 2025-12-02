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

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$field = isset($_POST['field']) ? trim($_POST['field']) : '';

if ($product_id <= 0 || !in_array($field, ['product_image', 'product_image2', 'product_image3'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Get current image filename
$result = $con->query("SELECT $field FROM Product WHERE product_id = $product_id");
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

$product = $result->fetch_assoc();
$filename = $product[$field];

// Update database to remove the image reference
if ($con->query("UPDATE Product SET $field = NULL WHERE product_id = $product_id")) {
    // Delete the image file if it exists
    if (!empty($filename)) {
        $image_path = "../uploads/products/" . $filename;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $con->error]);
}
?>
<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: manage_products.php");
    exit();
}

// First check if this product is referenced in any orders
$check = $con->query("SELECT COUNT(*) as count FROM Orders_Item WHERE product_id = $product_id");
$count = $check->fetch_assoc()['count'];

if ($count > 0) {
    $_SESSION['message'] = displayAlert('Cannot delete this product as it is referenced in existing orders.', 'danger');
    header("Location: manage_products.php");
    exit();
}

// Get images to delete
$image_fields = ['product_image', 'product_image2', 'product_image3'];
$images_to_delete = [];

$product_result = $con->query("SELECT " . implode(', ', $image_fields) . " FROM Product WHERE product_id = $product_id");
if ($product_result->num_rows > 0) {
    $product_data = $product_result->fetch_assoc();
    foreach ($image_fields as $field) {
        if (!empty($product_data[$field])) {
            $images_to_delete[] = $product_data[$field];
        }
    }
}

// Delete from database first
if ($con->query("DELETE FROM Product WHERE product_id = $product_id")) {
    // Delete associated flower types and occasions
    $con->query("DELETE FROM Product_Flower_Type WHERE product_id = $product_id");
    $con->query("DELETE FROM Product_Occasions WHERE product_id = $product_id");
    
    // Delete from wishlist
    $con->query("DELETE FROM wishlist WHERE product_id = $product_id");
    
    // Delete images from server
    foreach ($images_to_delete as $filename) {
        $image_path = "../uploads/products/" . $filename;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $_SESSION['message'] = displayAlert('Product deleted successfully!', 'success');
} else {
    $_SESSION['message'] = displayAlert('Error deleting product: ' . $con->error, 'danger');
}

header("Location: manage_products.php");
exit();
?>
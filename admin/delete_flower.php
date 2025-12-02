<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get flower ID from URL
$flower_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($flower_id <= 0) {
    header("Location: manage_flowers.php");
    exit();
}

// First check if this flower is referenced in any products
$check = $con->query("SELECT COUNT(*) as count FROM Product_Flower_Type WHERE flower_type_id = $flower_id");
$count = $check->fetch_assoc()['count'];

if ($count > 0) {
    $_SESSION['message'] = displayAlert('Cannot delete this flower type as it is used in products.', 'danger');
    header("Location: manage_flowers.php");
    exit();
}

// Get flower data to delete image
$flower_result = $con->query("SELECT image_url FROM flower_type WHERE flower_type_id = $flower_id");
$flower = $flower_result->fetch_assoc();

// Delete from database first
if ($con->query("DELETE FROM flower_type WHERE flower_type_id = $flower_id")) {
    // Delete main image if it exists
    if (!empty($flower['image_url']) && file_exists("../uploads/flowers/" . $flower['image_url'])) {
        unlink("../uploads/flowers/" . $flower['image_url']);
    }
    
    $_SESSION['message'] = displayAlert('Flower type deleted successfully!', 'success');
} else {
    $_SESSION['message'] = displayAlert('Error deleting flower type: ' . $con->error, 'danger');
}

header("Location: manage_flowers.php");
exit();
?>
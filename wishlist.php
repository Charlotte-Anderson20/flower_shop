<?php
include 'includes/db.php';

// Check if product is in wishlist
function isInWishlist($product_id, $customer_id) {
    global $con;
    $query = "SELECT * FROM wishlist WHERE product_id = ? AND customer_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $customer_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

// Toggle wishlist status
function toggleWishlist($product_id, $customer_id) {
    global $con;
    
    if (isInWishlist($product_id, $customer_id)) {
        // Remove from wishlist
        $query = "DELETE FROM wishlist WHERE product_id = ? AND customer_id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ii", $product_id, $customer_id);
        $result = mysqli_stmt_execute($stmt);
        return ['status' => 'removed', 'success' => $result];
    } else {
        // Add to wishlist
        $query = "INSERT INTO wishlist (product_id, customer_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ii", $product_id, $customer_id);
        $result = mysqli_stmt_execute($stmt);
        return ['status' => 'added', 'success' => $result];
    }
}

// Get all wishlist items for a customer
function getWishlistItems($customer_id) {
    global $con;
    $query = "SELECT p.*, pi.image_url 
              FROM wishlist w
              JOIN product p ON w.product_id = p.product_id
              LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
              WHERE w.customer_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

?>
<?php
session_start();
require '../includes/db.php'; // adjust path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_id'])) {
    $wishlist_id = intval($_POST['wishlist_id']);
    $customer_id = $_SESSION['customer_id'] ?? 0;

    if ($wishlist_id > 0 && $customer_id > 0) {
        // Delete only the item that belongs to this user
        $stmt = $con->prepare("DELETE FROM wishlist WHERE wishlist_id = ? AND customer_id = ?");
        $stmt->bind_param("ii", $wishlist_id, $customer_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to wishlist page
header("Location: dashboard.php");
exit;

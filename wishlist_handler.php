<?php
require_once 'includes/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage your wishlist']);
    exit;
}

$userId = $_SESSION['customer_id'];
$productId = intval($_POST['product_id']);
$action = $_POST['action'] === 'add' ? 'add' : 'remove';

$response = ['success' => false];

try {
    if ($action === 'add') {
        // Check if already in wishlist
        $check = $con->prepare("SELECT * FROM wishlist WHERE customer_id = ? AND product_id = ?");
        $check->bind_param("ii", $userId, $productId);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows === 0) {
            $stmt = $con->prepare("INSERT INTO wishlist (customer_id, product_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $productId);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Added to wishlist';
            }
        } else {
            $response['success'] = true;
            $response['message'] = 'Already in wishlist';
        }
    } else {
        $stmt = $con->prepare("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Removed from wishlist';
        }
    }
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>

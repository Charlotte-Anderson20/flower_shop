<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to submit feedback']);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id']) || !isset($data['rating']) || !isset($data['feedback'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$customer_id = $_SESSION['customer_id'];
$product_id = intval($data['product_id']);
$rating = intval($data['rating']);
$feedback = trim($data['feedback']);

// Validate rating
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating value']);
    exit();
}

// Validate feedback length
if (strlen($feedback) < 10) {
    echo json_encode(['success' => false, 'message' => 'Feedback must be at least 10 characters long']);
    exit();
}

// Check if customer has purchased this product
$purchaseCheck = $con->prepare("
    SELECT oi.order_item_id 
    FROM orders_item oi
    JOIN `order` o ON oi.order_id = o.order_id
    WHERE o.customer_id = ? AND oi.product_id = ? AND o.order_status = 'Accepted'
    LIMIT 1
");

$purchaseCheck->bind_param("ii", $customer_id, $product_id);
$purchaseCheck->execute();
$purchaseResult = $purchaseCheck->get_result();

if ($purchaseResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You have not purchased this product']);
    exit();
}

// Check if feedback already exists
$feedbackCheck = $con->prepare("
    SELECT feedback_id FROM feedback 
    WHERE customer_id = ? AND product_id = ?
    LIMIT 1
");

$feedbackCheck->bind_param("ii", $customer_id, $product_id);
$feedbackCheck->execute();
$feedbackResult = $feedbackCheck->get_result();

if ($feedbackResult->num_rows > 0) {
    // Update existing feedback
    $updateQuery = $con->prepare("
        UPDATE feedback 
        SET feedback_rating = ?, feedback_text = ?, feedback_date = NOW()
        WHERE customer_id = ? AND product_id = ?
    ");
    
    $updateQuery->bind_param("isii", $rating, $feedback, $customer_id, $product_id);
    
    if ($updateQuery->execute()) {
        echo json_encode(['success' => true, 'message' => 'Feedback updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $con->error]);
    }
    
    $updateQuery->close();
} else {
    // Insert new feedback
    $insertQuery = $con->prepare("
        INSERT INTO feedback (customer_id, product_id, feedback_text, feedback_rating, feedback_date)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $insertQuery->bind_param("iisi", $customer_id, $product_id, $feedback, $rating);

    if ($insertQuery->execute()) {
        echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $con->error]);
    }

    $insertQuery->close();
}

$purchaseCheck->close();
$feedbackCheck->close();
$con->close();
?>
<?php
session_start();
header('Content-Type: application/json');
require_once 'includes/db.php'; // make sure this has $con connection

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'login_required', 'message' => 'Please login to add items to cart']);
    exit;
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['id'], $data['type'])) {
        throw new Exception('Invalid item data');
    }

    $item_id = (int)$data['id'];
    $item_type = ($data['type'] === 'accessory') ? 'accessory' : 'product';

    // Get item details from DB based on type
   if ($item_type === 'accessory') {
    $stmt = $con->prepare("SELECT aid AS id, name, price, image FROM accessories WHERE aid = ?");
    $stmt->bind_param("i", $item_id);
} else {
    // Get product info + first image from product_images
    $stmt = $con->prepare(
        "SELECT p.product_id AS id, p.product_name AS name, p.product_price AS price, pi.image_url AS image
         FROM product p
         LEFT JOIN product_images pi ON p.product_id = pi.product_id
         WHERE p.product_id = ?
         ORDER BY pi.date_uploaded ASC
         LIMIT 1"
    );
    $stmt->bind_param("i", $item_id);
}

    
    $stmt->execute();
    $result = $stmt->get_result();
    $itemData = $result->fetch_assoc();
    
    if (!$itemData) {
        throw new Exception('Item not found');
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = ['items' => [], 'count' => 0];
    }

    $itemKey = $item_type . '_' . $itemData['id'];

    // Check if item already in cart
    if (isset($_SESSION['cart']['items'][$itemKey])) {
        $_SESSION['cart']['items'][$itemKey]['qty'] += 1;
    } else {
        $_SESSION['cart']['items'][$itemKey] = [
            'id' => $itemData['id'],
            'name' => $itemData['name'],
            'price' => (float)$itemData['price'],
            'image' => $itemData['image'],
            'type' => $item_type,
            'qty' => 1
        ];
    }

    // Update cart count
    $_SESSION['cart']['count'] = array_reduce($_SESSION['cart']['items'], 
        function($sum, $item) {
            return $sum + $item['qty'];
        }, 0);

    echo json_encode([
        'status' => 'success',
        'cart_count' => $_SESSION['cart']['count'],
        'message' => ucfirst($item_type) . ' added to cart'
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
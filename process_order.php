<?php
session_start();
require_once 'includes/db.php';

// Redirect if not logged in or cart empty
if (!isset($_SESSION['customer_id']) || empty($_SESSION['cart']['items'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['customer_id'];
$cart_items = $_SESSION['cart']['items'];
$subtotal = $_SESSION['cart_subtotal'] ?? 0;
$discount = $_SESSION['cart_discount'] ?? 0;
$total_price = $_SESSION['cart_total'] ?? $subtotal;

$payment_method_id = $_POST['payment_method_id'] ?? null;
$customer_note     = trim($_POST['customer_note'] ?? null);

// ----------------- VALIDATION -----------------
// Validate payment method
if ($payment_method_id && !is_numeric($payment_method_id)) {
    die("Invalid payment method selected.");
}

// Validate note length (avoid overly long text)
if ($customer_note && strlen($customer_note) > 500) {
    die("Customer note too long.");
}

// Validate cart contents again (extra safety)
if (empty($cart_items) || $total_price <= 0) {
    die("Invalid order: cart is empty or total is invalid.");
}

// ----------------- FILE UPLOAD VALIDATION -----------------
$payment_img = null;
if (isset($_FILES['payment_img']) && $_FILES['payment_img']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['payment_img']['error'] !== UPLOAD_ERR_OK) {
        die("Error uploading file.");
    }

    // Allow only certain extensions
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    $max_size     = 5 * 1024 * 1024; // 5MB max

    $ext = strtolower(pathinfo($_FILES['payment_img']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) {
        die("Invalid file type. Allowed: " . implode(", ", $allowed_exts));
    }

    if ($_FILES['payment_img']['size'] > $max_size) {
        die("File too large. Max size is 5MB.");
    }

    $upload_dir = __DIR__ . "/uploads/payment_proofs/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Prevent filename collisions
    $file_name   = uniqid('proof_', true) . '.' . $ext;
    $target_path = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES['payment_img']['tmp_name'], $target_path)) {
        die("Failed to save uploaded file.");
    }

    $payment_img = $file_name;
}

// ----------------- ORDER INSERT -----------------
$stmt = $con->prepare("INSERT INTO `order` 
    (customer_id, total_amount, order_date, order_status, customer_note) 
    VALUES (?, ?, NOW(), 'Pending', ?)");
$stmt->bind_param("ids", $user_id, $total_price, $customer_note);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// ----------------- ORDER ITEMS INSERT -----------------
$stmt = $con->prepare("INSERT INTO orders_item 
    (order_id, product_id, aid, quantity, sub_price, item_type) 
    VALUES (?, ?, ?, ?, ?, ?)");

foreach ($cart_items as $item) {
    $item_type = $item['item_type'] ?? $item['type'] ?? 'unknown';
    $quantity  = intval($item['quantity'] ?? $item['qty'] ?? 1);
    $sub_price = $item['sub_price'] ?? (($item['price'] ?? 0) * $quantity);

    if ($quantity <= 0 || $sub_price < 0) {
        continue; // Skip invalid items
    }

    if ($item_type === 'product') {
        $product_id = $item['product_id'] ?? $item['id'] ?? null;
        $aid = null;
        if (!$product_id) continue;
    } elseif ($item_type === 'accessory') {
        $product_id = null;
        $aid = $item['aid'] ?? $item['id'] ?? null;
        if (!$aid) continue;
    } else {
        continue; // Unknown type
    }

    $stmt->bind_param("iiidss", $order_id, $product_id, $aid, $quantity, $sub_price, $item_type);
    $stmt->execute();
}
$stmt->close();

// ----------------- PAYMENT INSERT -----------------
if ($payment_method_id && $payment_img) {
    $stmt = $con->prepare("INSERT INTO payment 
        (order_id, payment_method_id, payment_img, payment_date) 
        VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $order_id, $payment_method_id, $payment_img);
    $stmt->execute();
    $stmt->close();
}

// Clear cart
unset($_SESSION['cart'], $_SESSION['cart_subtotal'], $_SESSION['cart_discount'], $_SESSION['cart_total'], $_SESSION['cart_gifts']);

include 'ordersuccess.php'; 
exit;
?>

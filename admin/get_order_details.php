<?php
require_once '../includes/db.php';

if (!isset($_GET['order_id'])) {
    echo "Invalid request.";
    exit;
}

$order_id = intval($_GET['order_id']);

// Get order details + customer + payment info
$stmt = $con->prepare("
    SELECT o.*, c.customer_name, c.customer_email, c.customer_phone, 
           p.payment_date, p.payment_img, pm.method_name, pm.holder_name, pm.ph_no
    FROM `order` o
    JOIN customer c ON o.customer_id = c.customer_id
    LEFT JOIN payment p ON o.order_id = p.order_id
    LEFT JOIN payment_method pm ON p.payment_method_id = pm.payment_method_id
    WHERE o.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = $result->fetch_assoc();

// Fetch order items with product name for non-accessory items
// For accessory items, product_id is NULL, so no join possible

$items_sql = "
    SELECT oi.*, 
        CASE 
            WHEN oi.item_type = 'accessory' THEN 'Accessory Item'
            ELSE p.product_name 
        END AS item_name
    FROM orders_item oi
    LEFT JOIN product p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
";
$stmt_items = $con->prepare($items_sql);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

?>

<style>
    .payment-image {
        max-width: 220px;
        max-height: 220px;
        width: auto;
        height: auto;
        display: block;
        margin: 10px auto;
        border: 1px solid #ddd;
        border-radius: 5px;
        object-fit: contain;
        background-color: #f8f9fa;
    }
    table.order-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    table.order-items th, table.order-items td {
        border: 1px solid #ccc;
        padding: 8px 12px;
        text-align: left;
    }
    table.order-items th {
        background-color: #f0f0f0;
    }
</style>

<div style="padding:15px;">
    <h4>Customer Information</h4>
    <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>

    <h4>Payment Information</h4>
    <?php if (!empty($order['payment_img'])): ?>
        <p><strong>Method:</strong> <?= htmlspecialchars($order['method_name']) ?> 
            (<?= htmlspecialchars($order['holder_name']) ?> - <?= htmlspecialchars($order['ph_no']) ?>)</p>
        <p><strong>Payment Date:</strong> <?= date('M d, Y h:i A', strtotime($order['payment_date'])) ?></p>
        <img src="../uploads/payment_proofs/<?= htmlspecialchars($order['payment_img']) ?>" 
             alt="Payment Screenshot" class="payment-image">
    <?php else: ?>
        <p>No payment image found.</p>
    <?php endif; ?>
</div>

<?php
include '../includes/db.php';
include 'side.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Fetch orders for this customer
$sql_orders = $con->prepare("
    SELECT o.order_id, o.order_date, o.order_status, o.total_amount, o.customer_note
    FROM `order` o
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
");
$sql_orders->bind_param("i", $customer_id);
$sql_orders->execute();
$result_orders = $sql_orders->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        :root {
            --primary-color: #ffb6c1;
            --primary-light: #ffdfea;
            --primary-dark: #ff8fab;
            --card-bg: #fff;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(255, 126, 185, 0.15);
            --light-text: #888;
            --dark-text: #555;
        }
        
        .orders-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            padding: 0 1rem;
            margin-top: 4rem;
        }
        
        .order-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 182, 193, 0.2);
            height: 240px;
        }
        
        @media (max-width: 768px) {
            .orders-container {
                grid-template-columns: 1fr;
                margin-top: 1rem;
            }
            
            .order-card {
                height: auto;
            }
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 126, 185, 0.2);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }
        
        .order-id {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.1rem;
        }
        
        .order-date {
            font-size: 0.85rem;
            color: var(--light-text);
            margin-top: 0.3rem;
        }
        
        .order-status {
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            background-color: var(--primary-dark);
            text-transform: capitalize;
            min-width: 90px;
            text-align: center;
        }
        
        .order-status.Pending {
            background-color: #f0ad4e;
        }
        
        .order-status.Accepted {
            background-color: #28a745;
        }
        
        .order-status.Cancelled {
            background-color: #dc3545;
        }
        
        .order-summary {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .item-preview {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .item-count {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-bottom: 0.5rem;
        }
        
        .item-images {
            display: flex;
            margin-right: 1rem;
        }
        
        .item-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: -10px;
            border: 2px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .item-image:first-child {
            z-index: 3;
        }
        
        .item-image:nth-child(2) {
            z-index: 2;
            transform: translateX(10px);
        }
        
        .item-image:nth-child(3) {
            z-index: 1;
            transform: translateX(20px);
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: auto;
        }
        
        .order-total {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-dark);
        }
        
        .view-details {
            color: var(--primary-dark);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }
        
        .view-details i {
            margin-left: 0.3rem;
            transition: transform 0.2s ease;
        }
        
        .view-details:hover i {
            transform: translateX(3px);
        }
        
        .customer-note {
            font-size: 0.85rem;
            color: var(--light-text);
            font-style: italic;
            margin-top: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            width: 80%;
            max-width: 700px;
            position: relative;
            animation: slideUp 0.4s ease;
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.5rem;
            color: var(--light-text);
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .close-modal:hover {
            color: var(--primary-dark);
        }
        
        .modal-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.5rem;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }
        
        .modal-subtitle {
            display: flex;
            justify-content: space-between;
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        .modal-body {
            margin-bottom: 2rem;
        }
        
        .order-items {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }
        
        .order-items th {
            text-align: left;
            padding: 0.75rem 0.5rem;
            border-bottom: 2px solid #eee;
            color: var(--dark-text);
            font-weight: 600;
        }
        
        .order-items td {
            padding: 1rem 0.5rem;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .order-item {
            display: flex;
            align-items: center;
        }
        
        .order-item-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 1rem;
            border: 1px solid #eee;
        }
        
        .order-item-name {
            font-weight: 500;
            color: var(--dark-text);
            margin-bottom: 0.25rem;
        }
        
        .order-item-type {
            font-size: 0.8rem;
            color: var(--light-text);
            background: #f5f5f5;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
        }
        
        .order-summary-section {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 1.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .summary-label {
            color: var(--light-text);
        }
        
        .summary-value {
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .summary-total {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-dark);
            border-top: 1px solid #eee;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }
        
        .customer-note-full {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #fff9f9;
            border-left: 3px solid var(--primary-dark);
            border-radius: 0 4px 4px 0;
        }
        
        .note-label {
            font-weight: 500;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                padding: 1.5rem;
                margin: 2rem auto;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-item-img {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    

    <?php if ($result_orders->num_rows === 0): ?>
        <p>You have no orders yet.</p>
    <?php else: ?>
        <div class="orders-container">
            <?php while ($order = $result_orders->fetch_assoc()): ?>
                <?php
                // Fetch order items with product/accessory details
                $stmt_items = $con->prepare("
                    SELECT 
                        oi.quantity, oi.sub_price, oi.item_type,
                        p.product_id, p.product_name, p.product_price,
                        pi.image_url AS product_image,
                        a.aid, a.name AS accessory_name, a.image AS accessory_image
                    FROM orders_item oi
                    LEFT JOIN product p ON oi.product_id = p.product_id AND oi.item_type = 'product'
                    LEFT JOIN product_images pi ON p.product_id = pi.product_id
                    LEFT JOIN accessories a ON oi.aid = a.aid AND oi.item_type = 'accessory'
                    WHERE oi.order_id = ?
                    LIMIT 3
                ");
                $stmt_items->bind_param("i", $order['order_id']);
                if (!$stmt_items->execute()) {
                    error_log("Error executing items query: " . $stmt_items->error);
                }
                $result_items = $stmt_items->get_result();
                $item_count = $result_items->num_rows;
                
                // Store items for modal
                $all_items = [];
                $stmt_all_items = $con->prepare("
                    SELECT 
                        oi.quantity, oi.sub_price, oi.item_type,
                        p.product_id, p.product_name, p.product_price,
                        pi.image_url AS product_image,
                        a.aid, a.name AS accessory_name, a.image AS accessory_image
                    FROM orders_item oi
                    LEFT JOIN product p ON oi.product_id = p.product_id AND oi.item_type = 'product'
                    LEFT JOIN product_images pi ON p.product_id = pi.product_id
                    LEFT JOIN accessories a ON oi.aid = a.aid AND oi.item_type = 'accessory'
                    WHERE oi.order_id = ?
                ");
                $stmt_all_items->bind_param("i", $order['order_id']);
                if (!$stmt_all_items->execute()) {
                    error_log("Error executing all items query: " . $stmt_all_items->error);
                }
                $result_all_items = $stmt_all_items->get_result();
                while ($item = $result_all_items->fetch_assoc()) {
                    $all_items[] = $item;
                }
                ?>
                
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?= htmlspecialchars($order['order_id']) ?></div>
                            <div class="order-date"><?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></div>
                        </div>
                        <div class="order-status <?= htmlspecialchars($order['order_status']) ?>">
                            <?= htmlspecialchars($order['order_status']) ?>
                        </div>
                    </div>
                    
                    <div class="order-summary">
                        <div>
                            <div class="item-count"><?= $item_count ?> item<?= $item_count !== 1 ? 's' : '' ?></div>
                            <div class="item-preview">
                                <div class="item-images">
                                    <?php 
                                    $displayed_items = 0;
                                    while ($displayed_items < 3 && ($item = $result_items->fetch_assoc())): 
                                        $displayed_items++;
                                        $image_src = '';
                                        $alt_text = '';

                                        if ($item['item_type'] === 'product' && !empty($item['product_name'])) {
                                            $image_src = !empty($item['product_image']) ? "../uploads/products/" . htmlspecialchars($item['product_image']) : 'https://via.placeholder.com/60';
                                            $alt_text = htmlspecialchars($item['product_name']);
                                        } elseif ($item['item_type'] === 'accessory' && !empty($item['accessory_name'])) {
                                            $image_src = !empty($item['accessory_image']) ? "../uploads/accessories/" . htmlspecialchars($item['accessory_image']) : 'https://via.placeholder.com/60';
                                            $alt_text = htmlspecialchars($item['accessory_name']);
                                        } else {
                                            $image_src = 'https://via.placeholder.com/60';
                                            $alt_text = 'Item image';
                                        }
                                        ?>
                                        <img src="<?= $image_src ?>" alt="<?= $alt_text ?>" class="item-image" />
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-footer">
                            <div class="order-total"><?= number_format($order['total_amount']) ?> ks</div>
                        </div>
                    </div>
                    
                    <?php if (!empty($order['customer_note'])): ?>
                        <div class="customer-note">Note: <?= htmlspecialchars($order['customer_note']) ?></div>
                    <?php endif; ?>
                    
                    <!-- Hidden div to store order data for modal -->
                    <div class="order-data" id="order-data-<?= $order['order_id'] ?>" 
                         data-order='<?= json_encode($order) ?>'
                         data-items='<?= json_encode($all_items) ?>'
                         style="display: none;"></div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title">Order #<span id="modal-order-id"></span></h2>
            <div class="modal-subtitle">
                <span id="modal-order-date"></span>
                <span class="order-status" id="modal-order-status"></span>
            </div>
        </div>
        
        <div class="modal-body">
            <h3>Order Items</h3>
            <table class="order-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="modal-order-items">
                    <!-- Items will be inserted here by JavaScript -->
                </tbody>
            </table>
            
            <div class="order-summary-section">
                <div class="summary-row">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value" id="modal-subtotal"></span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Shipping:</span>
                    <span class="summary-value">0.00ks</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span id="modal-total"></span>
                </div>
            </div>
            
            <div id="modal-customer-note" class="customer-note-full" style="display: none;">
                <span class="note-label">Customer Note:</span>
                <p id="modal-note-text"></p>
            </div>
        </div>
    </div>
</div>

<script>
    // Open modal with order details
    function openOrderModal(event, orderId) {
        event.preventDefault();
        
        console.log(`Attempting to open modal for order #${orderId}`);
        
        const orderDataElement = document.getElementById(`order-data-${orderId}`);
        if (!orderDataElement) {
            console.error(`Order data element not found for order ID: ${orderId}`);
            alert('Order data not found. Please try again.');
            return;
        }

        try {
            const order = JSON.parse(orderDataElement.getAttribute('data-order'));
            const items = JSON.parse(orderDataElement.getAttribute('data-items'));
            
            console.log('Order data:', order);
            console.log('Items data:', items);
            
            if (!order || !items) {
                throw new Error('Order data is incomplete');
            }
            
            // Populate modal with order data
            document.getElementById('modal-order-id').textContent = order.order_id;
            document.getElementById('modal-order-date').textContent = new Date(order.order_date).toLocaleString();
            
            const statusElement = document.getElementById('modal-order-status');
            statusElement.textContent = order.order_status;
            statusElement.className = `order-status ${order.order_status}`;
            
            // Populate order items
            const itemsContainer = document.getElementById('modal-order-items');
            itemsContainer.innerHTML = '';
            
            let subtotal = 0;
            
            items.forEach(item => {
                const row = document.createElement('tr');
                
                let itemName, itemImage, itemType, itemPrice;
                
                if (item.item_type === 'product') {
                    itemName = item.product_name || 'Unknown Product';
                    itemImage = item.product_image ? `../uploads/products/${item.product_image}` : 'https://via.placeholder.com/60';
                    itemType = 'Product';
                    itemPrice = item.product_price || item.sub_price || 0;
                } else {
                    itemName = item.accessory_name || 'Unknown Accessory';
                    itemImage = item.accessory_image ? `../uploads/accessories/${item.accessory_image}` : 'https://via.placeholder.com/60';
                    itemType = 'Accessory';
                    itemPrice = item.sub_price || 0;
                }
                
                const quantity = item.quantity || 1;
                const total = itemPrice * quantity;
                subtotal += total;
                
                row.innerHTML = `
                    <td>
                        <div class="order-item">
                            <img src="${itemImage}" alt="${itemName}" class="order-item-img" />
                            <div>
                                <div class="order-item-name">${itemName}</div>
                                <span class="order-item-type">${itemType}</span>
                            </div>
                        </div>
                    </td>
                    <td>${quantity}</td>
                    <td>${itemPrice.toFixed(2)}ks</td>
                    <td>${total.toFixed(2)}ks</td>
                `;
                
                itemsContainer.appendChild(row);
            });
            
            // Set subtotal and total
            document.getElementById('modal-subtotal').textContent = `${subtotal.toFixed(2)}ks`;
            document.getElementById('modal-total').textContent = `${order.total_amount.toFixed(2)}ks`;
            
            // Handle customer note
            const noteContainer = document.getElementById('modal-customer-note');
            if (order.customer_note) {
                document.getElementById('modal-note-text').textContent = order.customer_note;
                noteContainer.style.display = 'block';
            } else {
                noteContainer.style.display = 'none';
            }
            
            // Show modal
            document.getElementById('orderModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
        } catch (error) {
            console.error('Error processing order data:', error);
            alert('There was an error loading the order details. Please try again.');
        }
    }
    
    // Close modal
    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('orderModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>
</body>
</html>
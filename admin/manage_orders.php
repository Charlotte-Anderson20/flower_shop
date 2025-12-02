<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Update order status if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['order_status'];
    
    $stmt = $con->prepare("UPDATE `order` SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $success_message = "Order #$order_id status updated successfully to $new_status!";
    } else {
        $error_message = "Error updating order status: " . $con->error;
    }
}

// Fetch all orders with complete customer information
// Base query
$query = "SELECT o.*, 
                 c.customer_id, c.customer_name, c.customer_email, 
                 c.customer_phone, c.customer_address, c.customer_image,
                 c.customer_created_at
          FROM `order` o 
          JOIN customer c ON o.customer_id = c.customer_id 
          WHERE 1=1";

// Apply filters dynamically
if (!empty($_GET['status'])) {
    $status = $con->real_escape_string($_GET['status']);
    $query .= " AND o.order_status = '$status'";
}

if (!empty($_GET['date'])) {
    $date = $con->real_escape_string($_GET['date']);
    $query .= " AND DATE(o.order_date) = '$date'";
}

if (!empty($_GET['customer'])) {
    $customer = $con->real_escape_string($_GET['customer']);
    $query .= " AND c.customer_name LIKE '%$customer%'";
}

$query .= " ORDER BY o.order_date DESC";

$orders_result = $con->query($query);

// Initialize counter for displaying numbers instead of IDs
$order_counter = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Dashboard</title>
  <link rel="shortcut icon" href="../images/flowerb.png" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        :root {
            --primary: #4a6bdf;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
            --text: #212529;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--light-gray);
            color: var(--text);
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .content-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .content-header h2 {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 500;
        }
        
        .breadcrumb {
            padding: 0;
            margin: 0;
            list-style: none;
            display: flex;
            font-size: 14px;
        }
        
        .breadcrumb-item {
            color: var(--dark-gray);
        }
        
        .breadcrumb-item.active {
            color: var(--primary);
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            padding: 0 8px;
            color: var(--dark-gray);
        }
        
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .close {
            float: right;
            font-size: 20px;
            font-weight: bold;
            line-height: 1;
            color: inherit;
            opacity: 0.7;
            background: none;
            border: none;
            cursor: pointer;
        }
        
        .table-container {
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: white;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .table-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 13px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--medium-gray);
            color: var(--dark-gray);
        }
        
        .btn i {
            margin-right: 5px;
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px 15px;
            background-color: var(--light-gray);
            font-weight: 500;
            font-size: 14px;
            color: var(--dark-gray);
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid var(--medium-gray);
            vertical-align: top;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 12px;
            text-transform: uppercase;
        }
        
        .badge-warning {
            background-color: var(--warning);
            color: #000;
        }
        
        .badge-success {
            background-color: var(--success);
            color: white;
        }
        
        .badge-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .customer-info {
            display: flex;
            align-items: flex-start;
        }
        
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            background-color: var(--medium-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-gray);
        }
        
        .customer-details {
            flex: 1;
        }
        
        .customer-name {
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .customer-email {
            font-size: 13px;
            color: var(--dark-gray);
        }
        
        .order-items {
            font-size: 14px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .order-item:last-child {
            margin-bottom: 0;
        }
        
        .product-image {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            object-fit: cover;
            margin-right: 10px;
            background-color: var(--medium-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-gray);
        }
        
        .btn-group {
            display: flex;
            gap: 5px;
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 4px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            padding: 15px 20px;
            background-color: #8e6c88;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 14px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            background-color: var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

       .filter-container {
    background: white;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    overflow: hidden;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.filter-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 500;
    color: #495057;
}

.filter-content {
    padding: 20px;
}

.filter-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #495057;
}

.filter-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.active-filters-text {
    font-size: 13px;
    color: #28a745;
    margin-left: 10px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .filter-group {
        min-width: 100%;
    }
    
    .filter-actions {
        flex-direction: column;
        align-items: flex-start;
    }
}

    </style>
</head>
<body>
      <?php include 'admin_header.php'; ?>
<div class="dashboard-container">
    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h2>Order Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Orders</li>
                </ol>
            </nav>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
                <button type="button" class="close">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
                <button type="button" class="close">&times;</button>
            </div>
        <?php endif; ?>

        <div class="filter-container">
    <div class="filter-header">
        <h3><i class="fas fa-filter"></i> Filter Orders</h3>
        <button type="button" class="btn btn-outline btn-sm" id="toggleFilters">
            <i class="fas fa-chevron-down"></i> Toggle Filters
        </button>
    </div>
    
    <div class="filter-content" id="filterContent">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="statusFilter">Status:</label>
                    <select name="status" id="statusFilter" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php if(isset($_GET['status']) && $_GET['status']=="Pending") echo "selected"; ?>>Pending</option>
                        <option value="Accepted" <?php if(isset($_GET['status']) && $_GET['status']=="Accepted") echo "selected"; ?>>Accepted</option>
                        <option value="Rejected" <?php if(isset($_GET['status']) && $_GET['status']=="Rejected") echo "selected"; ?>>Rejected</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="dateFilter">Order Date:</label>
                    <input type="date" name="date" id="dateFilter" class="form-control" 
                           value="<?php echo $_GET['date'] ?? ''; ?>">
                </div>

                <div class="filter-group">
                    <label for="customerFilter">Customer Name:</label>
                    <input type="text" name="customer" id="customerFilter" class="form-control"
                           placeholder="Search customer..." value="<?php echo $_GET['customer'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="minAmountFilter">Min Amount (Ks):</label>
                    <input type="number" name="min_amount" id="minAmountFilter" class="form-control"
                           placeholder="Min amount" value="<?php echo $_GET['min_amount'] ?? ''; ?>" min="0">
                </div>
                
                <div class="filter-group">
                    <label for="maxAmountFilter">Max Amount (Ks):</label>
                    <input type="number" name="max_amount" id="maxAmountFilter" class="form-control"
                           placeholder="Max amount" value="<?php echo $_GET['max_amount'] ?? ''; ?>" min="0">
                </div>
                
                <div class="filter-group">
                    <label for="sortBy">Sort By:</label>
                    <select name="sort" id="sortBy" class="form-control">
                        <option value="newest" <?php if(isset($_GET['sort']) && $_GET['sort']=="newest") echo "selected"; ?>>Newest First</option>
                        <option value="oldest" <?php if(isset($_GET['sort']) && $_GET['sort']=="oldest") echo "selected"; ?>>Oldest First</option>
                        <option value="highest" <?php if(isset($_GET['sort']) && $_GET['sort']=="highest") echo "selected"; ?>>Highest Amount</option>
                        <option value="lowest" <?php if(isset($_GET['sort']) && $_GET['sort']=="lowest") echo "selected"; ?>>Lowest Amount</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                <a href="manage_orders.php" class="btn btn-outline"><i class="fas fa-undo"></i> Reset</a>
                <?php if(isset($_GET['status']) || isset($_GET['date']) || isset($_GET['customer']) || isset($_GET['min_amount']) || isset($_GET['max_amount']) || isset($_GET['sort'])): ?>
                <span class="active-filters-text">Active filters applied</span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
        
        <div class="table-container">
            <div class="table-header">
                <h3>All Orders</h3>
                <button class="btn btn-outline btn-sm" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            
            


            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $order_counter; ?></td>
                        <td>
                            <div class="customer-info">
                                <?php if (!empty($order['customer_image']) && file_exists("../" . $order['customer_image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($order['customer_image']); ?>" 
                                         class="customer-avatar" alt="Customer">
                                <?php else: ?>
                                    <div class="customer-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="customer-details">
                                    <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <div class="customer-email"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($order['order_status']) {
                                    case 'Pending': echo 'badge-warning'; break;
                                    case 'Accepted': echo 'badge-success'; break;
                                    case 'Rejected': echo 'badge-danger'; break;
                                    default: echo '';
                                }
                                ?>">
                                <?php echo $order['order_status']; ?>
                            </span>
                        </td>
                        <td><?php echo number_format($order['total_amount']); ?> Ks </td>
                        <td>
                            <div class="order-items">
                                <?php 
                            $items_query = $con->prepare("
    SELECT 
    oi.*, 
    p.product_name, 
    (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id LIMIT 1) AS product_image,
    a.name AS accessory_name,
    a.image AS accessory_image
FROM orders_item oi
LEFT JOIN product p ON oi.product_id = p.product_id
LEFT JOIN accessories a ON (oi.item_type = 'accessory' AND oi.aid = a.aid)
WHERE oi.order_id = ?

");
                                $items_query->bind_param("i", $order['order_id']);
                                $items_query->execute();
                                $items_result = $items_query->get_result();

                                if ($items_result->num_rows > 0) {
                                    while ($item = $items_result->fetch_assoc()) {
                                        echo '<div class="order-item">';
                                        
                                       if ($item['item_type'] === 'accessory') {
    $img_src = !empty($item['accessory_image']) ? "../uploads/accessories/" . htmlspecialchars($item['accessory_image']) : '';
    $name = htmlspecialchars($item['accessory_name'] ?? 'Accessory Item');
} else {
    $img_src = !empty($item['product_image']) ? "../uploads/products/" . htmlspecialchars($item['product_image']) : '';
    $name = htmlspecialchars($item['product_name'] ?? 'Product');
}

if ($img_src && file_exists($img_src)) {
    echo '<img src="' . $img_src . '" class="product-image" alt="Item Image">';
} else {
    echo '<div class="product-image"><i class="fas fa-image"></i></div>';
}

                                        echo '<div class="item-details">';
                                        echo '<strong>' . $name . '</strong> × ' . (int)$item['quantity'];
                                        echo '<br><small>Type: ' . htmlspecialchars($item['item_type']) . '</small>';
                                        echo '</div>';

                                        echo '</div>';
                                    }
                                }

                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                               <button class="btn btn-primary btn-sm" onclick="openStatusModal(<?php echo $order['order_id']; ?>, '<?php echo $order['order_status']; ?>')">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="openViewOrderModal(<?php echo $order['order_id']; ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>

                            </div>
                        </td>
                    </tr>
                    <?php 
                    $order_counter++;
                    endwhile; 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Status Update Modal Template -->
<div id="statusModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Order Status</h3>
            <button class="close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="order_id" id="modalOrderId">
                <div class="form-group">
                    <label>Current Status</label>
                    <div id="currentStatusBadge" class="badge"></div>
                </div>
                <div class="form-group">
                    <label for="order_status">Update Status To</label>
                    <select class="form-control" name="order_status" id="order_status" required>
                        <option value="Pending">Pending</option>
                        <option value="Accepted">Accepted</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" name="update_status" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Order Modal -->
<div id="viewOrderModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Order Details & Payment</h3>
            <button class="close" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body" id="viewOrderBody">
            <!-- Content loaded via AJAX -->
            <div style="text-align:center; padding:20px;">Loading...</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>


<script>
// Refresh button functionality
document.getElementById('refreshBtn').addEventListener('click', function() {
    window.location.reload();
});

function openStatusModal(orderId, currentStatus) {
    const modal = document.getElementById('statusModal');
    const badge = document.getElementById('currentStatusBadge');

    document.getElementById('modalOrderId').value = orderId;
    badge.textContent = currentStatus;

    let badgeClass = 'badge';
    switch (currentStatus) {
        case 'Pending':
            badgeClass += ' badge-warning';
            break;
        case 'Accepted':
            badgeClass += ' badge-success';
            break;
        case 'Rejected':
            badgeClass += ' badge-danger';
            break;
    }
    badge.className = badgeClass;
    document.getElementById('order_status').value = currentStatus;

    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('statusModal').style.display = 'none';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target === modal) {
        closeModal();
    }
});

function openViewOrderModal(orderId) {
    const modal = document.getElementById('viewOrderModal');
    const body = document.getElementById('viewOrderBody');

    modal.style.display = 'flex';
    body.innerHTML = '<div style="text-align:center; padding:20px;">Loading...</div>';

    fetch('get_order_details.php?order_id=' + orderId)
        .then(response => response.text())
        .then(data => {
            body.innerHTML = data;
        })
        .catch(err => {
            body.innerHTML = '<p style="color:red;">Error loading order details.</p>';
        });
}

function closeViewModal() {
    document.getElementById('viewOrderModal').style.display = 'none';
}

// Close modal when clicking outside of modal content
window.onclick = function(event) {
    const modal = document.getElementById("viewOrderModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
};

</script>
<script>
// Toggle filter visibility
document.getElementById('toggleFilters').addEventListener('click', function() {
    const filterContent = document.getElementById('filterContent');
    const icon = this.querySelector('i');
    
    if (filterContent.style.display === 'none') {
        filterContent.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
    } else {
        filterContent.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
    }
});

// Initialize filter state
document.addEventListener('DOMContentLoaded', function() {
    // Check if any filters are active
    const urlParams = new URLSearchParams(window.location.search);
    const hasFilters = urlParams.has('status') || urlParams.has('date') || 
                      urlParams.has('customer') || urlParams.has('min_amount') || 
                      urlParams.has('max_amount') || urlParams.has('sort');
    
    // Keep filters open if they're active
    if (hasFilters) {
        document.getElementById('filterContent').style.display = 'block';
        document.querySelector('#toggleFilters i').className = 'fas fa-chevron-up';
    }
});
</script>
</body>
</html>


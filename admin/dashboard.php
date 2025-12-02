<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "tinny_flower_shop");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
  <link rel="shortcut icon" href="../images/flowerb.png" />
    <style>

        
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        
        .dashboard-header {
            background-color: lightpink;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .admin-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .logout-btn {
            background-color: #b75d69;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        
        .sidebar {
            width: 250px;
            background-color: white;
            padding: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 0.8rem 1rem;
            color:rgb(8, 8, 8);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: lightpink;
            color: white;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .stat-card h3 {
            color:rgb(254, 15, 226);
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            font-size: 2rem;
            font-weight: 700;
            color: #3d3d3d;
            margin: 0;
        }
        
        .recent-orders {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f2f2f2;
            color: #8e6c88;
        }
        
        .status-pending {
            color: #FFA500;
            font-weight: 600;
        }
        
        .status-accepted {
            color: #4CAF50;
            font-weight: 600;
        }
        
        .status-rejected {
            color: #f44336;
            font-weight: 600;
        }

        /* === Responsive Dashboard Extra === */

/* Make dashboard flex wrap on small screens */
@media (max-width: 1024px) {
    .dashboard-container {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        box-shadow: none;
        order: 1; /* optional: move sidebar above content */
    }

    .main-content {
        padding: 1rem;
        order: 2;
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }
}

/* Mobile smaller screens */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stat-card {
        padding: 1rem;
    }

    /* Make table scrollable */
    table {
        display: block;
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
    }

    th, td {
        padding: 0.5rem 0.7rem;
    }

    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .admin-profile {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.3rem;
    }
}

/* Extra small screens */
@media (max-width: 480px) {
    .dashboard-header h2 {
        font-size: 1.2rem;
    }

    .logout-btn {
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
    }

    .sidebar-menu a {
        padding: 0.6rem 0.8rem;
        font-size: 0.9rem;
    }

    .stat-card p {
        font-size: 1.5rem;
    }
}


    </style>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
</head>
<body>
    
        
     <?php include 'admin_header.php'; ?>
    <div class="dashboard-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="stats-grid">
                <?php
                // Get total products
                $products_query = "SELECT COUNT(*) as total FROM Product";
                $products_result = mysqli_query($con, $products_query);
                $products = mysqli_fetch_assoc($products_result);
                
                // Get total orders
                $orders_query = "SELECT COUNT(*) as total FROM `Order`";
                $orders_result = mysqli_query($con, $orders_query);
                $orders = mysqli_fetch_assoc($orders_result);
                
                // Get total customers
                $customers_query = "SELECT COUNT(*) as total FROM Customer";
                $customers_result = mysqli_query($con, $customers_query);
                $customers = mysqli_fetch_assoc($customers_result);
                
                // Get revenue
               // Get revenue
$revenue_query = "SELECT SUM(total_amount) as total FROM `Order` WHERE order_status = 'Accepted'";
$revenue_result = mysqli_query($con, $revenue_query);

// Debugging - check if query worked
if (!$revenue_result) {
    die("Query failed: " . mysqli_error($con));
}

$revenue = mysqli_fetch_assoc($revenue_result);


                ?>
                
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <p><?php echo $products['total']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p><?php echo $orders['total']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Customers</h3>
                    <p><?php echo $customers['total']; ?></p>
                </div>
                
                <div class="stat-card">
    <h3>Total Revenue</h3>
    <p><?php echo isset($revenue['total']) ? number_format($revenue['total']) : '0.00'; ?> Ks </p>
</div>
            </div>
            
            <div class="recent-orders">
                <h3>Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Note</th> 
                        </tr>
                    </thead>
                    <tbody>
    <?php
    $recent_orders_query = "SELECT o.order_id, c.customer_name, o.order_date, o.total_amount, o.order_status, o.customer_note 
                            FROM `Order` o
                            JOIN Customer c ON o.customer_id = c.customer_id
                            ORDER BY o.order_date DESC LIMIT 5";
    $recent_orders_result = mysqli_query($con, $recent_orders_query);

    $counter = 1; // start numbering
    while ($order = mysqli_fetch_assoc($recent_orders_result)) {
        echo '<tr>
            <td>' . $counter++ . '</td> <!-- show row number -->
            <td>' . htmlspecialchars($order['customer_name']) . '</td>
            <td>' . date('M d, Y', strtotime($order['order_date'])) . '</td>
            <td>' . number_format($order['total_amount']) . ' Ks </td>
            <td><span class="status-' . strtolower($order['order_status']) . '">' . htmlspecialchars($order['order_status']) . '</span></td>
            <td>' . (!empty($order['customer_note']) ? htmlspecialchars($order['customer_note']) : '-') . '</td>
        </tr>';
    }
    ?>
</tbody>

                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($con); ?>
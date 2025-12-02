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

// Set default time period (current month)
$current_month = date('m');
$current_year = date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : $current_month;
$year = isset($_GET['year']) ? $_GET['year'] : $current_year;

$revenue_query = "
SELECT
    SUM(order_total) AS total_revenue,
    COUNT(order_id) AS total_orders,
    CASE 
        WHEN COUNT(order_id) > 0 THEN SUM(order_total)/COUNT(order_id) 
        ELSE 0 
    END AS avg_order_value
FROM (
    SELECT 
        o.order_id,
        o.total_amount + IFNULL(a.accessory_total, 0) AS order_total
    FROM `order` o
    LEFT JOIN (
        SELECT order_id, SUM(sub_price) AS accessory_total
        FROM orders_item
        WHERE item_type = 'accessory'
        GROUP BY order_id
    ) a ON o.order_id = a.order_id
    WHERE o.order_status = 'Accepted'
      AND MONTH(o.order_date) = $month
      AND YEAR(o.order_date) = $year
) AS subquery
";


$revenue_result = mysqli_query($con, $revenue_query);
if ($revenue_result && mysqli_num_rows($revenue_result) > 0) {
    $revenue_data = mysqli_fetch_assoc($revenue_result);
} else {
    $revenue_data = [
        'total_revenue' => 0,
        'total_orders' => 0,
        'avg_order_value' => 0
    ];
}


// Get best selling products
$best_sellers_query = "SELECT 
                        p.product_id,
                        p.product_name,
                        p.product_price,
                        SUM(oi.quantity) as total_quantity,
                        SUM(oi.quantity * p.product_price) as total_revenue
                      FROM orders_item oi
                      JOIN Product p ON oi.product_id = p.product_id
                      JOIN `Order` o ON oi.order_id = o.order_id
                      WHERE o.order_status = 'Accepted'
                      AND MONTH(o.order_date) = $month
                      AND YEAR(o.order_date) = $year
                      GROUP BY p.product_id
                      ORDER BY total_quantity DESC
                      LIMIT 5";
$best_sellers_result = mysqli_query($con, $best_sellers_query);

// Get best selling arrangements
$best_arrangements_query = "SELECT 
                            a.arrangement_id,
                            a.arrangement_name,
                            COUNT(oi.product_id) as total_sold
                          FROM orders_item oi
                          JOIN Product p ON oi.product_id = p.product_id
                          JOIN arrangement_type a ON p.arrangement_id = a.arrangement_id
                          JOIN `Order` o ON oi.order_id = o.order_id
                          WHERE o.order_status = 'Accepted'
                          AND MONTH(o.order_date) = $month
                          AND YEAR(o.order_date) = $year
                          GROUP BY a.arrangement_id
                          ORDER BY total_sold DESC
                          LIMIT 5";
$best_arrangements_result = mysqli_query($con, $best_arrangements_query);

 $best_accessories_query = "SELECT 
                            a.aid,
    a.name,
    a.image,
    a.category,
    a.price,
    SUM(oi.quantity) AS total_sold
FROM orders_item oi
JOIN accessories a ON oi.item_type = 'accessory' AND oi.sub_price = a.price
GROUP BY a.aid, a.name, a.image, a.category, a.price
ORDER BY total_sold DESC
LIMIT 4"; 
 $best_accessories_result = mysqli_query($con, $best_accessories_query);

// Get products with most feedback
$top_feedback_query = "SELECT 
                        p.product_id,
                        p.product_name,
                        COUNT(f.feedback_id) as feedback_count,
                        AVG(f.feedback_rating) as avg_rating
                      FROM Product p
                      JOIN feedback f ON p.product_id = f.product_id
                      GROUP BY p.product_id
                      ORDER BY feedback_count DESC, avg_rating DESC
                      LIMIT 5";
$top_feedback_result = mysqli_query($con, $top_feedback_query);

// Get monthly revenue for chart
$monthly_revenue_query = "SELECT 
                            MONTH(order_date) as month,
                            SUM(total_amount) as monthly_revenue
                          FROM `Order`
                          WHERE order_status = 'Accepted'
                          AND YEAR(order_date) = $year
                          GROUP BY MONTH(order_date)
                          ORDER BY MONTH(order_date)";
$monthly_revenue_result = mysqli_query($con, $monthly_revenue_query);

$monthly_revenue_data = [];
while ($row = mysqli_fetch_assoc($monthly_revenue_result)) {
    $monthly_revenue_data[$row['month']] = $row['monthly_revenue'];
}

// Fill in missing months with 0
for ($m = 1; $m <= 12; $m++) {
    if (!isset($monthly_revenue_data[$m])) {
        $monthly_revenue_data[$m] = 0;
    }
}
ksort($monthly_revenue_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
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
        
        .report-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
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
        
        .chart-container {
            height: 400px;
            margin-top: 2rem;
        }
        
        .filter-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }
        
        .filter-form select, .filter-form button {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .filter-form button {
            background-color: lightpink;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        .month-name {
            text-transform: capitalize;
        }

        /* Previous styles remain the same */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/chart.js" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
      <?php include 'admin_header.php'; ?>
    <div class="dashboard-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="main-content">
            <h2>Sales Reports</h2>
            
            <div class="filter-form">
                <form method="get" action="reports.php">
                    <select name="month">
                        <?php
                        $months = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        
                        foreach ($months as $num => $name) {
                            $selected = ($num == $month) ? 'selected' : '';
                            echo "<option value='$num' $selected>$name</option>";
                        }
                        ?>
                    </select>
                    
                    <select name="year">
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected = ($y == $year) ? 'selected' : '';
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>
                    
                    <button type="submit">Filter</button>
                </form>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <p><?php echo number_format($revenue_data['total_revenue']); ?>Ks</p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p><?php echo $revenue_data['total_orders'] ?? 0; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Avg. Order Value</h3>
                    <p><?php echo number_format($revenue_data['avg_order_value']); ?> Ks </p>
                </div>
            </div>
            
            <div class="report-section">
                <h3>Monthly Revenue - <?php echo $year; ?></h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <div class="two-columns">
                <div class="report-section">
                    <h3>Best Selling Products - <?php echo $months[(int)$month] . ' ' . $year; ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity Sold</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($best_sellers_result) > 0) {
                                while ($product = mysqli_fetch_assoc($best_sellers_result)) {
                                    echo '<tr>
                                        <td>' . $product['product_id'] . '</td>
                                        <td>' . $product['product_name'] . '</td>
                                        <td> Ks ' . number_format($product['product_price']) . '</td>
                                        <td>' . $product['total_quantity'] . '</td>
                                        <td> Ks ' . number_format($product['total_revenue']) . '</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5">No sales data for this period</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="report-section">
    <h3>Best Selling Accessories - <?php echo htmlspecialchars($months[(int)$month]) . ' ' . htmlspecialchars($year); ?></h3>
    
    <?php
    if (mysqli_num_rows($best_accessories_result) > 0) {
        echo '<table>
            <thead>
                <tr>
                    <th>Accessory ID</th>
                    <th>Accessory Name</th>
                    <th>Price</th>
                    <th>Quantity Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>';
        
        while ($accessory = mysqli_fetch_assoc($best_accessories_result)) {
            echo '<tr>
                <td>' . (int)$accessory['aid'] . '</td>
                <td>' . htmlspecialchars($accessory['name']) . '</td>
                <td> Ks ' . number_format((float)$accessory['price']) . '</td>
                <td>' . (int)$accessory['total_sold'] . '</td>
                <td> Ks ' . number_format((float)$accessory['price'] * (int)$accessory['total_sold']) . '</td>
            </tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="no-data">No accessories sold this period. Recent accessory sales may still be pending approval.</div>';
        
        if (isset($debug_result)) {
            echo "<h4>Debug Data</h4><pre>";
            mysqli_data_seek($debug_result, 0);
            while ($row = mysqli_fetch_assoc($debug_result)) {
                print_r($row);
            }
            echo "</pre>";
        }
    }
    ?>
</div>

                
                <div class="report-section">
                    <h3>Top Arrangements - <?php echo $months[(int)$month] . ' ' . $year; ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Arrangement ID</th>
                                <th>Arrangement Name</th>
                                <th>Total Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($best_arrangements_result) > 0) {
                                while ($arrangement = mysqli_fetch_assoc($best_arrangements_result)) {
                                    echo '<tr>
                                        <td>' . $arrangement['arrangement_id'] . '</td>
                                        <td>' . $arrangement['arrangement_name'] . '</td>
                                        <td>' . $arrangement['total_sold'] . '</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3">No arrangement data for this period</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="report-section">
                <h3>Products with Most Feedback</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Feedback Count</th>
                            <th>Average Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($top_feedback_result) > 0) {
                            while ($product = mysqli_fetch_assoc($top_feedback_result)) {
                                echo '<tr>
                                    <td>' . $product['product_id'] . '</td>
                                    <td>' . $product['product_name'] . '</td>
                                    <td>' . $product['feedback_count'] . '</td>
                                    <td>' . number_format($product['avg_rating'], 1) . ' ★</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4">No feedback data available</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Revenue ($)',
                    data: [
                        <?php echo $monthly_revenue_data[1] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[2] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[3] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[4] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[5] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[6] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[7] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[8] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[9] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[10] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[11] ?? 0; ?>,
                        <?php echo $monthly_revenue_data[12] ?? 0; ?>
                    ],
                    backgroundColor: 'rgba(255, 182, 193, 0.7)',
                    borderColor: 'rgba(255, 182, 193, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($con); ?>
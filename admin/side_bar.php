  <style>
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
  </style>
  

  <div class="dashboard-container">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="manage_products.php">Products</a></li>
                <li><a href="accessories.php">Accessories</a></li>
                <li><a href="manage_orders.php">Orders</a></li>
                <li><a href="manage_customers.php">Customers</a></li>
                <li><a href="manage_feedbacks.php">Feedbacks</a></li>
                <li><a href="manage_occasions.php">Occasions</a></li>
                <li><a href="add_flower.php">Flower Types</a></li>
                <li><a href="manage_arrangements.php">Arrangement Types</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>
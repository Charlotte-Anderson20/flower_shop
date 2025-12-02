<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id']; // ✅ Define customer_id
$totalOrders = 0;

$query = "SELECT COUNT(*) AS total_orders FROM `order` WHERE customer_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_orders = $row['total_orders'];


// Fetch wishlist products
$query = "
   SELECT w.wishlist_id, p.product_id, p.product_name, p.product_price, p.product_description,
       pi.image_url
FROM wishlist w
JOIN product p ON w.product_id = p.product_id
LEFT JOIN (
    SELECT product_id, MIN(image_url) AS image_url
    FROM product_images
    GROUP BY product_id
) pi ON p.product_id = pi.product_id
WHERE w.customer_id = ? AND p.is_active = 1

";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$wishlistItems = [];
while ($row = $result->fetch_assoc()) {
    $wishlistItems[] = $row;
}

  $stmt = $con->prepare("
        SELECT COUNT(*) AS total 
        FROM wishlist w 
        JOIN product p ON w.product_id = p.product_id 
        WHERE w.customer_id = ? AND p.is_active = 1
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $totalWishlist = $row['total'];
    }

      $stmt = $con->prepare("
        SELECT COUNT(*) AS total 
        FROM `order` 
        WHERE customer_id = ? AND order_status = 'Accepted'
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $totalOrders = $row['total'];
    }

    $showPopup = false;

if ($customer_id) {
    $query = "
        SELECT oi.product_id
        FROM `order` o
        JOIN orders_item oi ON o.order_id = oi.order_id
        LEFT JOIN feedback f ON f.customer_id = o.customer_id AND f.product_id = oi.product_id
        WHERE o.customer_id = ?
        AND o.order_status = 'Accepted'
        AND TIMESTAMPDIFF(MINUTE, o.order_date, NOW()) >= 1 -- ✅ 1 min for testing
        AND f.feedback_id IS NULL
        LIMIT 1
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $showPopup = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | Tiny Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
    --primary: #f7c9d8;         /* Softer pink */
    --primary-dark: #e6a5b8;    /* Muted rosy pink */
    --primary-light: #fde9ef;   /* Very light pink */
    --secondary: #333333;       /* Dark gray for contrast */
    --light: #f9f6f7;           /* Light background gray-pink */
    --dark: #1a1a1a;            /* Almost black */
    --accent: #f4aab9;          /* Soft medium pink */
    --text: #2c2c2c;            /* Deep charcoal for better readability */
    --dark-bg: #fdf8f9;         /* Very soft pinkish white */
    --darker-bg: #f6f1f2;       /* Slightly deeper pink-tinted background */
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}


* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Montserrat', sans-serif;
}

body {
    background-color: var(--light);
    color: var(--text);
    line-height: 1.7;
    overflow-x: hidden;
}

/* Dashboard Layout */
.dashboard-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 280px;
    background-color: var(--dark-bg);
    color: white;
    padding: 2rem 0;
    position: fixed;
    height: 100vh;
    transition: var(--transition);
    z-index: 100;
}

.sidebar-header {
    display: flex;
    align-items: center;
    padding: 0 2rem 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-logo {
    font-size: 1.5rem;
    font-weight: 600;
    color: black;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-logo i {
    color: var(--primary);
}

.sidebar-menu {
    padding: 2rem 0;
}

.menu-title {
    padding: 0 2rem 1rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(0, 0, 0, 0.5);
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 0.8rem 2rem;
    color: rgba(0, 0, 0, 0.8);
    text-decoration: none;
    transition: var(--transition);
    position: relative;
}

.menu-item:hover, .menu-item.active {
    color: var(--dark);
    background-color: var(--primary);
}

.menu-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background-color: var(--primary);
}

.menu-item i {
    margin-right: 12px;
    font-size: 1.1rem;
    width: 24px;
    text-align: center;
}

.menu-item:hover i {
    color: var(--dark);
}

/* Main Content */
.main-content {
    flex: 1;
    margin-left: 280px;
    background-color: var(--light);
    min-height: 100vh;
    transition: var(--transition);
}

/* Top Navigation */
.top-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    background-color: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 90;
}

.search-bar {
    position: relative;
    width: 300px;
}

.search-bar input {
    width: 100%;
    padding: 0.8rem 1.5rem 0.8rem 3rem;
    border-radius: 50px;
    border: 1px solid rgba(255, 105, 180, 0.2);
    font-size: 0.95rem;
    transition: var(--transition);
    background-color: var(--dark-bg);
    color: white;
}

.search-bar input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.3);
}

.search-bar i {
    position: absolute;
    left: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary);
    opacity: 0.8;
}

 .user-profile {
    position: relative;
    display: inline-block;
    --profile-primary: #5DADE2;
    --profile-text: #2C3E50;
    --profile-hover: #EBF5FB;
    transition: all 0.3s ease;
}

.profile-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    gap: 10px;
    padding: 6px 12px;
    border-radius: 24px;
    transition: all 0.2s ease;
}

.profile-btn:hover {
    background-color: var(--profile-hover);
}

.user-img-container {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--profile-primary);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.profile-btn:hover .user-img-container {
    transform: scale(1.05);
}

.user-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.user-name {
    font-weight: 600;
    color: var(--profile-text);
    font-size: 0.95rem;
    line-height: 1.2;
}

.user-role {
    font-size: 0.75rem;
    color: #7F8C8D;
    font-weight: 400;
}

/* Optional status indicator */
.user-status {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #2ECC71;
    border: 2px solid white;
}

/* Dropdown arrow */
.profile-btn::after {
    content: "▼";
    font-size: 0.6rem;
    margin-left: 4px;
    color: #7F8C8D;
    transition: transform 0.2s ease;
}

.profile-btn:hover::after {
    color: var(--profile-text);
}

/* Active state for when dropdown is open */
.profile-btn.active::after {
    transform: rotate(180deg);
}

.notification-btn {
    position: relative;
    color: var(--primary);
    font-size: 1.3rem;
    margin-right: 1.5rem;
}

.notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: var(--primary);
    color: var(--dark);
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    font-weight: bold;
}

/* Dashboard Content */
.dashboard-content {
    padding: 2rem;
}

.welcome-banner {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
    color: var(--dark);
    padding: 2.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.welcome-banner::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
}

.welcome-banner::after {
    content: '';
    position: absolute;
    bottom: -80px;
    right: -30px;
    width: 150px;
    height: 150px;
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
}

.welcome-banner h2 {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.welcome-banner p {
    opacity: 0.9;
    max-width: 600px;
    position: relative;
    z-index: 1;
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
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
    color: white;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.orders {
    background-color: rgba(255, 105, 180, 0.2);
}

.stat-icon.wishlist {
    background-color: rgba(255, 105, 180, 0.2);
}

.stat-icon.reviews {
    background-color: rgba(255, 105, 180, 0.2);
}

.stat-icon.points {
    background-color: rgba(255, 105, 180, 0.2);
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: black;
}

.stat-title {
    color: rgba(1, 1, 1, 0.8);
    font-size: 0.95rem;
}

/* Recent Orders */
.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-title h3 {
    font-size: 1.4rem;
    color: black;
}

.section-title a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.section-title a:hover {
    color: var(--primary-light);
}

.orders-table {
    width: 100%;
    background-color: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.orders-table table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background-color: var(--dark-bg);
    color: black;
    padding: 1rem 1.5rem;
    text-align: left;
    font-weight: 600;
}

.orders-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    color: black;
}

.orders-table tr:last-child td {
    border-bottom: none;
}

.order-status {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pending {
    background-color: rgba(255, 165, 0, 0.2);
    color: orange;
}

.status-completed {
    background-color: rgba(50, 205, 50, 0.2);
    color: limegreen;
}

.status-processing {
    background-color: rgba(30, 144, 255, 0.2);
    color: dodgerblue;
}

.status-cancelled {
    background-color: rgba(255, 99, 71, 0.2);
    color: tomato;
}

.action-btn {
    background: none;
    border: none;
    color: var(--primary);
    cursor: pointer;
    transition: var(--transition);
    padding: 0.3rem;
}

.action-btn:hover {
    color: var(--primary-light);
}

/* Wishlist */
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.wishlist-card {
    background-color: var(--secondary);
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
    position: relative;
}

.wishlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.wishlist-img {
    height: 180px;
    overflow: hidden;
}

.wishlist-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s ease;
}

.wishlist-card:hover .wishlist-img img {
    transform: scale(1.05);
}

.wishlist-info {
    padding: 1.5rem;
}

.wishlist-info h4 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: white;
}

.wishlist-price {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 1rem;
    display: block;
}

.wishlist-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.remove-wishlist {
    color: white;
    background-color: #e6a5b8;
    opacity: 0.6;
    cursor: pointer;
    transition: var(--transition);
}

.remove-wishlist:hover {
    color: var(--primary);
    opacity: 1;
}

/* Form elements */
.btn {
    background-color: var(--primary);
    color: var(--dark);
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: var(--transition);
}

.btn:hover {
    background-color: var(--primary-dark);
    color: var(--dark);
}

/* Responsive styles remain the same */
        
        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
                background: none;
                border: none;
                color: var(--text);
                font-size: 1.5rem;
                cursor: pointer;
                margin-right: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .search-bar {
                width: 200px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .welcome-banner h2 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .top-nav {
                padding: 1rem;
            }
            
            .search-bar {
                display: none;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-content {
                padding: 1.5rem 1rem;
            }
            
            .welcome-banner {
                padding: 1.5rem;
            }
            
            .welcome-banner h2 {
                font-size: 1.3rem;
            }
            
            .orders-table {
                overflow-x: auto;
                display: block;
            }
        }
        
        /* Floating flowers decoration */
        .floating-flowers {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: -1;
        }
        
        .floating-flower {
            position: absolute;
            opacity: 0.6;
            animation: float-up 15s linear infinite;
            z-index: 1;
            pointer-events: none;
            color: var(--primary);
        }
        
        @keyframes float-up {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        .mobile-menu-btn {
    display: none;
}
@media (max-width: 992px) {
    .mobile-menu-btn {
        display: block;
        background: none;
        border: none;
        color: var(--text);
        font-size: 1.5rem;
        cursor: pointer;
        margin-right: 1rem;
    }
}

/* Add this to your existing CSS, replacing the responsive section */

/* Responsive Breakpoints */
@media (max-width: 1600px) {
    /* Large desktop adjustments */
    .welcome-banner h2 {
        font-size: 1.7rem;
    }
    .welcome-banner p {
        max-width: 80%;
    }
}

@media (max-width: 1200px) {
    /* Desktop adjustments */
    .sidebar {
        width: 250px;
    }
    .main-content {
        margin-left: 250px;
    }
    .wishlist-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
    .stats-grid {
        gap: 1.2rem;
    }
}

@media (max-width: 992px) {
    /* Tablet landscape adjustments */
    .sidebar {
        transform: translateX(-100%);
        width: 280px;
    }
    .sidebar.active {
        transform: translateX(0);
    }
    .main-content {
        margin-left: 0;
    }
    .mobile-menu-btn {
        display: block;
    }
    .welcome-banner {
        padding: 2rem;
    }
    .welcome-banner h2 {
        font-size: 1.5rem;
    }
    .orders-table th, 
    .orders-table td {
        padding: 0.8rem 1rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
    /* Tablet portrait adjustments */
    .search-bar {
        width: 200px;
    }
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    .welcome-banner {
        padding: 1.8rem;
    }
    .welcome-banner h2 {
        font-size: 1.4rem;
    }
    .welcome-banner p {
        font-size: 0.95rem;
    }
    .stat-card {
        padding: 1.2rem;
    }
    .stat-value {
        font-size: 1.6rem;
    }
    .wishlist-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    .orders-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}

@media (max-width: 576px) {
    /* Mobile landscape adjustments */
    .top-nav {
        padding: 1rem;
    }
    .search-bar {
        display: none;
    }
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .dashboard-content {
        padding: 1.5rem 1rem;
    }
    .welcome-banner {
        padding: 1.5rem;
        text-align: center;
    }
    .welcome-banner h2 {
        font-size: 1.3rem;
    }
    .welcome-banner p {
        max-width: 100%;
        margin: 0 auto;
    }
    .welcome-banner::before,
    .welcome-banner::after {
        display: none;
    }
    .section-title h3 {
        font-size: 1.2rem;
    }
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
    .wishlist-img {
        height: 220px;
    }
    .user-profile .user-name {
        display: none;
    }
}

@media (max-width: 480px) {
    /* Mobile portrait adjustments */
    .stat-card {
        padding: 1rem;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    .stat-value {
        font-size: 1.5rem;
    }
    .orders-table th, 
    .orders-table td {
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
    }
    .order-status {
        padding: 0.2rem 0.6rem;
    }
    .mobile-menu-btn {
        margin-right: 0.5rem;
        font-size: 1.3rem;
    }
    .user-profile {
        padding: 4px;
    }
}

@media (max-width: 380px) {
    /* Small mobile adjustments */
    .sidebar {
        width: 260px;
    }
    .welcome-banner {
        padding: 1.2rem;
    }
    .stat-card {
        padding: 0.8rem;
    }
    .stat-value {
        font-size: 1.4rem;
    }
    .section-title h3 {
        font-size: 1.1rem;
    }
    .section-title a {
        font-size: 0.9rem;
    }
}

    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="../index.php" class="sidebar-logo">
                    <i class="fas fa-spa"></i>
                    <span>Tiny Flower</span>
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-title">Main</div>
                <a href="#" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my_orders.php" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Orders</span>
                </a>
                
               
                <a href="../index.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                
                <div class="menu-title">Account</div>
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>

                 <a href="feedback.php" class="menu-item">
                    <i class="fas fa-star"></i>
                    <span>feedback</span>
                </a>
               
                <a href="../logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>

                
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <div class="top-nav">
                
                  <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>
                <!-- <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search dashboard...">
                </div> -->
                
                <div class="user-actions">
                    <!-- <a href="#" class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </a> -->
                    
                    <div class="user-profile">
                        <div class="user-img">
                          <?php
$defaultImage = 'https://ui-avatars.com/api/?name=User&background=ffb6c1&color=fff';
$customerName = isset($_SESSION['customer_name']) ? htmlspecialchars($_SESSION['customer_name']) : 'User';

if (isset($_SESSION['customer_image']) && !empty($_SESSION['customer_image'])) {
    $webPath = '../' . $_SESSION['customer_image']; // relative to dashboard.php
    $serverPath = realpath(__DIR__ . '/../' . $_SESSION['customer_image']); // server path

    if ($serverPath && file_exists($serverPath)) {
        echo '<img src="' . $webPath . '" alt="' . $customerName . '" style="width: 50px; height: 50px; border-radius: 50%;">';
    } else {
        echo '<img src="' . $defaultImage . '" alt="' . $customerName . '" style="width: 50px; height: 50px; border-radius: 50%;">';
    }
} else {
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($customerName) . '&background=ffb6c1&color=fff';
    echo '<img src="' . $avatarUrl . '" alt="' . $customerName . '" style="width: 50px; height: 50px; border-radius: 50%;">';
}
?>


                        </div>
                        <div class="user-name"><?php echo $_SESSION['customer_name']; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h2>Welcome back, <?php echo explode(' ', $_SESSION['customer_name'])[0]; ?>!</h2>
                   <p>
    Hello again! 🌷 You’ve placed <strong><?php echo $totalOrders; ?></strong> 
    <?php echo $totalOrders == 1 ? 'flower order' : 'flower orders'; ?> with us so far. We’re happy to keep blooming with you!
</p>

                </div>
                
            <div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-value"><?= $total_orders ?></div>
                <div class="stat-title">Total Orders</div>
            </div>
            <div class="stat-icon orders">
                <i class="fas fa-shopping-bag"></i>
            </div>
        </div>
    </div>



                    
                    <div class="stat-card">
    <div class="stat-header">
        <div>
            <div class="stat-value"><?php echo $totalWishlist; ?></div>
            <div class="stat-title">Wishlist Items</div>
        </div>
        <div class="stat-icon wishlist">
            <i class="fas fa-heart"></i>
        </div>
    </div>
</div>

                    
                    <!-- <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">5</div>
                                <div class="stat-title">Your Reviews</div>
                            </div>
                            <div class="stat-icon reviews">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div> -->
                    
                    
                </div>
                
                <!-- Recent Orders -->
                <div class="section-title">
                    <h3>Recent Orders</h3>
                    <a href="my_orders.php">View All <i class="fas fa-chevron-right"></i></a>
                </div>
                
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
include '../includes/db.php';


// Get current customer ID

$query = "
    SELECT o.order_id, o.order_date, o.order_status, o.total_amount,
           GROUP_CONCAT(CONCAT(p.product_name, IF(oi.quantity > 1, CONCAT(' (x', oi.quantity, ')'), '')) SEPARATOR ', ') AS items
    FROM `order` o
    JOIN orders_item oi ON o.order_id = oi.order_id
    JOIN product p ON oi.product_id = p.product_id
    WHERE o.customer_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
    LIMIT 4
";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $formattedId = "#TF-" . str_pad($row['order_id'], 4, '0', STR_PAD_LEFT);
    $formattedDate = date("M d, Y", strtotime($row['order_date']));
    $statusClass = strtolower($row['order_status']); // "completed", "pending", etc.
    
    echo "<tr>
        <td>{$formattedId}</td>
        <td>{$formattedDate}</td>
        <td>{$row['items']}</td>
        <td>" . number_format($row['total_amount']) . "ks</td>
        <td><span class='order-status status-{$statusClass}'>{$row['order_status']}</span></td>
    </tr>";
}
?>

</tbody>

                    </table>
                </div>
                
                 <?php if ($showPopup): ?>
    <script>
        window.addEventListener('load', function () {
            setTimeout(() => {
                const popup = document.createElement('div');
                popup.innerHTML = `
                    <div style="
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        background: #fff0f6;
                        border: 1px solid #f5c2da;
                        padding: 16px;
                        border-radius: 12px;
                        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                        z-index: 9999;
                        max-width: 320px;
                        font-family: sans-serif;
                    ">
                        <p style="margin:0 0 8px;font-weight:bold;color:#d63384;">
                            💐 Enjoying your flowers?
                        </p>
                        <p style="margin:0 0 10px;color:#444;">We'd love your thoughts!</p>
                        <a href="feedback.php" style="
                            display: inline-block;
                            padding: 8px 12px;
                            background-color: #d63384;
                            color: white;
                            text-decoration: none;
                            border-radius: 8px;
                        ">Leave Feedback</a>
                        <button onclick="this.parentElement.remove()" style="
                            margin-left: 10px;
                            background: none;
                            border: none;
                            color: #999;
                            font-size: 14px;
                            cursor: pointer;
                        ">Dismiss</button>
                    </div>
                `;
                document.body.appendChild(popup);
            }, 1500); // Show after 1.5 seconds page load
        });
    </script>
    <?php endif; ?>
               
<!-- Wishlist UI -->
<div class="section-title">
    <h3>Your Wishlist</h3>
    <a href="my_wishlist.php">View All <i class="fas fa-chevron-right"></i></a>
</div>

<div class="wishlist-grid">
    <?php if (!empty($wishlistItems)): ?>
        <?php foreach ($wishlistItems as $item): ?>
            <div class="wishlist-card">
                <div class="wishlist-img">
                    <img src="<?= '../uploads/products/' . htmlspecialchars($item['image_url'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                </div>
                <div class="wishlist-info">
                    <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                    <span class="wishlist-price"><?= number_format($item['product_price']) ?> Ks </span>
                    <div class="wishlist-actions">
                        <form action="../shop.php" method="post" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <button class="btn btn-sm" type="submit">Buy</button>
                        </form>
                        <form action="remove_from_wishlist.php" method="post" style="display:inline;">
                            <input type="hidden" name="wishlist_id" value="<?= $item['wishlist_id'] ?>">
                            <button class="remove-wishlist" title="Remove" type="submit">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Your wishlist is empty.</p>
    <?php endif; ?>
</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Floating flowers decoration -->
    <div class="floating-flowers">
        <i class="floating-flower fas fa-leaf" style="left: 10%; animation-duration: 20s; animation-delay: 0s;"></i>
        <i class="floating-flower fas fa-spa" style="left: 30%; animation-duration: 25s; animation-delay: 5s;"></i>
        <i class="floating-flower fas fa-seedling" style="left: 70%; animation-duration: 18s; animation-delay: 2s;"></i>
        <i class="floating-flower fas fa-feather-alt" style="left: 50%; animation-duration: 22s; animation-delay: 7s;"></i>
        <i class="floating-flower fas fa-cloud" style="left: 85%; animation-duration: 30s; animation-delay: 10s;"></i>
    </div>
    
    <script>
        // Toggle mobile sidebar
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Create more floating flowers
        function createFlowers() {
            const container = document.querySelector('.floating-flowers');
            const flowerIcons = ['fa-leaf', 'fa-spa', 'fa-seedling', 'fa-feather-alt', 'fa-cloud'];
            
            for (let i = 0; i < 5; i++) {
                const flower = document.createElement('i');
                flower.className = `floating-flower fas ${flowerIcons[Math.floor(Math.random() * flowerIcons.length)]}`;
                
                // Random position
                const left = Math.random() * 100;
                
                // Random animation duration
                const duration = 15 + Math.random() * 20;
                
                // Random delay
                const delay = Math.random() * 15;
                
                flower.style.cssText = `
                    left: ${left}%;
                    animation-duration: ${duration}s;
                    animation-delay: ${delay}s;
                `;
                
                container.appendChild(flower);
            }
        }
        
        // Call the function when page loads
        window.addEventListener('load', createFlowers);
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
 <?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
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
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--primary);
}

.user-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-name {
    font-weight: 500;
    color: black;
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

  .menu-item.active {
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
                <a href="dashboard.php" class="menu-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my_orders.php" class="menu-item <?php echo ($current_page == 'my_orders.php') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Orders</span>
                </a>
                
                <a href="../index.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>

                
                
                <div class="menu-title">Account</div>
                <a href="profile.php" class="menu-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
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

            <script>

                document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        const itemHref = item.getAttribute('href');
        if (itemHref && itemHref.includes(currentPage)) {
            item.classList.add('active');
        }
    });
});
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
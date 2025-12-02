<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accessories - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_styles.css">
<div class="sidebar">
     <div class="sidebar-close" onclick="toggleSidebar()">
        <i class="fas fa-times"></i>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a></li>
        <li><a href="manage_products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_products.php' ? 'active' : ''; ?>">
    <i class="fas fa-boxes"></i> Products
</a></li>

<li><a href="add_product.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_product.php' ? 'active' : ''; ?>">
    <i class="fas fa-plus-square"></i> Add Products
</a></li>

<li><a href="accessories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'accessories.php' ? 'active' : ''; ?>">
    <i class="fas fa-gem"></i> Accessories
</a></li>

<li><a href="add_flower.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_flower.php' ? 'active' : ''; ?>">
    <i class="fas fa-seedling"></i> Add Flower
</a></li>

        <li><a href="manage_flowers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_flowers.php' ? 'active' : ''; ?>">
            <i class="fas fa-spa"></i> Flower Types
        </a></li>
        <li><a href="manage_arrangements.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_arrangements.php' ? 'active' : ''; ?>">
            <i class="fas fa-ribbon"></i> Arrangement Types
        </a></li>
        <li><a href="manage_occasions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_occasions.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Occasions
        </a></li>
        <li><a href="manage_orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Orders
        </a></li>
      <li><a href="reports.php"> <i class="fas fa-info-circle"></i>Reports</a></li>
        <li><a href="manage_promos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about_us.php' ? 'active' : ''; ?>">
            <i class="fas fa-info-circle"></i> Discount & Gifts
        </a></li>
    </ul>
</div>

<style>
    /* Close button inside sidebar */
.sidebar-close {
    display: none; /* hidden on desktop */
    text-align: right;
    padding: 15px 20px;
    font-size: 24px;
    cursor: pointer;
    color: pink; /* adjust to your sidebar text color */
}

@media (max-width: 768px) {
    .sidebar-close {
        display: block; /* show on mobile */
    }
}

</style>

<script>
    function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
}

</script>
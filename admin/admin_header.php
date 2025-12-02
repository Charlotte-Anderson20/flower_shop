<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accessories - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_styles.css">

<div class="dashboard-header">
    <div class="mobile-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</div>

    <h2>Admin Dashboard</h2>
    <div class="admin-profile">
        <img src="<?php echo $_SESSION['admin_image']; ?>" alt="Admin Profile">
        <span><?php echo $_SESSION['admin_name']; ?></span>
        <form action="admin_logout.php" method="post">
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>

<style>
    /* Mobile Toggle Button */
.mobile-toggle {
    display: none; /* hidden on desktop */
    font-size: 24px;
    cursor: pointer;
}

/* Sidebar behavior on mobile */
@media (max-width: 768px) {
    .mobile-toggle {
        display: block; /* show button on mobile */
    }
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 220px;        /* your sidebar width */
        height: 100%;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        z-index: 1000;
    }
    .sidebar.active {
        transform: translateX(0);
    }
}

</style>

<script>
    function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
}

</script>
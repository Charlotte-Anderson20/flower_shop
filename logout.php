<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];

    // Clear token from DB
    mysqli_query($con, "UPDATE customer SET remember_token = NULL, remember_expiry = NULL WHERE customer_id = $customer_id");
}

// Destroy session
session_unset();
session_destroy();

// Delete the remember_token cookie
setcookie('remember_token', '', time() - 3600, '/');

// Redirect to home or login page
header('Location: index.php');
exit();

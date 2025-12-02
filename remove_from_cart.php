<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key'])) {
    $key = $_POST['key'];
    
    if (isset($_SESSION['cart']['items'][$key])) {
        // Remove item
        unset($_SESSION['cart']['items'][$key]);
        
        // Update cart count
        $_SESSION['cart']['count'] = array_reduce(
            $_SESSION['cart']['items'], 
            function($sum, $item) { return $sum + $item['qty']; }, 
            0
        );
    }
    
    // Redirect to cart after processing
    header("Location: cart.php");
    exit;
} else {
    header("Location: cart.php");
    exit;
}
?>

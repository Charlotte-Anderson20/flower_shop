<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key'], $_POST['qty'])) {
    $key = $_POST['key'];
    $new_qty = (int)$_POST['qty'];
    
    if (isset($_SESSION['cart']['items'][$key])) {
        // Update quantity
        $_SESSION['cart']['items'][$key]['qty'] = $new_qty;
        
        // Update cart count
        $_SESSION['cart']['count'] = array_reduce(
            $_SESSION['cart']['items'], 
            function($sum, $item) { return $sum + $item['qty']; }, 
            0
        );
        
        // Redirect to cart without showing message
        header("Location: cart.php");
        exit;
    } else {
        header("Location: cart.php");
        exit;
    }
} else {
    header("Location: cart.php");
    exit;
}
?>

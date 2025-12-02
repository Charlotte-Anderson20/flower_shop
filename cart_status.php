<?php
session_start();

// Initialize cart with the new structure if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = ['items' => [], 'count' => 0];
}

// Count unique items (not total qty)
$totalItems = 0;

if (!empty($_SESSION['cart']['items'])) {
    $totalItems = count(array_filter($_SESSION['cart']['items'], function($item) {
        return $item['qty'] > 0; // only count items with qty > 0
    }));
}

// Save in session
$_SESSION['cart']['count'] = $totalItems;

// Output (don’t show 0 if empty)
if ($totalItems > 0) {
    echo $totalItems;
}
?>
<?php
session_start();

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo "<p>Your cart is empty.</p>";
    exit;
}

echo "<ul style='list-style: none; padding: 0;'>";
foreach ($cart as $item) {
    $name = htmlspecialchars($item['name'] ?? 'Unknown');
    $qty = htmlspecialchars($item['qty'] ?? '0');
    $img = htmlspecialchars($item['image'] ?? 'placeholder.jpg');

    echo "<li style='margin-bottom: 10px'>
            <img src='uploads/products/$img' width='40' style='vertical-align:middle;margin-right:10px;'>
            $name (x$qty)
          </li>";
}
echo "</ul>";

<?php
session_start();
require_once 'includes/db.php';

// Check for order success or error messages
$order_success = isset($_SESSION['order_success']) && $_SESSION['order_success'];
$order_error = $_SESSION['order_error'] ?? null;
$order_id = $_SESSION['order_id'] ?? null;

// Clear the messages after displaying
unset($_SESSION['order_success']);
unset($_SESSION['order_error']);
unset($_SESSION['order_id']);

// Initialize cart (using the new structure with 'items' and 'count')
$cart = $_SESSION['cart']['items'] ?? [];

// Count unique items (ignore qty = 0)
$cart_count = 0;
if (!empty($cart)) {
    $cart_count = count(array_filter($cart, function($item) {
        return $item['qty'] > 0;
    }));
}

// Save in session so badge/header can also use it
$_SESSION['cart']['count'] = $cart_count;


// Initialize variables
$subtotal = 0;
$discount = 0;
$total = 0;
$gifts = [];
$showPromo = false;
$discount_percent = 0;

// Calculate subtotal
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

// Fetch the most relevant active promo
$promo_sql = "SELECT * FROM admin_promos 
              WHERE status = 'active' AND min_order_amount <= ? 
              ORDER BY discount_percent DESC, min_order_amount DESC LIMIT 1";
$promo_stmt = $con->prepare($promo_sql);
$promo_stmt->bind_param("d", $subtotal);
$promo_stmt->execute();
$promo_result = $promo_stmt->get_result();

if ($promo = $promo_result->fetch_assoc()) {
    if ($promo['discount_percent'] > 0) {
        $discount_percent = $promo['discount_percent'];
        $discount = $subtotal * ($discount_percent / 100);
    }
    
    if (!empty($promo['gift_description'])) {
        $gifts[] = $promo['gift_description'];
    }
    
    $showPromo = true;
    $_SESSION['cart_promo_title'] = $promo['title'];
} else {
    $_SESSION['cart_promo_title'] = null;
}

// Calculate total
$total = $subtotal - $discount;

// Save to session for checkout
$_SESSION['cart_subtotal'] = $subtotal;
$_SESSION['cart_discount'] = $discount;
$_SESSION['cart_total'] = $total;
$_SESSION['cart_gifts'] = $gifts;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart | Tin Flower</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #fff9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        h2 {
            color: #d88a97;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(214, 112, 147, 0.1);
        }
        .cart-table th {
            background: #f8e1e6;
            padding: 15px;
            text-align: left;
        }
        .cart-table td {
            padding: 12px;
            border-bottom: 1px solid #f0d8dc;
        }
        .cart-summary {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(214, 112, 147, 0.1);
            margin-bottom: 2rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0d8dc;
        }
        .summary-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #d88a97;
        }
        .btn {
            background: #ff69b4;
            color: white;
            border: none;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .btn:hover {
            background: #e55fa0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .gift-item {
            margin-top: 0.3rem;
            color: #e91e63;
        }
        .empty-cart {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(214, 112, 147, 0.1);
        }
        .empty-cart i {
            font-size: 50px;
            color: #ffb6c1;
            margin-bottom: 10px;
        }
        .promo-notification {
            margin-top: 20px; 
            background: #ffe3ee; 
            padding: 1rem; 
            border-radius: 8px; 
            border-left: 5px solid #ff69b4;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
            vertical-align: middle;
        }
        input[type="number"] {
            padding: 8px;
            border: 1px solid #f0d8dc;
            border-radius: 4px;
            width: 60px;
        }

        /* Success Message Styles */
        .success-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        
        .success-box {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideUp 0.5s ease;
            position: relative;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #4CAF50;
            margin-bottom: 1rem;
            animation: bounce 1s;
        }
        
        .success-title {
            font-size: 1.8rem;
            color: #2E7D32;
            margin-bottom: 1rem;
        }
        
        .success-message {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .success-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
        }
        
        .success-btn:hover {
            background: #388E3C;
            transform: translateY(-2px);
        }
        
        .success-btn.secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .success-btn.secondary:hover {
            background: #e9ecef;
        }

        /* Error Message Styles */
        .error-container {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ffebee;
            padding: 1rem;
            border-radius: 8px;
            border-left: 5px solid #f44336;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
            animation: slideIn 0.5s ease;
            max-width: 400px;
        }
        
        .error-icon {
            color: #f44336;
            margin-right: 10px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(50px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-30px);}
            60% {transform: translateY(-15px);}
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .cart-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 2rem 1.5rem;
    background: linear-gradient(135deg, #f9f3f3 0%, #f2e6ff 100%);
    border-radius: 0 0 15px 15px;
    margin-bottom: 3rem;
    height: 45vh;
}

.hero-content h1 {
    color: #5a287d;
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.hero-content p {
    color: #7c4d8f;
    font-size: 1.1rem;
    margin: 0;
}

.hero-image {
    width: 150px;
    height: 150px;
}

.hero-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 5px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .cart-hero {
        flex-direction: column;
        text-align: center;
        padding: 1.5rem 1rem;
    }
    
    .hero-content h1 {
        font-size: 1.8rem;
    }
    
    .hero-image {
        margin-top: 1rem;
        width: 120px;
        height: 120px;
    }
}
    </style>
</head>
<body>
    <?php include 'header.php' ?>
    <section class="cart-hero">
    <div class="hero-content">
        <h1>Your Flower Journey</h1>
        <p>Review and complete your beautiful selection</p>
    </div>
    <div class="hero-image">
        <img src="images/flowerb.png" alt="Beautiful flower arrangement">
    </div>
</section>
    <?php if ($order_success): ?>
    <div class="success-container" id="successContainer">
        <div class="success-box">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="success-title">Order Confirmed!</h2>
            <p class="success-message">
                Thank you for your purchase at Tinny Flower!<br>
                Your order <strong>#<?= $order_id ?></strong> has been successfully placed.<br>
                Your flowers will be blooming their way to you soon!
            </p>
            <div>
                <a href="shop.php" class="success-btn">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="user/dashboard.php" class="success-btn secondary">
                    <i class="fas fa-history"></i> View Orders
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-close after 3 minutes (180,000 milliseconds) if not clicked
        setTimeout(() => {
            document.getElementById('successContainer').style.opacity = '0';
            setTimeout(() => {
                document.getElementById('successContainer').style.display = 'none';
            }, 500);
        }, 180000);
        
        // Close when clicking outside
        document.getElementById('successContainer').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.opacity = '0';
                setTimeout(() => {
                    this.style.display = 'none';
                }, 500);
            }
        });
    </script>
<?php endif; ?>

    <?php if ($order_error): ?>
    <div class="error-container" id="errorContainer">
        <div>
            <i class="fas fa-exclamation-circle error-icon"></i>
            <strong>Order Error:</strong> <?= htmlspecialchars($order_error) ?>
        </div>
    </div>
    
    <script>
        // Auto-close error after 5 seconds
        setTimeout(() => {
            document.getElementById('errorContainer').style.opacity = '0';
            setTimeout(() => {
                document.getElementById('errorContainer').style.display = 'none';
            }, 500);
        }, 5000);
    </script>
    <?php endif; ?>

     <div class="container">
    <h2>
        Your Shopping Cart
        <?php if ($cart_count > 0): ?>
            (<?= $cart_count ?> <?= $cart_count > 1 ? 'items' : 'item' ?>)
        <?php endif; ?>
    </h2>
    <p>Review your items before checkout</p>
        <?php if ($cart_count > 0): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cart as $key => $item): 
                    $item_subtotal = $item['price'] * $item['qty'];
                    $image_path = ($item['type'] === 'accessory') ? 
                                  "uploads/accessories/{$item['image']}" : 
                                  "uploads/products/{$item['image']}";
                ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                            <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td><?= ucfirst($item['type']) ?></td>
                        <td><?= number_format($item['price']) ?>Ks</td>
                        <td>
                            <form action="update_cart.php" method="post" style="display:flex; gap:8px;">
                                <input type="hidden" name="key" value="<?= $key ?>">
                                <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1">
                                <button type="submit" class="btn" style="padding: 6px 12px;"><i class="fas fa-sync-alt"></i></button>
                            </form>
                        </td>
                        <td><?= number_format($item_subtotal) ?>Ks</td>
                        <td>
                            <form action="remove_from_cart.php" method="post">
                                <input type="hidden" name="key" value="<?= $key ?>">
                                <button type="submit" class="btn" style="background: #ff6b6b;"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div class="summary-title">Order Summary</div>

                   <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><?= number_format($subtotal) ?>Ks</span>
                </div>

                <?php if ($discount > 0): ?>
                <div class="summary-row" style="color: #88c9a1;">
                    <span>Discount (<?= $discount_percent ?>% off):</span>
                    <span><?= number_format($discount) ?>-ks</span>
                </div>
                <?php endif; ?>

                <?php if (!empty($gifts)): ?>
                <div class="summary-row">
                    <span>Your Gifts:</span>
                    <div>
                        <?php foreach ($gifts as $gift): ?>
                            <div class="gift-item"><i class="fas fa-gift"></i> <?= htmlspecialchars($gift) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>FREE</span>
                </div>

                <div class="summary-row" style="font-weight: bold; font-size: 1.2rem; border-bottom: none;">
                    <span>Total:</span>
                    <span><?= number_format($total) ?>Ks</span>
                </div>

                <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="checkout.php" class="btn"><i class="fas fa-credit-card"></i> Secure Checkout</a>
                    <a href="shop.php" style="margin-left: 10px;" class="btn" title="Continue shopping"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                </div>
            </div>

            <?php if ($showPromo): ?>
            <div class="promo-notification">
                <i class="fas fa-gift" style="color: #ff69b4;"></i>
                <strong style="margin-left: 10px;">You've unlocked a special offer: 
                <?php if ($discount_percent > 0): ?>
                    <?= $discount_percent ?>% off
                <?php endif; ?>
                <?php if (!empty($gifts) && $discount_percent > 0): ?>
                    +
                <?php endif; ?>
                <?php if (!empty($gifts)): ?>
                    Free <?= htmlspecialchars(implode(", ", $gifts)) ?>
                <?php endif; ?>
                </strong>
            </div>
            <?php endif; ?>
        
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Thanks for shopping with us.</h3>
                <p>Add some items to buy again!</p>
                <a href="shop.php" class="btn"><i class="fas fa-arrow-left"></i> Again Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
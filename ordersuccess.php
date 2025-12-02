<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Thank You!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .thank-you-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            opacity: 0.1;
        }
        
        .circle-1 {
            width: 200px;
            height: 200px;
            top: -50px;
            left: -50px;
        }
        
        .circle-2 {
            width: 300px;
            height: 300px;
            bottom: -100px;
            right: -100px;
        }
        
        .checkmark {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            animation: scaleUp 0.5s ease-out;
        }
        
        .checkmark i {
            color: white;
            font-size: 50px;
        }
        
        h1 {
            color: #2d3436;
            margin-bottom: 15px;
            font-size: 2.5rem;
        }
        
        .subtitle {
            color: #636e72;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }
        
        .order-details h2 {
            color: #2d3436;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .detail-label {
            color: #636e72;
            font-weight: 500;
        }
        
        .detail-value {
            color: #2d3436;
            font-weight: 600;
        }
        
        .total {
            font-size: 1.3rem;
            color: #2d3436;
            font-weight: 700;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed #e0e0e0;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
        }
        
        .btn-outline {
            background: transparent;
            color: #6c5ce7;
            border: 2px solid #6c5ce7;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .countdown {
            margin-top: 20px;
            color: #636e72;
            font-size: 0.9rem;
        }
        
        @keyframes scaleUp {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            70% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            .thank-you-container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="thank-you-container">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
        
        <div class="checkmark">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Thank You For Your Order!</h1>
        <p class="subtitle">Your order has been placed successfully and is being processed</p>
        
        <div class="order-details">
            <h2>Order Details</h2>
            
            <div class="detail-row">
                <span class="detail-label">Order Number:</span>
                <span class="detail-value">#<?php echo $order_id; ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Order Date:</span>
                <span class="detail-value"><?php echo date('F j, Y'); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">
                    <?php 
                    if ($payment_method_id == 1) echo "Credit Card";
                    elseif ($payment_method_id == 2) echo "PayPal";
                    elseif ($payment_method_id == 3) echo "Bank Transfer";
                    else echo "Not specified";
                    ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Items:</span>
                <span class="detail-value"><?php echo count($cart_items); ?> item(s)</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Subtotal:</span>
                <span class="detail-value">$<?php echo number_format($subtotal, 2); ?></span>
            </div>
            
            <?php if ($discount > 0): ?>
            <div class="detail-row">
                <span class="detail-label">Discount:</span>
                <span class="detail-value">-$<?php echo number_format($discount, 2); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="detail-row total">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value">$<?php echo number_format($total_price, 2); ?></span>
            </div>
        </div>
        
        <p>We've sent a confirmation email with your order details. You will receive a shipping confirmation email when your order ships.</p>
        
        <div class="actions">
            <a href="shop.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Continue Shopping
            </a>
            <a href="user/my_orders.php" class="btn btn-primary">
                <i class="fas fa-history"></i> View Order History
            </a>
        </div>
        
        <div class="countdown">
            <p>Redirecting to cart in <span id="countdown">10</span> seconds</p>
        </div>
    </div>

    <script>
        // Countdown timer for redirection
        let seconds = 10;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = 'cart.php';
            }
        }, 1000);
    </script>
</body>
</html>
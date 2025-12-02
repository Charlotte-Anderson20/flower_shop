<?php
session_start();
require_once 'includes/db.php';

// Redirect if not logged in or cart is empty
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart']['items'])) {
    header("Location: cart.php");
    exit;
}

// Get cart items, subtotal, discount, total from session
$cart       = $_SESSION['cart']['items'] ?? [];
$subtotal   = $_SESSION['cart_subtotal'] ?? 0;
$discount   = $_SESSION['cart_discount'] ?? 0;
$total      = $_SESSION['cart_total'] ?? $subtotal;
$gifts      = $_SESSION['cart_gifts'] ?? [];

// Count unique items and total quantity
$cart_count = $_SESSION['cart']['count'] ?? count($cart);
$total_qty  = 0;
if (!empty($cart)) {
    $total_qty = array_sum(array_column($cart, 'qty'));
}

// Fetch user details
$user_id = $_SESSION['customer_id'];
$user_stmt = $con->prepare("SELECT * FROM customer WHERE customer_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch payment methods
$methods = $con->query("SELECT * FROM payment_method");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Bloom Boutique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-pink: #ffb6c1;
            --soft-pink: #ffdfe6;
            --dark-pink: #d88a97;
            --accent-pink: #ff69b4;
            --light: #fff9fa;
            --dark: #4a4a4a;
            --success: #88c9a1;
            --danger: #ff6b6b;
            --border: #f0d8dc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .checkout-header h1 {
            font-size: 2.5rem;
            color: var(--dark-pink);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .checkout-header p {
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .checkout-layout {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }
        
        @media (max-width: 768px) {
            .checkout-layout {
                flex-direction: column;
            }
        }
        
        .checkout-main {
            flex: 2;
        }
        
        .checkout-sidebar {
            flex: 1;
            position: sticky;
            top: 20px;
        }
        
        .checkout-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(214, 112, 147, 0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            color: var(--dark-pink);
            margin-bottom: 1.5rem;
            font-weight: 600;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        
        .user-info-card .user-info-row {
            display: flex;
            margin-bottom: 1rem;
        }
        
        .user-info-label {
            font-weight: 600;
            min-width: 120px;
            color: var(--dark-pink);
        }
        
        .user-info-value {
            color: var(--dark);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark-pink);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            background-color: var(--light);
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-pink);
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
            background-color: var(--light);
        }
        
        .payment-method:hover {
            border-color: var(--accent-pink);
        }
        
        .payment-method input[type="radio"] {
            accent-color: var(--accent-pink);
            width: 18px;
            height: 18px;
        }
        
        .payment-method-details {
            flex-grow: 1;
        }
        
        .payment-method-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark);
        }
        
        .payment-method-info {
            font-size: 0.9rem;
            color: var(--dark);
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-btn {
            width: 100%;
            padding: 1rem;
            border: 2px dashed var(--accent-pink);
            border-radius: 8px;
            background-color: rgba(255, 105, 180, 0.05);
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--accent-pink);
            font-weight: 500;
        }
        
        .file-upload-btn:hover {
            background-color: rgba(255, 105, 180, 0.1);
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-name {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--dark);
            font-style: italic;
        }
        
        .btn {
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 1.1rem;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .btn-pink {
            background-color: var(--accent-pink);
            color: white;
        }
        
        .btn-pink:hover {
            background-color: #e55fa0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 105, 180, 0.3);
        }
        
        .gift-notice {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background-color: rgba(255, 214, 230, 0.5);
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--accent-pink);
        }
        
        .gift-notice i {
            color: var(--accent-pink);
            font-size: 1.5rem;
        }
        
        .gift-notice-content h4 {
            color: var(--dark-pink);
            margin-bottom: 0.25rem;
        }
        
        .gift-notice-content p {
            font-size: 0.9rem;
            color: var(--dark);
        }
        
        .order-details {
            margin-top: 1.5rem;
        }
        
        .order-detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed var(--border);
        }
        
        .order-total {
            font-size: 1.3rem;
            font-weight: 700;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            color: var(--dark-pink);
        }
        
        .help-card {
            text-align: center;
        }
        
        .help-card p {
            margin-bottom: 1.5rem;
        }
        
        .help-contact {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            color: var(--accent-pink);
        }
        
        .help-contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
       <div class="container">
    <div class="checkout-header">
        <h1>Almost There!</h1>
        <p>Complete your details to finalize your order</p>
    </div>

    <div class="checkout-layout">
        <div class="checkout-main">
            <div class="checkout-card user-info-card">
                <h2 class="section-title">Your Details</h2>
                <div class="user-info-row">
                    <span class="user-info-label">Name:</span>
                    <span class="user-info-value"><?= htmlspecialchars($user['customer_name'] ?? 'Not provided') ?></span>
                </div>
                <div class="user-info-row">
                    <span class="user-info-label">Email:</span>
                    <span class="user-info-value"><?= htmlspecialchars($user['customer_email'] ?? 'Not provided') ?></span>
                </div>
                <div class="user-info-row">
                    <span class="user-info-label">Phone:</span>
                    <span class="user-info-value"><?= htmlspecialchars($user['customer_phone'] ?? 'Not provided') ?></span>
                </div>
            </div>

            <?php if (!empty($gifts)): ?>
            <div class="gift-notice">
                <i class="fas fa-gift"></i>
                <div class="gift-notice-content">
                    <h4>Special Gift Included!</h4>
                    <p>Your order qualifies for: <?= implode(", ", $gifts) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <form action="process_order.php" method="POST" enctype="multipart/form-data" class="checkout-card">
                <h2 class="section-title">Payment Information</h2>

                <?php while($row = $methods->fetch_assoc()): ?>
                <label class="payment-method">
                    <input type="radio" name="payment_method_id" value="<?= $row['payment_method_id'] ?>" required>
                    <div class="payment-method-details">
                        <div class="payment-method-name"><?= htmlspecialchars($row['method_name']) ?></div>
                        <div class="payment-method-info"><?= htmlspecialchars($row['holder_name']) ?> | <?= htmlspecialchars($row['ph_no']) ?></div>
                        <?php if (!empty($row['additional_info'])): ?>
                        <div class="payment-method-info"><?= htmlspecialchars($row['additional_info']) ?></div>
                        <?php endif; ?>
                    </div>
                </label>
                <?php endwhile; ?>

                <div class="form-group">
                    <label class="form-label">Payment Proof (Screenshot/Receipt)</label>
                    <div class="file-upload">
                        <div class="file-upload-btn">
                            <i class="fas fa-cloud-upload-alt"></i> Choose File
                        </div>
                        <input type="file" name="payment_img" class="file-upload-input" accept="image/*" required>
                    </div>
                    <div class="file-name" id="file-name">No file selected yet</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Special Instructions (Optional)</label>
                    <textarea name="customer_note" class="form-control" placeholder="Any special delivery instructions..."></textarea>
                </div>

                <button type="submit" class="btn btn-pink btn-block">
                    <i class="fas fa-lock"></i> Complete Secure Checkout
                </button>
            </form>
        </div>

            <div class="checkout-sidebar">
                <div class="checkout-card">
                    <h2 class="section-title">Order Highlights</h2>
                    <div class="order-details">

                        <div class="order-detail-item">
                            <span>Items:</span>
                            <span>
                                <?= $cart_count ?> type<?= $cart_count > 1 ? 's' : '' ?>
                                (<?= $total_qty ?> qty)
                            </span>
                        </div>

                        <div class="order-detail-item">
                            <span>Subtotal:</span>
                            <span>ks<?= number_format($subtotal, 2) ?></span>
                        </div>

                        <?php if ($discount > 0): ?>
                        <div class="order-detail-item" style="color: var(--success);">
                            <span>Discount (<?= $_SESSION['cart_promo_title'] ?? '' ?>):</span>
                            <span>-ks<?= number_format($discount, 2) ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($gifts)): ?>
                        <div class="order-detail-item">
                            <span>Your Gifts:</span>
                            <span>
                                <?php foreach ($gifts as $gift): ?>
                                    <?= htmlspecialchars($gift) ?><br>
                                <?php endforeach; ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <div class="order-detail-item">
                            <span>Delivery:</span>
                            <span>FREE</span>
                        </div>

                        <div class="order-detail-item order-total">
                            <span>Total:</span>
                            <span>ks<?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                </div>



            <div class="checkout-card help-card">
                <h2 class="section-title">Need Help?</h2>
                <p>Our customer care team is here to assist you with any questions about your order.</p>

                <div class="help-contact">
                    <div class="help-contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <span>+1 (555) 123-4567</span>
                    </div>
                    <div class="help-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>care@bloomboutique.com</span>
                    </div>
                    <div class="help-contact-item">
                        <i class="fas fa-comment-dots"></i>
                        <span>Live Chat</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <script>
        // Show selected file name
        document.querySelector('.file-upload-input').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected yet';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>
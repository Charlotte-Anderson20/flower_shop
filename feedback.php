<?php
require_once 'includes/db.php';
session_start();

$customer_id = $_SESSION['customer_id'] ?? null;
if (!$customer_id) {
    header("Location: login.php");
    exit();
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $customer_id) {
    $ratings = $_POST['rating'] ?? [];
    $feedbacks = $_POST['feedback_text'] ?? [];

    if (!empty($ratings)) {
        $stmt = $con->prepare("
            INSERT INTO feedback (customer_id, order_id, product_id, feedback_text, feedback_rating, feedback_date) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        foreach ($ratings as $orderId => $products) { // loop by order first
            foreach ($products as $productId => $rating) {
                $text = $feedbacks[$orderId][$productId] ?? '';
                $stmt->bind_param("iiisi", $customer_id, $orderId, $productId, $text, $rating);
                $stmt->execute();
            }
        }

        $_SESSION['feedback_success'] = true;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Fetch products without feedback per order
$orderQuery = "
    SELECT o.order_id, o.order_date,
           oi.product_id, p.product_name,
           (SELECT image_url FROM product_images WHERE product_id = p.product_id LIMIT 1) as product_image
    FROM `order` o
    JOIN orders_item oi ON o.order_id = oi.order_id
    JOIN product p ON oi.product_id = p.product_id
    LEFT JOIN feedback f 
           ON f.customer_id = o.customer_id 
          AND f.product_id = oi.product_id 
          AND f.order_id = o.order_id
    WHERE o.customer_id = $customer_id
      AND o.order_status = 'Accepted'
      AND f.feedback_id IS NULL
    ORDER BY o.order_date DESC, o.order_id
";

$orderResult = $con->query($orderQuery);

// Group products by order
$orders = [];
while ($row = $orderResult->fetch_assoc()) {
    $orderId = $row['order_id'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'order_date' => $row['order_date'],
            'products' => []
        ];
    }
    $orders[$orderId]['products'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Your Feedback | Our Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #6d5d6e;
            --secondary: #f4eee0;
            --accent: #a6b1e1;
            --dark: #393646;
            --light: #f8f5f1;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }
        
        /* Hero Section Styles */
        .contact-hero {
            position: relative;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(240, 206, 220, 0.8) 0%, rgba(244, 138, 170, 0.7) 100%);
            margin-bottom: 40px;
        }
        
        .contact-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .contact-hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin-bottom: 20px;
            color: black;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .contact-hero-content h1:after {
            background: var(--secondary);
            width: 100px;
        }
        
        .contact-hero-content p {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 0;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .contact-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 L0,100 Z" fill="none"/><path d="M0,0 C50,20 50,80 100,100 L100,0 Z" fill="rgba(255,255,255,0.07)"/><path d="M0,100 C40,80 60,20 100,0 L0,0 Z" fill="rgba(255,255,255,0.07)"/></svg>');
            background-size: cover;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        h1 {
            font-family: 'Playfair Display', serif;
            color: black;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent);
        }
        
        .feedback-form {
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }
        
        select, textarea, input[type="number"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        select:focus, textarea:focus, input[type="number"]:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(166, 177, 225, 0.2);
        }
        
        .product-select {
            position: relative;
        }
        
        .product-select:after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--primary);
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: block;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .submit-btn:hover {
            background: var(--dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(109, 93, 110, 0.3);
        }
        
        .product-card {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            background: var(--secondary);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        
        .product-name {
            font-weight: 500;
        }
        
        .product-image-placeholder {
            width: 60px;
            height: 60px;
            background: #f0f0f0;
            border-radius: 8px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
        }
        
        .rating-input-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .rating-input-container input {
            max-width: 80px;
        }
        
        .rating-hint {
            font-size: 14px;
            color: #666;
        }
        
        /* Star Rating */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin: 10px 0;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            cursor: pointer;
            width: 30px;
            height: 30px;
            margin: 0 2px;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3e%3cpath d='M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z' fill='%23ddd'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: center;
            transition: all 0.2s;
        }
        
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3e%3cpath d='M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z' fill='%23ffc107'/%3e%3c/svg%3e");
        }
        
        /* Order grouping styles */
        .order-group {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background-color: #fafafa;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .order-date {
            font-weight: 600;
            color: var(--primary);
        }
        
        .order-id {
            font-size: 14px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
            
            .contact-hero {
                height: 50vh;
            }
            
            .contact-hero-content h1 {
                font-size: 2.5rem;
            }
            
            .contact-hero-content p {
                font-size: 1rem;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .rating-input-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
        
          .thank-you-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .thank-you-popup.active {
            opacity: 1;
            visibility: visible;
        }
        
        .popup-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        
        .thank-you-popup.active .popup-content {
            transform: translateY(0);
        }
        
        .popup-icon {
            font-size: 60px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .popup-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .popup-message {
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .popup-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .popup-button:hover {
            background: var(--dark);
        }
    </style>
</head>
<body>
     <?php include 'header.php' ?>
    
   <div class="thank-you-popup" id="thankYouPopup">
    <div class="popup-content">
        <div class="popup-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 class="popup-title">Thank You!</h2>
        <p class="popup-message">Your feedback has been successfully submitted. We appreciate you taking the time to share your experience with us.</p>
        <button class="popup-button" onclick="closeThankYouPopup()">Continue</button>
    </div>
</div>

<section class="contact-hero">
    <div class="contact-hero-content">
        <h1>Feedback</h1>
        <p>
            Your voice helps us bloom even brighter.  
            Share your thoughts, experiences, and suggestions—because every petal of feedback  
            inspires us to create more memorable moments for you.
        </p>
    </div>
    <div class="contact-hero-overlay"></div>
</section>

<div class="container">
    <h1>Share Your Experience</h1>
    <p style="text-align: center; margin-bottom: 30px; color: var(--primary);">
        We value your feedback to help us improve our products and services.
    </p>
    
  <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $orderId => $orderData): ?>
            <div class="order-group">
                <h3>Order #<?= $orderId ?> (<?= date('F j, Y', strtotime($orderData['order_date'])) ?>)</h3>
                <form method="post">
                    <?php foreach ($orderData['products'] as $product): ?>
                        <div class="product-feedback-card">
                            <strong><?= htmlspecialchars($product['product_name']) ?></strong>

                            <!-- Rating -->
                            <div class="star-rating">
                                <?php for ($i=5; $i>=1; $i--): ?>
                                    <input type="radio"
                                           name="rating[<?= $orderId ?>][<?= $product['product_id'] ?>]"
                                           value="<?= $i ?>" required>
                                    <label><?= $i ?></label>
                                <?php endfor; ?>
                            </div>

                            <!-- Feedback -->
                            <textarea name="feedback_text[<?= $orderId ?>][<?= $product['product_id'] ?>]"
                                      placeholder="Your feedback here..."></textarea>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit">Submit Feedback for Order #<?= $orderId ?></button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No products available for feedback.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php' ?>
  <script>
document.querySelectorAll('.product-feedback-card').forEach(card => {
    card.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => {
            card.style.boxShadow = '0 0 0 2px var(--accent)';
        });
    });
});

// Show thank you popup if feedback was just submitted
<?php if (isset($_SESSION['feedback_success']) && $_SESSION['feedback_success']): ?>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(showThankYouPopup, 500);
        <?php unset($_SESSION['feedback_success']); ?>
    });
<?php endif; ?>

function showThankYouPopup() {
    document.getElementById('thankYouPopup').classList.add('active');
}

function closeThankYouPopup() {
    document.getElementById('thankYouPopup').classList.remove('active');
}

function handleFormSubmit(form) {
    let isValid = true;
    const ratingInputs = form.querySelectorAll('input[type="radio"]:checked');
    
    if (ratingInputs.length === 0) {
        alert('Please provide a rating for at least one product');
        isValid = false;
    }
    
    return isValid;
}
</script>
</body>
</html>
<?php
session_start();
require 'includes/db.php';

$customer_id = $_SESSION['customer_id'] ?? null;
$occasions = $con->query("SELECT * FROM occasions");
$flowers = $con->query("SELECT * FROM flower_type");
$arrangements = $con->query("SELECT * FROM arrangement_type");


$flowerData = '';
$occasionData = '';
$arrangementSlug = '';

$flowerTypes = isset($row['flower_types']) ? explode(',', $row['flower_types']) : [];
$occasionsList = isset($row['occasions']) ? explode(',', $row['occasions']) : [];
$arrangementSlug = isset($row['arrangement_name']) ? strtolower(str_replace(' ', '-', $row['arrangement_name'])) : '';

$flowerData = strtolower(implode(' ', array_map(function($f) {
    return str_replace(' ', '-', trim($f));
}, $flowerTypes)));
$occasionData = strtolower(implode(' ', array_map(function($o) {
    return str_replace(' ', '-', trim($o));
}, $occasionsList)));


// Initialize wishlist status array
$wishlistStatus = [];
if (isset($_SESSION['customer_id'])) {
    $customerId = $_SESSION['customer_id'];
    $wishlistQuery = "SELECT product_id FROM wishlist WHERE customer_id = $customerId";
    $wishlistResult = $con->query($wishlistQuery);
    
    if ($wishlistResult) {
        while ($row = $wishlistResult->fetch_assoc()) {
            $wishlistStatus[$row['product_id']] = true;
        }
    }
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$product_query = "SELECT p.*, at.arrangement_name, 
                 (SELECT AVG(feedback_rating) FROM feedback WHERE product_id = p.product_id) as avg_rating,
                 (SELECT COUNT(*) FROM feedback WHERE product_id = p.product_id) as review_count
                 FROM product p
                 JOIN arrangement_type at ON p.arrangement_id = at.arrangement_id
                 WHERE p.product_id = ?";
$stmt = $con->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: shop.php");
    exit();
}

// Fetch all product images
$images_query = "SELECT * FROM product_images WHERE product_id = ?";
$stmt = $con->prepare($images_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$images_result = $stmt->get_result();
$product_images = [];
while ($row = $images_result->fetch_assoc()) {
    $product_images[] = $row;
}

// Fetch reviews
$reviews_query = "SELECT f.*, c.customer_name, c.customer_image 
                 FROM feedback f
                 JOIN customer c ON f.customer_id = c.customer_id
                 WHERE f.product_id = ?
                 ORDER BY f.feedback_date DESC
                 LIMIT 5";
$stmt = $con->prepare($reviews_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}

// Related products
$related_query = "SELECT p.*, 
                 (SELECT image_url FROM product_images pi WHERE pi.product_id = p.product_id LIMIT 1) AS product_image
                 FROM product p
                 WHERE p.arrangement_id = ? AND p.product_id != ?
                 LIMIT 4";
$stmt = $con->prepare($related_query);
$stmt->bind_param("ii", $product['arrangement_id'], $product_id);
$stmt->execute();
$related_result = $stmt->get_result();
$related_products = [];
while ($row = $related_result->fetch_assoc()) {
    $related_products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?> | Tiny Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e91e63;
            --secondary-color: #f8bbd0;
            --dark-color: #333;
            --light-color: #fff;
            --gray-color: #f5f5f5;
            --text-color: #555;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background-color: #f9f9f9;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Product Detail Section */
        .product-detail {
            padding: 60px 0;
            background-color: var(--light-color);
        }
        
        .product-detail-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        /* Product Gallery */
        .product-gallery {
            display: flex;
            flex-direction: column;
        }
        
        .main-image-container {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            height: 500px;
            background-color: #f5f5f5;
        }
        
        .main-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: opacity 0.3s ease;
        }
        
        .thumbnail-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        
        .thumbnail {
            cursor: pointer;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            height: 100px;
            background-color: #f5f5f5;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .thumbnail.active, .thumbnail:hover {
            border-color: var(--primary-color);
        }
        
        /* Product Info */
        .product-info {
            padding: 20px 0;
        }
        
        .product-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .product-meta {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .rating {
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        
        .stars {
            color: #ffc107;
            margin-right: 5px;
        }
        
        .review-count {
            color: var(--text-color);
            font-size: 0.9rem;
        }
        
        .product-price {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 600;
            margin: 20px 0;
        }
        
        .product-description {
            margin-bottom: 25px;
            color: var(--text-color);
        }
        
        .product-meta-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
        }
        
        .meta-item i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            background-color: var(--gray-color);
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 1px solid var(--gray-color);
            margin: 0 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--light-color);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #c2185b;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--secondary-color);
        }
        
        .share-buttons {
            display: flex;
            gap: 10px;
        }
        
        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--gray-color);
            color: var(--dark-color);
            transition: all 0.3s ease;
        }
        
        .share-btn:hover {
            background-color: var(--primary-color);
            color: var(--light-color);
        }
        
        /* Product Tabs */
        .product-tabs {
            margin-top: 60px;
        }
        
        .tab-header {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            padding: 12px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            position: relative;
            color: var(--text-color);
        }
        
        .tab-btn.active {
            color: var(--primary-color);
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
            padding: 20px 0;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Reviews Section */
        .review-item {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .reviewer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .reviewer-name {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .review-date {
            font-size: 0.8rem;
            color: #999;
            margin-top: 3px;
        }
        
        .review-rating {
            margin-left: auto;
            color: #ffc107;
        }
        
        /* Related Products */
        .related-products {
            padding: 60px 0;
            background-color: var(--gray-color);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .section-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .section-title p {
            color: var(--text-color);
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background: var(--light-color);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-img {
            height: 200px;
            overflow: hidden;
        }
        
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-img img {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-info h3 {
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .price {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-color);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            visibility: hidden;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .toast.success {
            background: var(--primary-color);
        }

        .toast.error {
            background: #e74c3c;
        }

        /* Cart count pulse animation */
        .cart-count.pulse {
            animation: pulse 0.5s ease;
            display: inline-block;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Wishlist button styles */
        .wishlist-btn {
            position: relative;
            transition: all 0.3s ease;
        }

        .wishlist-btn.pulse {
            animation: pulse 0.6s ease;
        }

        .wishlist-btn i {
            transition: all 0.3s ease;
        }

        .wishlist-btn.active i {
            color: #e74c3c;
        }

        .wishlist-btn:hover i {
            transform: scale(1.1);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .product-detail-container {
                grid-template-columns: 1fr;
            }
            
            .main-image-container {
                height: 400px;
            }
        }
        
        @media (max-width: 768px) {
            .product-meta-info {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .tab-header {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
            
            .tab-btn {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php' ?>
    
    <section class="product-detail">
        <div class="container">
            <div class="product-detail-container">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="main-image-container">
                        <img id="main-image" src="uploads/products/<?= htmlspecialchars($product_images[0]['image_url'] ?? 'default-product.jpg') ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="main-image">
                    </div>
                    
                    <div class="thumbnail-container">
                        <?php foreach ($product_images as $index => $image): ?>
                            <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" onclick="changeImage('uploads/products/<?= htmlspecialchars($image['image_url']) ?>', this)">
                                <img src="uploads/products/<?= htmlspecialchars($image['image_url']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?> - Thumbnail <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="product-info">
                    <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
                    
                    <div class="product-meta">
                        <div class="rating">
                            <div class="stars">
                                <?php
                                $avg_rating = round($product['avg_rating'] ?? 0);
                                $empty_stars = 5 - $avg_rating;
                                
                                for ($i = 0; $i < $avg_rating; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }
                                
                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                            <span class="review-count">(<?= $product['review_count'] ?? 0 ?> reviews)</span>
                        </div>
                        
                        <span class="arrangement-badge"><?= htmlspecialchars($product['arrangement_name']) ?></span>
                    </div>
                    
                    <div class="product-price"><?= number_format($product['product_price']) ?> Ks </div>
                    
                    <p class="product-description"><?= htmlspecialchars($product['product_description']) ?></p>
                    
                    <div class="product-meta-info">
                        <div class="meta-item">
                            <i class="fas fa-box-open"></i>
                            <span>Size: <?= htmlspecialchars($product['size']) ?></span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                         <button class="add-to-cart" 
                                            data-id="<?= $product['product_id'] ?>" 
                                            data-name="<?= htmlspecialchars($product['product_name']) ?>" 
                                            data-price="<?= $product['product_price'] ?>" 
                                            data-image="<?= $product_images[0]['image_url'] ?? 'default-product.jpg' ?>">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                        <button class="btn btn-outline wishlist-btn <?= isset($wishlistStatus[$product_id]) ? 'active' : '' ?>" 
                                onclick="toggleWishlist(this, <?= $product_id ?>)" 
                                data-loggedin="<?= isset($_SESSION['customer_id']) ? '1' : '0' ?>">
                            <i class="<?= isset($wishlistStatus[$product_id]) ? 'fas' : 'far' ?> fa-heart"></i> Wishlist
                        </button>
                    </div>
                    
                    <div class="share-buttons">
                        <span>Share: </span>
                        <a href="#" class="share-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="share-btn"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="share-btn"><i class="fab fa-pinterest-p"></i></a>
                        <a href="#" class="share-btn"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Product Tabs -->
            <div class="product-tabs">
                <div class="tab-header">
                    <button class="tab-btn active" onclick="openTab('details')">Product Details</button>
                    <button class="tab-btn" onclick="openTab('delivery')">Delivery Info</button>
                    <button class="tab-btn" onclick="openTab('reviews')">Reviews (<?= $product['review_count'] ?? 0 ?>)</button>
                </div>
                
                <div id="details" class="tab-content active">
                    <h3>About This Arrangement</h3>
                    <p><?= htmlspecialchars($product['product_description']) ?></p>
                    
                    <h3>Care Instructions</h3>
                    <p>To keep your flowers looking fresh, follow these care instructions:</p>
                    <ul>
                        <li>Change the water every 2-3 days</li>
                        <li>Trim stems at an angle every few days</li>
                        <li>Keep away from direct sunlight and heat sources</li>
                        <li>Remove any wilted flowers or leaves</li>
                    </ul>
                </div>
                
                <div id="delivery" class="tab-content">
                    <h3>Delivery Information</h3>
                    <p>We offer same-day delivery for orders placed before 2pm local time. Delivery times may vary based on location and order volume.</p>
                    
                    <h3>Shipping Options</h3>
                    <ul>
                        <li><strong>Standard Delivery:</strong> $5.99 (2-3 business days)</li>
                        <li><strong>Express Delivery:</strong> $9.99 (1 business day)</li>
                        <li><strong>Same-Day Delivery:</strong> $14.99 (Order by 2pm)</li>
                    </ul>
                </div>
                
                <div id="reviews" class="tab-content">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-avatar">
                                        <img src="<?= htmlspecialchars($review['customer_image']) ?>" alt="<?= htmlspecialchars($review['customer_name']) ?>" onerror="this.src='default-profile.png'">
                                    </div>
                                    <div>
                                        <div class="reviewer-name"><?= htmlspecialchars($review['customer_name']) ?></div>
                                        <div class="review-date"><?= date('F j, Y', strtotime($review['feedback_date'])) ?></div>
                                    </div>
                                    <div class="review-rating">
                                        <?php
                                        for ($i = 0; $i < $review['feedback_rating']; $i++) {
                                            echo '<i class="fas fa-star"></i>';
                                        }
                                        for ($i = 0; $i < 5 - $review['feedback_rating']; $i++) {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="review-text">
                                    <p><?= htmlspecialchars($review['feedback_text']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No reviews yet. Be the first to review this product!</p>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </section>
    
    <!-- Related Products -->
    <section class="related-products">
        <div class="container">
            <div class="section-title">
                <h2>You May Also Like</h2>
                <p>Similar arrangements you might love</p>
            </div>
            
            <div class="products-grid">
                <?php foreach ($related_products as $related): ?>
                    <div class="product-card">
                        <a href="product_details.php?id=<?= $related['product_id'] ?>">
                            <div class="product-img">
                                <img src="uploads/products/<?= htmlspecialchars($related['product_image']) ?>" alt="<?= htmlspecialchars($related['product_name']) ?>">
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($related['product_name']) ?></h3>
                                <div class="price"><?= number_format($related['product_price']) ?> Ks </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php' ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script>
$(document).ready(function() {
    // Initialize cart functionality
    initCart();
});

// Change main image when thumbnail is clicked
function changeImage(src, element) {
    document.getElementById('main-image').src = src;

    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    element.classList.add('active');
}

// Tab switching
function openTab(tabName, event) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show the selected tab and mark button as active
    document.getElementById(tabName).classList.add('active');
    if (event) event.currentTarget.classList.add('active');
}

function initCart() {
    // Add to cart functionality with enhanced UI feedback
    $('.add-to-cart').click(async function() {
        const $button = $(this);
        const product = {
            id: $button.data('id'),
            name: $button.data('name'),
            price: $button.data('price'),
            image: $button.data('image'),
            type: 'product'
        };

        // Visual feedback
        const originalText = $button.html();
        $button.html('<i class="fas fa-spinner fa-spin"></i> Adding...');
        $button.prop('disabled', true);

        try {
            const response = await fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(product)
            });

            const data = await response.json();

            if (data.status === 'success') {
                // Update cart count
                $('.cart-count').each(function() {
                    $(this).text(data.cart_count).addClass('pulse');
                    setTimeout(() => $(this).removeClass('pulse'), 500);
                });

                // Button feedback
                $button.addClass('added');
                $button.html('<i class="fas fa-check"></i> Added!');
                
                // Show toast
                showToast(`${product.name} added to cart!`, 'success');
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    $button.html(originalText);
                    $button.removeClass('added');
                    $button.prop('disabled', false);
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to add to cart');
            }
        } catch (error) {
            console.error('Error:', error);
            $button.html(originalText);
            $button.prop('disabled', false);
            showToast(error.message, 'error');
        }
    });
}

// Show toast notification
function showToast(message, type = 'success') {
    const $toast = $('<div class="toast"></div>');
    $toast.text(message)
        .addClass(type)
        .addClass('show')
        .appendTo('body');

    setTimeout(() => {
        $toast.removeClass('show');
        setTimeout(() => $toast.remove(), 300);
    }, 3000);
}

function toggleWishlist(heartElement, productId) {
    const isLoggedIn = heartElement.dataset.loggedin === '1';
    
    if (!isLoggedIn) {
        showToast('Please login to use the wishlist', 'warning'); // ✅ show message
        return; // stop execution
    }

    const isActive = heartElement.classList.contains('active');
    const icon = heartElement.querySelector('i');

    // Visual feedback
    heartElement.classList.add('pulse');
    setTimeout(() => heartElement.classList.remove('pulse'), 600);

    // Optimistic UI update
    heartElement.classList.toggle('active');
    if (heartElement.classList.contains('active')) {
        icon.classList.replace('far', 'fas');
    } else {
        icon.classList.replace('fas', 'far');
    }

    fetch('wishlist_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&action=${isActive ? 'remove' : 'add'}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert UI changes if failed
            heartElement.classList.toggle('active');
            if (heartElement.classList.contains('active')) {
                icon.classList.replace('far', 'fas');
            } else {
                icon.classList.replace('fas', 'far');
            }
            showToast(data.message || 'Failed to update wishlist', 'error');
        } else {
            showToast(data.message, 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert UI changes on error
        heartElement.classList.toggle('active');
        if (heartElement.classList.contains('active')) {
            icon.classList.replace('far', 'fas');
        } else {
            icon.classList.replace('fas', 'far');
        }
        showToast('An error occurred while updating wishlist', 'error');
    });
}

</script>

</body>
</html>
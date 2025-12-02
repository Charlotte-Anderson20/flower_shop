<?php
session_start();
require 'includes/db.php';
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

// Only assign session variables if $customer is set and is an array
if (isset($customer) && is_array($customer)) {
    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['customer_name'];
    $_SESSION['customer_image'] = $customer['customer_image'];
}

$customer_id = $_SESSION['customer_id'] ?? null;
$showPopup = false;

if ($customer_id) {
    $query = "
        SELECT oi.product_id
        FROM `order` o
        JOIN orders_item oi ON o.order_id = oi.order_id
        LEFT JOIN feedback f ON f.customer_id = o.customer_id AND f.product_id = oi.product_id
        WHERE o.customer_id = ?
        AND o.order_status = 'Accepted'
        AND TIMESTAMPDIFF(MINUTE, o.order_date, NOW()) >= 1 -- ✅ 1 min for testing
        AND f.feedback_id IS NULL
        LIMIT 1
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $showPopup = true;
    }
}

// Get best selling products
$bestSellingProducts = [];
$bestSellingQuery = "SELECT p.*, SUM(oi.quantity) as total_sold, 
                    (SELECT image_url FROM product_images WHERE product_id = p.product_id LIMIT 1) as image_url
                    FROM product p
                    JOIN orders_item oi ON p.product_id = oi.product_id
                    JOIN `order` o ON oi.order_id = o.order_id
                    WHERE o.order_status = 'Accepted' AND oi.item_type = 'product'
                    GROUP BY p.product_id
                    ORDER BY total_sold DESC
                    LIMIT 4";
$bestSellingResult = $con->query($bestSellingQuery);
if ($bestSellingResult) {
    $bestSellingProducts = $bestSellingResult->fetch_all(MYSQLI_ASSOC);
}

// Get best selling accessories
$bestSellingAccessories = [];
$bestAccessoriesQuery = "SELECT 
    a.aid,
    a.name,
    a.image,
    a.category,
    a.price,
    SUM(oi.quantity) AS total_sold
FROM orders_item oi
JOIN accessories a ON oi.item_type = 'accessory' AND oi.sub_price = a.price
GROUP BY a.aid, a.name, a.image, a.category, a.price
ORDER BY total_sold DESC
LIMIT 4";

$bestAccessoriesResult = $con->query($bestAccessoriesQuery);
if ($bestAccessoriesResult) {
    $bestSellingAccessories = $bestAccessoriesResult->fetch_all(MYSQLI_ASSOC);
}

// Get top rated products
$topRatedProducts = [];
$topRatedQuery = "SELECT p.*, AVG(f.feedback_rating) as avg_rating,
                 (SELECT image_url FROM product_images WHERE product_id = p.product_id LIMIT 1) as image_url
                 FROM product p
                 JOIN feedback f ON p.product_id = f.product_id
                 GROUP BY p.product_id
                 HAVING avg_rating >= 4
                 ORDER BY avg_rating DESC, COUNT(f.feedback_id) DESC
                 LIMIT 4";
$topRatedResult = $con->query($topRatedQuery);
if ($topRatedResult) {
    $topRatedProducts = $topRatedResult->fetch_all(MYSQLI_ASSOC);
}

// Get most popular arrangement types
$popularArrangements = [];
$arrangementQuery = "SELECT at.*, COUNT(oi.order_item_id) as order_count
                    FROM arrangement_type at
                    LEFT JOIN product p ON at.arrangement_id = p.arrangement_id
                    LEFT JOIN orders_item oi ON p.product_id = oi.product_id
                    LEFT JOIN `order` o ON oi.order_id = o.order_id
                    WHERE o.order_status = 'Accepted' OR o.order_id IS NULL
                    GROUP BY at.arrangement_id
                    ORDER BY order_count DESC
                    LIMIT 4";
$arrangementResult = $con->query($arrangementQuery);
if ($arrangementResult) {
    $popularArrangements = $arrangementResult->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiny Flower Shop - Elegant Floral Arrangements</title>
  <link rel="shortcut icon" href="images/flowerb.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <style>
        /* Add to your existing styles */
        
        .featured-section {
            padding: 60px 0;
            background-color: #f9f9f9;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .section-header h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 15px;
            font-family: 'Playfair Display', serif;
        }
        
        .section-header p {
            font-size: 1.1rem;
            color: #777;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .featured-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .featured-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .featured-img-container {
            height: 200px;
            overflow: hidden;
        }
        
        .featured-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .featured-card:hover .featured-img {
            transform: scale(1.05);
        }
        
        .featured-content {
            padding: 20px;
        }
        
        .featured-category {
            display: inline-block;
            background: #f0e6ff;
            color: #8a4fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        
        .featured-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .featured-price {
            font-size: 1.3rem;
            color: #e91e63;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .featured-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #777;
        }
        
        .featured-rating {
            color: #ffc107;
        }
        
        .featured-sales {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .featured-sales i {
            color: #e91e63;
        }
        
        .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e91e63;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
        }
        
        .view-all {
            text-align: center;
            margin-top: 30px;
        }
        
        .view-all-btn {
            display: inline-block;
            padding: 10px 25px;
            background: #e91e63;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        
        .view-all-btn:hover {
            background: #c2185b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(233,30,99,0.3);
        }
        
       /* Desktop default */
.arrangement-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Large Tablets (992px - 1199px) */
@media (max-width: 1199px) {
    .arrangement-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
    }
}

/* Tablets (768px - 991px) */
@media (max-width: 991px) {
    .arrangement-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

/* Mobile (up to 767px) */
@media (max-width: 767px) {
    .arrangement-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}

        
        .arrangement-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .arrangement-card:hover {
            transform: translateY(-10px);
        }
        
        .arrangement-icon {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        
        .default-icon {
            font-size: 2.5rem;
            color: #ff6b6b;
            margin-bottom: 15px;
        }

       

        
        /* Small devices (landscape phones, 576px and up) */
@media (min-width: 576px) {
    .hero h1 {
        font-size: 2.5rem;
    }
    .hero p {
        font-size: 1.1rem;
    }
    .hero-content {
        padding: 30px;
    }
}

/* Medium devices (tablets, 768px and up) */
@media (min-width: 768px) {
    .hero h1 {
        font-size: 3rem;
    }
    .hero p {
        font-size: 1.2rem;
    }
    .hero-content {
        padding: 40px;
        max-width: 90%;
    }
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .testimonial-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
    .hero h1 {
        font-size: 3.5rem;
    }
    .hero p {
        max-width: 700px;
    }
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
   
    .testimonial-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .footer-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Extra large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {
    .hero h1 {
        font-size: 4rem;
    }
    .hero-content {
        max-width: 1200px;
    }
    .products-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Mobile-specific adjustments (max-width 575px) */
@media (max-width: 575px) {
    nav {
        padding: 1rem;
    }
    .nav-links {
        gap: 1rem;
    }
    .user-actions {
        gap: 0.8rem;
        margin-left: 1rem;
    }
    .user-btn {
        padding: 0.5rem 1rem;
    }
    .section-title h2 {
        font-size: 2rem;
    }
    .testimonial-grid {
        grid-template-columns: 1fr;
    }
    .modal-content {
        width: 95%;
    }
}

/* Tablet portrait adjustments (max-width 767px) */
@media (max-width: 767px) {
    .logo {
        font-size: 1.5rem;
    }
    .logo i {
        font-size: 1.7rem;
    }
    
    .form-row {
        flex-direction: column;
    }
    .half-width {
        width: 100%;
    }
}

/* Landscape phones and smaller tablets */
@media (max-width: 991px) and (orientation: landscape) {
    .hero {
        min-height: 400px;
    }
    .hero h1 {
        font-size: 2.5rem;
    }
}

/* Height adjustments for smaller screens */
@media (max-height: 600px) {
    .hero {
        min-height: 100vh;
    }
    .hero h1 {
        font-size: 2.2rem;
        margin-bottom: 10px;
    }
    .hero p {
        margin-bottom: 15px;
    }
}


    </style>
</head>
<body>
    <?php include 'header.php' ?>

     <section class="hero">
        <div class="hero-floral-decoration decoration-1"></div>
        <div class="hero-floral-decoration decoration-2"></div>
        
        <div class="hero-content">
            <h1>Tinny Nature's Elegance in Every Petal</h1>
            <p>Discover handcrafted floral arrangements that bring beauty and joy to every occasion. Our sustainably sourced blooms are artfully designed to create lasting impressions.</p>
            <a href="shop.php" class="cta-button">Explore Collections</a>
        </div>
    </section>
    
    <?php if ($showPopup): ?>
    <script>
        window.addEventListener('load', function () {
            setTimeout(() => {
                const popup = document.createElement('div');
                popup.innerHTML = `
                    <div style="
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        background: #fff0f6;
                        border: 1px solid #f5c2da;
                        padding: 16px;
                        border-radius: 12px;
                        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                        z-index: 9999;
                        max-width: 320px;
                        font-family: sans-serif;
                    ">
                        <p style="margin:0 0 8px;font-weight:bold;color:#d63384;">
                            💐 Enjoying your flowers?
                        </p>
                        <p style="margin:0 0 10px;color:#444;">We'd love your thoughts!</p>
                        <a href="user/feedback.php" style="
                            display: inline-block;
                            padding: 8px 12px;
                            background-color: #d63384;
                            color: white;
                            text-decoration: none;
                            border-radius: 8px;
                        ">Leave Feedback</a>
                        <button onclick="this.parentElement.remove()" style="
                            margin-left: 10px;
                            background: none;
                            border: none;
                            color: #999;
                            font-size: 14px;
                            cursor: pointer;
                        ">Dismiss</button>
                    </div>
                `;
                document.body.appendChild(popup);
            }, 1500); // Show after 1.5 seconds page load
        });
    </script>
    <?php endif; ?>

    <section class="featured-section">
    <div class="section-header">
        <h2>Our Best Sellers</h2>
        <p>Discover the floral arrangements our customers love most</p>
    </div>
    
    <div class="featured-grid">
        <?php foreach ($bestSellingProducts as $product): ?>
        <div class="featured-card animate__animated animate__fadeInUp" 
     onclick="window.location.href='product_details.php?id=<?php echo $product['product_id']; ?>'">
    <div class="badge">Bestseller</div>
    <div class="featured-img-container">
        <img src="uploads/products/<?php echo htmlspecialchars($product['image_url'] ?? 'default-product.jpg'); ?>" 
             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
             class="featured-img">
    </div>
    <div class="featured-content">
        <span class="featured-category">Flowers</span>
        <h3 class="featured-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
        <div class="featured-price"><?php echo number_format($product['product_price']); ?> Ks </div>
        <div class="featured-meta">
            <span class="featured-sales">
                <i class="fas fa-fire"></i> <?php echo $product['total_sold'] ?? 0; ?> sold
            </span>
        </div>
    </div>
</div>

        <?php endforeach; ?>
    </div>
    
    <div class="view-all">
        <a href="shop.php" class="view-all-btn">View All Products</a>
    </div>
</section>

<section class="featured-section" style="background-color: #fff;">
    <div class="section-header">
        <h2>Popular Accessories</h2>
        <p>Perfect additions to complement your floral gifts</p>
    </div>
    
    <div class="featured-grid">
        <?php foreach ($bestSellingAccessories as $accessory): ?>
        <div class="featured-card animate__animated animate__fadeInUp">
            <div class="badge">Popular</div>
            <div class="featured-img-container">
                <img src="uploads/accessories/<?php echo htmlspecialchars($accessory['image'] ?? 'default-accessory.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($accessory['name']); ?>" 
                     class="featured-img">
            </div>
            <div class="featured-content">
                <span class="featured-category"><?php echo htmlspecialchars($accessory['category']); ?></span>
                <h3 class="featured-title"><?php echo htmlspecialchars($accessory['name']); ?></h3>
                <div class="featured-price"><?php echo number_format($accessory['price']); ?> Ks </div>
                <div class="featured-meta">
                    <span class="featured-sales">
                        <i class="fas fa-fire"></i> <?php echo $accessory['total_sold'] ?? 0; ?> sold
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="view-all">
        <a href="accessories.php" class="view-all-btn">View All Accessories</a>
    </div>
</section>

    
    <!-- Popular Arrangement Types -->
    <section class="arrangements" id="arrangements" style="background-color: #fff; padding: 60px 0;">
        <div class="section-header">
            <h2>Popular Arrangement Styles</h2>
            <p>Discover the floral styles our customers love most</p>
        </div>
        
        <div class="arrangement-grid">
            <?php foreach ($popularArrangements as $i => $arrangement): ?>
            <div class="arrangement-card animate__animated animate__fadeInUp animate-delay-<?php echo $i % 3; ?>">
                <?php if (!empty($arrangement['icon_image'])): ?>
                    <img src="<?php echo htmlspecialchars($arrangement['icon_image']); ?>" 
                         alt="<?php echo htmlspecialchars($arrangement['arrangement_name']); ?> icon" 
                         class="arrangement-icon">
                <?php else: ?>
                    <i class="fas fa-spa default-icon"></i>
                <?php endif; ?>
                
                <h3><?php echo htmlspecialchars($arrangement['arrangement_name']); ?></h3>
                <p>Beautifully crafted <?php echo strtolower(htmlspecialchars($arrangement['arrangement_name'])); ?> arrangements</p>
                <?php if ($arrangement['order_count'] > 0): ?>
                    <small><?php echo $arrangement['order_count']; ?> ordered</small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <div class="section-header">
            <h2>Customer Love</h2>
            <p>What our customers say about our floral creations</p>
        </div>
        
        <div class="testimonial-grid">
            <?php
            $con = mysqli_connect("localhost", "root", "", "tinny_flower_shop");
            
            if (!$con) {
                die("Connection failed: " . mysqli_connect_error());
            }
            
            // Fetch testimonials with customer info
            $query = "SELECT f.*, c.customer_name, c.customer_image
                      FROM Feedback f
                      JOIN Customer c ON f.customer_id = c.customer_id
                      ORDER BY f.feedback_date DESC LIMIT 3";

            $result = mysqli_query($con, $query);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $stars = str_repeat('<i class="fas fa-star"></i>', $row['feedback_rating']);
                
                echo '<div class="testimonial-card animate__animated animate__fadeInUp">
                    <div class="rating">' . $stars . '</div>
                    <p>"' . $row['feedback_text'] . '"</p>
                    <div class="customer">
                        <div class="customer-img">
                            <img src="' . $row['customer_image'] . '" alt="' . $row['customer_name'] . '" onerror="this.src=\'uploads/customers/default-profile.jpg\'">
                        </div>
                        <div class="customer-info">
                            <h4>' . $row['customer_name'] . '</h4>
                            <p>Happy Customer</p>
                        </div>
                    </div>
                </div>';
            }
            
            mysqli_close($con);
            ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php' ?>
    
    <!-- Toast Notification -->
    <!-- <div id="toast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span class="toast-message"></span>
    </div> -->
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.getElementById('navbar');
                if (navbar) {
                    if (window.scrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                }
            });
            
            // Toast notification function
            function showToast(message, type = 'success') {
                const toast = document.getElementById("toast");
                const toastMessage = toast.querySelector('.toast-message');
                
                if (!toast) return;
                
                // Set message and type
                toastMessage.textContent = message;
                toast.className = 'toast'; // Reset classes
                toast.classList.add(type);
                
                // Show the toast
                toast.classList.add("show");
                
                // Hide after 3 seconds
                setTimeout(() => {
                    toast.classList.remove("show");
                }, 3000);
            }
            
            // Initialize cart if it doesn't exist
            if (!localStorage.getItem('cart')) {
                localStorage.setItem('cart', JSON.stringify([]));
            }

            // Update cart count display
            function updateCartCount() {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const cartCountElements = document.querySelectorAll('.cart-count');
                
                cartCountElements.forEach(el => {
                    el.textContent = cart.reduce((total, item) => total + (item.quantity || 1), 0);
                });
            }

            // Initialize cart count
            updateCartCount();
            
            // Initialize slick slider for testimonials if needed
            if ($('.testimonial-grid').length) {
                $('.testimonial-grid').slick({
                    dots: true,
                    infinite: true,
                    speed: 300,
                    slidesToShow: 1,
                    adaptiveHeight: true,
                    autoplay: true,
                    autoplaySpeed: 5000
                });
            }
        });
    </script>
</body>
</html>
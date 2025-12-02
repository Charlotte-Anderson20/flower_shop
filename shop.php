<?php
session_start();
include 'includes/db.php';

// Wishlist functionality
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

// Get filter options
$occasions = $con->query("SELECT * FROM occasions");
$flowers = $con->query("SELECT * FROM flower_type");
$arrangements = $con->query("SELECT * FROM arrangement_type");

// Product filtering logic
$where = [];

if (!empty($_POST['flowers'])) {
    $flower_ids = implode(',', array_map('intval', $_POST['flowers']));
    $where[] = "p.product_id IN (
        SELECT product_id FROM product_flower_type WHERE flower_type_id IN ($flower_ids)
    )";
}

if (!empty($_POST['occasions'])) {
    $occasion_ids = implode(',', array_map('intval', $_POST['occasions']));
    $where[] = "p.product_id IN (
        SELECT product_id FROM product_occasions WHERE occasion_id IN ($occasion_ids)
    )";
}

if (!empty($_POST['arrangements'])) {
    $arr_ids = implode(',', array_map('intval', $_POST['arrangements']));
    $where[] = "p.arrangement_id IN ($arr_ids)";
}

if (!empty($_POST['min_price'])) {
    $where[] = "p.product_price >= " . intval($_POST['min_price']);
}

if (!empty($_POST['max_price'])) {
    $where[] = "p.product_price <= " . intval($_POST['max_price']);
}

// Ratings filter
if (!empty($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    $where[] = "p.product_id IN (
        SELECT product_id
        FROM feedback
        GROUP BY product_id
        HAVING AVG(feedback_rating) >= $rating
    )";
}

// Build the SQL query
$sql = "
    SELECT 
        p.*, 
        at.arrangement_name,
        GROUP_CONCAT(DISTINCT ft.flower_name) AS flower_types,
        GROUP_CONCAT(DISTINCT o.occasion_name) AS occasions,
        (SELECT image_url FROM product_images WHERE product_id = p.product_id LIMIT 1) AS product_image
    FROM product p
    JOIN arrangement_type at ON p.arrangement_id = at.arrangement_id
    LEFT JOIN product_flower_type pft ON p.product_id = pft.product_id
    LEFT JOIN flower_type ft ON pft.flower_type_id = ft.flower_type_id
    LEFT JOIN product_occasions po ON p.product_id = po.product_id
    LEFT JOIN occasions o ON po.occasion_id = o.occasion_id
    WHERE p.is_active = 1
";

if ($where) {
    $sql .= " AND " . implode(' AND ', $where);
}

$sql .= " GROUP BY p.product_id ORDER BY p.date DESC";

// Execute the query
$result = mysqli_query($con, $sql);
$count = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiny Flower Shop - Elegant Floral Arrangements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --soft-pink: #f8e1e4;
        --darker-pink: #f3c4cb;
        --accent-pink: #e8a1ac;
        --dark-text: #333;
        --light-text: #666;
        --white: #fff;
        --light-gray: #f9f9f9;
        --border-radius: 12px;
        --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        color: var(--dark-text);
        background-color: var(--white);
        line-height: 1.6;
    }

    h1, h2, h3, h4 {
        font-family: 'Playfair Display', serif;
        font-weight: 600;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        gap: 30px;
    }

    /* Filter Sidebar - Elegant Design */
    .filter-sidebar {
        width: 280px;
        flex-shrink: 0;
        margin-top: 40px;
    }

    .filter-section {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 25px;
        position: sticky;
        top: 20px;
    }

    .filter-header {
        border-bottom: 1px solid var(--soft-pink);
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .filter-header h3 {
        font-size: 1.5rem;
        color: var(--dark-text);
        margin: 0;
    }

    .filter-group {
        margin-bottom: 25px;
        border-bottom: 1px solid var(--soft-pink);
        padding-bottom: 20px;
    }

    .filter-group:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .filter-group-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        padding: 8px 0;
    }

    .filter-group-title span {
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--dark-text);
    }

    .filter-group-title i {
        color: var(--accent-pink);
        transition: var(--transition);
    }

    .filter-group-content {
        padding-top: 10px;
        display: none;
    }

    .filter-group.active .filter-group-content {
        display: block;
    }

    .filter-option {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
        padding: 8px 12px;
        border-radius: 6px;
        transition: var(--transition);
        cursor: pointer;
    }

    .filter-option:hover {
        background-color: var(--soft-pink);
    }

    .filter-option input {
        margin-right: 12px;
        accent-color: var(--accent-pink);
        width: 18px;
        height: 18px;
    }

    /* Price Range Styling */
    .price-range-container {
        padding-top: 15px;
    }

    .price-range {
        width: 100%;
        height: 6px;
        background: var(--soft-pink);
        border-radius: 3px;
        outline: none;
        margin-bottom: 15px;
    }

    .price-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        background: var(--accent-pink);
        border-radius: 50%;
        cursor: pointer;
        transition: var(--transition);
    }

    .price-range::-webkit-slider-thumb:hover {
        transform: scale(1.1);
    }

    .price-inputs {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .price-inputs input {
        width: 80px;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: 'Montserrat', sans-serif;
    }

    .price-inputs span {
        color: var(--light-text);
    }

    /* Reset Button */
    .filter-reset {
        width: 100%;
        padding: 12px;
        background: var(--soft-pink);
        color: var(--dark-text);
        border: none;
        border-radius: var(--border-radius);
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .filter-reset:hover {
        background: var(--darker-pink);
    }

    .filter-reset i {
        font-size: 0.9rem;
    }

    /* Main Content */
    .product-content {
        flex-grow: 1;
    }

    .section-title {
        text-align: center;
        margin-bottom: 40px;
    }

    .section-title h2 {
        font-size: 2.2rem;
        margin-bottom: 10px;
        color: var(--dark-text);
    }

    .section-title p {
        color: var(--light-text);
        font-size: 1.1rem;
        margin-bottom: 15px;
    }

    .title-divider {
        width: 80px;
        height: 3px;
        background: var(--accent-pink);
        margin: 0 auto;
        border-radius: 3px;
    }

    /* Products Header */
    .products-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .results-count {
        color: var(--light-text);
        font-size: 0.95rem;
    }

    .sort-options {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sort-options span {
        color: var(--light-text);
        font-size: 0.95rem;
    }

    .sort-options select {
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: 'Montserrat', sans-serif;
        background-color: var(--white);
        cursor: pointer;
        transition: var(--transition);
    }

    .sort-options select:hover {
        border-color: var(--accent-pink);
    }

    /* Modern Product Cards */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-bottom: 40px;
    }

    .product-item {
        position: relative;
    }

    .product-card {
        background: var(--white);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        aspect-ratio: 1.5 / 1;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .product-img {
        position: relative;
        padding-top: 66.66%; /* 3:2 aspect ratio */
        overflow: hidden;
    }

    .product-img img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .product-card:hover .product-img img {
        transform: scale(1.05);
    }

    .product-badges {
        position: absolute;
        top: 15px;
        left: 15px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        z-index: 2;
    }

    .product-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .arrangement-badge {
        background: var(--accent-pink);
        color: var(--white);
    }

    .discount-badge {
        background: var(--white);
        color: var(--accent-pink);
        border: 1px solid var(--accent-pink);
    }

    .wishlist-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 32px;
        height: 32px;
        background: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 2;
        transition: var(--transition);
    }

    .wishlist-btn:hover {
        background: var(--accent-pink);
        color: var(--white);
    }

    .product-info {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-info h3 {
        font-size: 1.2rem;
        margin: 0 0 10px 0;
        color: var(--dark-text);
        font-weight: 600;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 10px;
    }

    .stars {
        color: #ffc107;
    }

    .rating-count {
        font-size: 0.85rem;
        color: var(--light-text);
    }

    .price-container {
        margin-bottom: 10px;
    }

    .price {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--accent-pink);
    }

    .original-price {
        font-size: 0.9rem;
        text-decoration: line-through;
        color: var(--light-text);
        margin-left: 8px;
    }

    .size {
        display: inline-block;
        font-size: 0.85rem;
        color: var(--light-text);
        margin-bottom: 12px;
    }

    .product-info p {
        font-size: 0.95rem;
        color: var(--light-text);
        margin: 0 0 15px 0;
        flex-grow: 1;
    }

    /* Enhanced Action Buttons - Placed below card */
    .product-actions {
        display: flex;
        gap: 15px;
        margin-top: 15px;
        padding: 0 10px;
    }

    .add-to-cart {
        background: var(--accent-pink);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 1;
        box-shadow: 0 2px 10px rgba(232, 161, 172, 0.3);
    }

    .add-to-cart:hover {
        background: var(--darker-pink);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(232, 161, 172, 0.4);
    }

    .add-to-cart:active {
        transform: translateY(0);
    }

    .add-to-cart i {
        font-size: 1rem;
    }

    .view-details {
        background: transparent;
        color: var(--accent-pink);
        border: 1px solid var(--accent-pink);
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 1;
    }

    .view-details:hover {
        background: rgba(232, 161, 172, 0.1);
    }

    /* Pulse animation for add to cart */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .add-to-cart.added {
        animation: pulse 0.5s ease;
        background: #4CAF50;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 40px;
        padding: 0;
        list-style: none;
    }

    .page-item {
        display: flex;
    }

    .page-link {
        padding: 8px 15px;
        border-radius: 6px;
        color: var(--dark-text);
        text-decoration: none;
        transition: var(--transition);
        border: 1px solid transparent;
    }

    .page-link:hover {
        border-color: var(--accent-pink);
        color: var(--accent-pink);
    }

    .page-link.active {
        background: var(--accent-pink);
        color: var(--white);
        border-color: var(--accent-pink);
    }

    /* Toast Notification */
    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: var(--accent-pink);
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
        background: var(--accent-pink);
    }

    .toast.error {
        background: #e74c3c;
    }

    /* Cart count pulse animation */
    .cart-count.pulse {
        animation: pulse 0.5s ease;
        display: inline-block;
    }

    /* Responsive Adjustments */
    @media (max-width: 1200px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .container {
            flex-direction: column;
        }
        
        .filter-sidebar {
            width: 100%;
            position: static;
        }
        
        .filter-group-content {
            display: block;
        }
    }

    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .product-card {
            aspect-ratio: 1.25 / 1;
        }
        
        .products-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .product-actions {
            flex-direction: column;
            gap: 10px;
        }
    }

    /* Wishlist button styles */
    .wishlist-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 255, 255, 0.8);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 2;
    }

    .wishlist-btn:hover {
        background: rgba(255, 255, 255, 0.9);
        transform: scale(1.1);
    }

    .wishlist-btn i {
        font-size: 16px;
        color: #555;
    }

    .wishlist-btn.active i {
        color: #e74c3c;
    }

    .wishlist-btn.active:hover i {
        color: #c0392b;
    }
 

    .filter-group-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        padding: 8px 0;
        font-weight: bold;
        border-bottom: 1px solid #ddd;
    }

    .filter-group-title i {
        transition: transform 0.2s ease;
    }

    .filter-group-title i.rotated {
        transform: rotate(180deg);
    }

    .filter-group-content {
        padding: 5px 0 10px 0;
    }

    .contact-hero {
        position: relative;
        height: 60vh;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        background-image: url('images/Screenshot\ 2025-08-15\ 012214.png');
        background-size: cover;
        background-position: center;
        margin-bottom: 40px;
    }
    
    .contact-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(251, 185, 207, 0.85) 0%, rgba(237, 226, 232, 0.8) 100%);
    }
    
    .contact-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        padding: 0 20px;
        max-width: 800px;
        margin: 0 auto;
        animation: fadeInUp 0.8s ease-out;
    }
    
    .contact-hero h1 {
        font-size: 3rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .contact-hero p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        opacity: 0.9;
        line-height: 1.6;
    }
    
    .contact-hero-info {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 40px;
    }
    
    .contact-method {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        padding: 15px 25px;
        border-radius: 50px;
        transition: all 0.3s ease;
    }
    
    .contact-method:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-3px);
    }
    
    .contact-method i {
        font-size: 1.2rem;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .contact-hero {
            height: 70vh;
            min-height: 500px;
        }
        
        .contact-hero h1 {
            font-size: 2.2rem;
        }
        
        .contact-hero p {
            font-size: 1rem;
        }
        
        .contact-hero-info {
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }
        
        .contact-method {
            width: 100%;
            max-width: 250px;
            justify-content: center;
        }
    }
    </style>
</head>
<body>
    <?php include 'header.php' ?>
      <section class="contact-hero">
        <div class="contact-hero-content">
            <h1>Our Product Collections</h1>
            <p>Our floral creations will give customers the best experiences</p>
            <div class="contact-hero-info">
                <div class="contact-method">
                    <i class="fas fa-phone-alt"></i>
                    <span>+1 (555) 123-4567</span>
                </div>
                <div class="contact-method">
                    <i class="fas fa-envelope"></i>
                    <span>hello@tinyflowershop.com</span>
                </div>
                <div class="contact-method">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>123 Blossom St, Flower City</span>
                </div>
            </div>
        </div>
        <div class="contact-hero-overlay"></div>
    </section>
    <!-- Product Gallery Section -->
    <section class="product-gallery">
        <div class="container">
            <!-- Filter Sidebar -->
            <aside class="filter-sidebar">
                <div class="filter-section">
                    <div class="filter-header">
                        <h3>Filter by</h3>
                    </div>

                    <!-- Flower Types -->
                    <div class="filter-group">
                        <div class="filter-group-title">
                            <span>Flower Types</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-group-content">
                            <?php while ($flower = $flowers->fetch_assoc()): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="flowers[]" value="<?= $flower['flower_type_id'] ?>">
                                    <?= htmlspecialchars($flower['flower_name']) ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Occasions -->
                    <div class="filter-group">
                        <div class="filter-group-title">
                            <span>Occasions</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-group-content">
                            <?php while ($occasion = $occasions->fetch_assoc()): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="occasions[]" value="<?= $occasion['occasion_id'] ?>">
                                    <?= htmlspecialchars($occasion['occasion_name']) ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Arrangements -->
                    <div class="filter-group">
                        <div class="filter-group-title">
                            <span>Arrangement</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-group-content">
                            <?php while ($arr = $arrangements->fetch_assoc()): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="arrangements[]" value="<?= $arr['arrangement_id'] ?>">
                                    <?= htmlspecialchars($arr['arrangement_name']) ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <div class="filter-group-title">
                            <span>Price Range</span>
                        </div>
                        <div class="filter-group-content price-range-container">
                            <input type="number" id="min-price" name="min_price" placeholder="Min">
                            <input type="number" id="max-price" name="max_price" placeholder="Max">
                        </div>
                    </div>

                    <!-- Ratings -->
                    <div class="filter-group">
                        <div class="filter-group-title">
                            <span>Rating</span>
                        </div>
                        <div class="filter-group-content">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <label class="filter-option">
                                    <input type="radio" name="rating" value="<?= $i ?>">
                                    <?= str_repeat('★', $i) ?> & up
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <button type="button" class="filter-reset"><i class="fas fa-redo"></i> Reset Filters</button>
                </div>
            </aside>
            
            <!-- Main Product Content -->
            <main class="product-content">
                <div class="section-title">
                    <h2>Our Floral Collection</h2>
                    <p>Discover our exquisite range of floral arrangements for every occasion</p>
                    <div class="title-divider"></div>
                </div>

                <div class="products-header">
                    <div class="results-count">Showing <?= $count ?> products</div>
                    <div class="sort-options">
                        <span>Sort by:</span>
                        <select>
                            <option>Featured</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Newest Arrivals</option>
                            <option>Best Rated</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="products-grid">
                    <?php if ($result && $count > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            $flowerTypes = $row['flower_types'] ? explode(',', $row['flower_types']) : [];
                            $occasionsList = $row['occasions'] ? explode(',', $row['occasions']) : [];
                            
                            // Sluggify values
                            $arrangementSlug = strtolower(str_replace(' ', '-', $row['arrangement_name']));
                            $dataFlowers = $flowerTypes ? strtolower(str_replace(' ', '-', implode(',', $flowerTypes))) : '';
                            $dataOccasions = $occasionsList ? strtolower(str_replace(' ', '-', implode(',', $occasionsList))) : '';
                        ?>
                            <div class="product-item">
                                <div class="product-card animate__animated animate__fadeInUp"
                                    data-arrangement="<?= $arrangementSlug ?>"
                                    data-price="<?= $row['product_price'] ?>"
                                    data-occasion="<?= $dataOccasions ?>"
                                    data-flower="<?= $dataFlowers ?>">
                                    
                                    <div class="product-img">
                                        <img src="uploads/products/<?= htmlspecialchars($row['product_image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                                        <div class="product-badges">
                                            <span class="product-badge arrangement-badge">
                                                <?= htmlspecialchars($row['arrangement_name']) ?>
                                            </span>
                                            <span class="product-badge discount-badge">
                                                <?= number_format($row['product_price']) ?> Ks
                                            </span>
                                        </div>
                                        <div class="wishlist-btn <?= isset($wishlistStatus[$row['product_id']]) ? 'active' : '' ?>" 
                                            onclick="toggleWishlist(this, <?= $row['product_id'] ?>)"
                                            data-loggedin="<?= isset($_SESSION['customer_id']) ? '1' : '0' ?>">
                                            <i class="<?= isset($wishlistStatus[$row['product_id']]) ? 'fas' : 'far' ?> fa-heart"></i>
                                        </div>
                                    </div>

                                    <div class="product-info">
                                        <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                                        <div class="product-rating">
                                            <div class="stars">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                            </div>
                                            <span class="rating-count">(24)</span>
                                        </div>
                                        <div class="price-container">
                                            <span class="price"><?= number_format($row['product_price']) ?> Ks </span>
                                            <span class="original-price"><?= number_format($row['product_price']) ?> Ks </span>
                                        </div>
                                        <span class="size"><?= htmlspecialchars($row['size']) ?></span>
                                        <p><?= htmlspecialchars(substr($row['product_description'], 0, 100)) ?>...</p>
                                    </div>
                                </div>

                                <div class="product-actions">
                                    <button class="add-to-cart" 
                                            data-id="<?= $row['product_id'] ?>" 
                                            data-name="<?= htmlspecialchars($row['product_name']) ?>" 
                                            data-price="<?= $row['product_price'] ?>" 
                                            data-image="<?= $row['product_image'] ?>">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    <button class="view-details" data-id="<?= $row['product_id'] ?>">
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-products">No products found matching your criteria.</p>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <ul class="pagination">
                    <li class="page-item"><a href="#" class="page-link active">1</a></li>
                    <li class="page-item"><a href="#" class="page-link">2</a></li>
                    <li class="page-item"><a href="#" class="page-link">3</a></li>
                    <li class="page-item"><a href="#" class="page-link">4</a></li>
                    <li class="page-item"><a href="#" class="page-link">Next <i class="fas fa-chevron-right"></i></a></li>
                </ul>
            </main>
        </div>
    </section>
    <div id="toast" class="toast"></div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize cart functionality
        initCart();
        
        // Toggle filter group
        $('.filter-group-title').on('click', function(){
            let content = $(this).next('.filter-group-content');
            let icon = $(this).find('i');

            // Toggle content with slide animation
            content.slideToggle(200);

            // Rotate arrow icon
            icon.toggleClass('rotated');
        });
        
        // Event: On any filter change
        $('.filter-option input, #min-price, #max-price').on('change keyup', function(){
            fetchFilteredProducts();
        });

        // Reset filters
        $('.filter-reset').click(function(){
            $('input[type=checkbox], input[type=radio]').prop('checked', false);
            $('#min-price, #max-price').val('');
            fetchFilteredProducts();
        });
    });

    function fetchFilteredProducts(){
        let filters = {
            flowers: $('input[name="flowers[]"]:checked').map(function(){ return this.value; }).get(),
            occasions: $('input[name="occasions[]"]:checked').map(function(){ return this.value; }).get(),
            arrangements: $('input[name="arrangements[]"]:checked').map(function(){ return this.value; }).get(),
            min_price: $('#min-price').val(),
            max_price: $('#max-price').val(),
            rating: $('input[name="rating"]:checked').val()
        };

        $.ajax({
            url: window.location.pathname,
            method: 'POST',
            data: filters,
            beforeSend: function(){
                $('.products-grid').html('<div class="loading">Loading...</div>');
            },
            success: function(data){
                // Extract just the products grid from the response
                let $response = $(data);
                let productsGrid = $response.find('.products-grid').html();
                let resultsCount = $response.find('.results-count').text();
                
                // Update the page
                $('.products-grid').html(productsGrid);
                $('.results-count').text(resultsCount);
                
                // Reinitialize cart functionality for the new products
                initCart();
            },
            error: function() {
                $('.products-grid').html('<div class="error">Error loading products. Please try again.</div>');
            }
        });
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

        // View details button functionality
        $('.view-details').click(function() {
            const productId = $(this).data('id');
            window.location.href = 'product_details.php?id=' + productId;
        });
    }

function showToast(message, type = 'success') {
        const $toast = $('#toast');
        if (!$toast.length) return;

        $toast.text(message)
            .removeClass('success error')
            .addClass(type)
            .addClass('show');

        setTimeout(() => {
            $toast.removeClass('show');
        }, 3000);
    }

    function toggleWishlist(heartElement, productId) {
        // Check if user is logged in (add this data attribute to your wishlist button)
        const isLoggedIn = heartElement.dataset.loggedin === '1';
        
        if (!isLoggedIn) {
                    showToast('Please login to use the wishlist', 'warning'); // ✅ show message
return;
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
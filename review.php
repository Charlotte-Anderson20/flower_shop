<?php
session_start();
require 'includes/db.php';

// Only assign session variables if $customer is set and is an array
if (isset($customer) && is_array($customer)) {
    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['customer_name'];
    $_SESSION['customer_image'] = $customer['customer_image'];
}

$customer_id = $_SESSION['customer_id'] ?? null;

// Fetch reviews from database
$reviews_query = "SELECT f.*, c.customer_name, c.customer_image, p.product_name 
                  FROM feedback f
                  JOIN customer c ON f.customer_id = c.customer_id
                  JOIN product p ON f.product_id = p.product_id
                  ORDER BY f.feedback_date DESC";
$reviews_result = mysqli_query($con, $reviews_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews | Floral Boutique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-pink: #ff69b4;
            --light-pink: #ffb6c1;
            --dark-pink: #db7093;
            --white: #fff8f8;
            --gray: #f5f5f5;
            --dark-gray: #333;
            --light-gray: #e0e0e0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--white);
            margin: 0;
            padding: 0;
            color: var(--dark-gray);
            line-height: 1.6;
        }
        
        .hero {
            background: linear-gradient(135deg, var(--light-pink) 0%, var(--primary-pink) 100%);
            color: white;
            text-align: center;
            padding: 5rem 1rem;
            margin-bottom: 3rem;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .reviews-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .review-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .customer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid var(--light-pink);
        }
        
        .customer-info h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--dark-gray);
        }
        
        .product-name {
            font-size: 0.9rem;
            color: var(--primary-pink);
            margin-top: 0.2rem;
            font-weight: 600;
        }
        
        .review-date {
            font-size: 0.8rem;
            color: #777;
            margin-top: 0.3rem;
        }
        
        .review-content {
            margin-top: 1rem;
        }
        
        .review-text {
            color: #555;
            margin-bottom: 1rem;
        }
        
        .rating {
            color: var(--primary-pink);
            margin-bottom: 0.5rem;
        }
        
        .rating i {
            margin-right: 2px;
        }
        
        .no-reviews {
            text-align: center;
            padding: 3rem;
            color: #777;
            font-size: 1.1rem;
            grid-column: 1 / -1;
        }
        
        .add-review-btn {
            display: block;
            background: var(--primary-pink);
            color: white;
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 2rem auto;
            max-width: 250px;
            transition: background 0.3s ease;
        }
        
        .add-review-btn:hover {
            background: var(--dark-pink);
        }
        
        @media (max-width: 768px) {
            .reviews-container {
                grid-template-columns: 1fr;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <section class="hero">
        <h1>Customer Reviews</h1>
        <p>Read what our customers say about our floral arrangements and services</p>
    </section>
    
    <div class="container">
        <div class="reviews-container">
            <?php if(mysqli_num_rows($reviews_result) > 0): ?>
                <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <img src="<?php echo htmlspecialchars($review['customer_image']); ?>" 
     alt="<?php echo htmlspecialchars($review['customer_name']); ?>" 
     class="customer-avatar">

                            <div class="customer-info">
                                <h3><?php echo htmlspecialchars($review['customer_name']); ?></h3>
                                <div class="product-name"><?php echo htmlspecialchars($review['product_name']); ?></div>
                                <div class="review-date"><?php echo date('F j, Y', strtotime($review['feedback_date'])); ?></div>
                            </div>
                        </div>
                        
                        <div class="review-content">
                            <div class="rating">
                                <?php 
                                $rating = $review['feedback_rating'];
                                for($i = 1; $i <= 5; $i++): 
                                    if($i <= $rating): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; 
                                endfor; ?>
                            </div>
                            
                            <p class="review-text"><?php echo htmlspecialchars($review['feedback_text']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-reviews">
                    <p>No reviews yet. Be the first to share your experience!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
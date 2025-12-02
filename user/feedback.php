<?php
include '../includes/db.php';
include 'side.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$totalOrders = 0;

$query = "SELECT COUNT(*) AS total_orders FROM `order` WHERE customer_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_orders = $row['total_orders'];


// Fetch wishlist products
$query = "
   SELECT w.wishlist_id, p.product_id, p.product_name, p.product_price, p.product_description,
       pi.image_url
FROM wishlist w
JOIN product p ON w.product_id = p.product_id
LEFT JOIN (
    SELECT product_id, MIN(image_url) AS image_url
    FROM product_images
    GROUP BY product_id
) pi ON p.product_id = pi.product_id
WHERE w.customer_id = ? AND p.is_active = 1

";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$wishlistItems = [];
while ($row = $result->fetch_assoc()) {
    $wishlistItems[] = $row;
}

  $stmt = $con->prepare("
        SELECT COUNT(*) AS total 
        FROM wishlist w 
        JOIN product p ON w.product_id = p.product_id 
        WHERE w.customer_id = ? AND p.is_active = 1
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $totalWishlist = $row['total'];
    }

      $stmt = $con->prepare("
        SELECT COUNT(*) AS total 
        FROM `order` 
        WHERE customer_id = ? AND order_status = 'Accepted'
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $totalOrders = $row['total'];
    }

    $ordersQuery = "
    SELECT o.order_id, o.order_date, 
           oi.product_id, oi.quantity, oi.sub_price,
           p.product_name, p.product_description,
           (SELECT image_url FROM product_images WHERE product_id = p.product_id LIMIT 1) as image_url,
           f.feedback_id, f.feedback_rating, f.feedback_text
    FROM `order` o
    JOIN orders_item oi ON o.order_id = oi.order_id
    JOIN product p ON oi.product_id = p.product_id
    LEFT JOIN feedback f ON oi.product_id = f.product_id AND f.customer_id = o.customer_id
    WHERE o.customer_id = ? 
    AND o.order_status = 'Accepted'
    ORDER BY o.order_date DESC
    LIMIT 5
";

$stmt = $con->prepare($ordersQuery);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$ordersResult = $stmt->get_result();

$ordersWithItems = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orderId = $row['order_id'];
    
    if (!isset($ordersWithItems[$orderId])) {
        $ordersWithItems[$orderId] = [
            'order_date' => $row['order_date'],
            'items' => []
        ];
    }
    
    $ordersWithItems[$orderId]['items'][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>

     :root {
    --primary: #f7c9d8;         /* Softer pink */
    --primary-dark: #e6a5b8;    /* Muted rosy pink */
    --primary-light: #fde9ef;   /* Very light pink */
    --secondary: #333333;       /* Dark gray for contrast */
    --light: #f9f6f7;           /* Light background gray-pink */
    --dark: #1a1a1a;            /* Almost black */
    --accent: #f4aab9;          /* Soft medium pink */
    --text: #2c2c2c;            /* Deep charcoal for better readability */
    --dark-bg: #fdf8f9;         /* Very soft pinkish white */
    --darker-bg: #f6f1f2;       /* Slightly deeper pink-tinted background */
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}


    .feedback-section {
    margin-top: 3rem;
    padding: 2rem;
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.feedback-section .section-title {
    margin-bottom: 1.5rem;
}

.feedback-section .section-title p {
    color: #666;
    margin-top: 0.5rem;
}

.orders-feedback {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-feedback-card {
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
}

.order-feedback-header {
    background-color: var(--primary-light);
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-feedback-header h4 {
    margin: 0;
    color: var(--dark);
}

.order-date {
    color: #666;
    font-size: 0.9rem;
}

.order-items-feedback {
    padding: 1rem;
}

.item-feedback {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f5f5f5;
}

.item-feedback:last-child {
    border-bottom: none;
}

.item-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.item-info img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 8px;
}

.item-details h5 {
    margin: 0 0 0.5rem 0;
    color: var(--dark);
}

.item-details p {
    margin: 0.2rem 0;
    font-size: 0.9rem;
    color: #666;
}

.item-price {
    color: var(--primary) !important;
    font-weight: 600;
}

.feedback-action .leave-feedback-btn,
.feedback-action .edit-feedback-btn {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
    color: var(--dark);
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.feedback-action .leave-feedback-btn:hover,
.feedback-action .edit-feedback-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 105, 180, 0.3);
}

.existing-feedback {
    text-align: right;
}

.rating-display {
    margin-bottom: 0.5rem;
}

.rating-display .star {
    color: #ccc;
    font-size: 1.2rem;
}

.rating-display .star.filled {
    color: #ff69b4;
}

.feedback-preview {
    margin: 0.5rem 0;
    font-size: 0.9rem;
    color: #666;
    font-style: italic;
}

.no-feedback-items {
    text-align: center;
    padding: 2rem;
    color: #666;
    font-style: italic;
}

/* Feedback Modal Styles */
.feedback-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

.feedback-modal-content {
    background-color: white;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.4s ease;
}

.feedback-modal-header {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
    color: var(--dark);
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

.feedback-modal-header h3 {
    margin: 0;
    font-size: 1.4rem;
}

.feedback-close-btn {
    background: none;
    border: none;
    font-size: 1.8rem;
    cursor: pointer;
    color: var(--dark);
}

.feedback-modal-body {
    padding: 1.5rem;
}

.feedback-product {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.feedback-product img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    margin-right: 1rem;
}

.feedback-product h4 {
    margin: 0;
    color: var(--dark);
    font-size: 1.1rem;
}

.rating-input {
    margin-bottom: 1.5rem;
}

.rating-input p {
    margin: 0 0 1rem 0;
    font-weight: 500;
}

.rating-stars {
    display: flex;
    gap: 0.5rem;
}

.rating-stars .star {
    font-size: 2rem;
    color: #ccc;
    cursor: pointer;
    transition: all 0.2s ease;
}

.rating-stars .star:hover {
    color: #ff69b4;
    transform: scale(1.2);
}

.feedback-input {
    margin-bottom: 1.5rem;
}

.feedback-input label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.feedback-input textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    resize: vertical;
    min-height: 120px;
    font-family: inherit;
    transition: border-color 0.3s ease;
}

.feedback-input textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.1);
}

.feedback-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.cancel-feedback-btn {
    background: none;
    border: 1px solid #ddd;
    color: #666;
    padding: 0.8rem 1.5rem;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.cancel-feedback-btn:hover {
    background-color: #f9f9f9;
}

.feedback-submit-btn {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
    color: var(--dark);
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.feedback-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 105, 180, 0.3);
}

.feedback-submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Thank You Message Styles */
.thank-you-message {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    z-index: 1001;
    animation: fadeIn 0.5s ease;
}

.thank-you-content i {
    font-size: 3rem;
    color: #ff69b4;
    margin-bottom: 1rem;
    animation: pulse 1.5s infinite;
}

.thank-you-content h3 {
    margin: 0 0 1rem 0;
    color: var(--dark);
}

.thank-you-content p {
    margin: 0;
    color: #666;
}

/* Animations */
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

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .item-feedback {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .feedback-action {
        align-self: flex-end;
    }
    
    .existing-feedback {
        text-align: left;
    }
    
    .feedback-actions {
        flex-direction: column;
    }
    
    .cancel-feedback-btn,
    .feedback-submit-btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .feedback-section {
        padding: 1.5rem;
    }
    
    .feedback-modal-content {
        width: 95%;
        margin: 1rem;
    }
    
    .rating-stars .star {
        font-size: 1.8rem;
    }
    
    .thank-you-message {
        width: 90%;
    }
    
    .order-feedback-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

 <div class="feedback-section">
    <div class="section-title">
        <h3>Rate Your Recent Purchases</h3>
        <p>Share your experience to help us improve</p>
    </div>
    
    <div class="orders-feedback">
        <?php if (!empty($ordersWithItems)): ?>
            <?php foreach ($ordersWithItems as $orderId => $order): ?>
                <div class="order-feedback-card">
                    <div class="order-feedback-header">
                        <h4>Order #TF-<?php echo str_pad($orderId, 4, '0', STR_PAD_LEFT); ?></h4>
                        <span class="order-date"><?php echo date("M d, Y", strtotime($order['order_date'])); ?></span>
                    </div>
                    
                    <div class="order-items-feedback">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="item-feedback" data-product-id="<?php echo $item['product_id']; ?>">
                                <div class="item-info">
                                    <img src="../uploads/products/<?php echo $item['image_url'] ?? 'default.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    <div class="item-details">
                                        <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                        <p>Qty: <?php echo $item['quantity']; ?></p>
                                        <p class="item-price"><?php echo number_format($item['sub_price']); ?> Ks</p>
                                    </div>
                                </div>
                                
                                <div class="feedback-action">
                                    <?php if ($item['feedback_id']): ?>
                                        <div class="existing-feedback">
                                            <div class="rating-display">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star <?php echo $i <= $item['feedback_rating'] ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="feedback-preview"><?php echo htmlspecialchars(substr($item['feedback_text'], 0, 50) . '...'); ?></p>
                                            <button class="edit-feedback-btn">Edit Review</button>
                                        </div>
                                    <?php else: ?>
                                        <button class="leave-feedback-btn">Leave Feedback</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-feedback-items">You don't have any recent orders to review.</p>
        <?php endif; ?>
    </div>
</div>

  <script>
// Function to show feedback modal for a specific product
function showFeedbackModal(productId, productName, productImage, existingRating = 0, existingFeedback = '') {
    // Create and show modal
    const modal = document.createElement('div');
    modal.className = 'feedback-modal';
    modal.innerHTML = `
        <div class="feedback-modal-content">
            <div class="feedback-modal-header">
                <h3>Review Your Product</h3>
                <button class="feedback-close-btn">&times;</button>
            </div>
            <div class="feedback-modal-body">
                <div class="feedback-product">
                    <img src="${productImage}" alt="${productName}">
                    <h4>${productName}</h4>
                </div>
                <form class="feedback-form" data-product-id="${productId}">
                    <div class="rating-input">
                        <p>How would you rate this product?</p>
                        <div class="rating-stars">
                            <span class="star" data-value="1">☆</span>
                            <span class="star" data-value="2">☆</span>
                            <span class="star" data-value="3">☆</span>
                            <span class="star" data-value="4">☆</span>
                            <span class="star" data-value="5">☆</span>
                        </div>
                        <input type="hidden" name="rating" id="rating-value" value="${existingRating}">
                    </div>
                    <div class="feedback-input">
                        <label for="feedback-text">Share your experience</label>
                        <textarea name="feedback" id="feedback-text" placeholder="What did you like about this product? Was there anything that could be improved?" required>${existingFeedback}</textarea>
                    </div>
                    <div class="feedback-actions">
                        <button type="button" class="cancel-feedback-btn">Cancel</button>
                        <button type="submit" class="feedback-submit-btn">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Initialize stars with existing rating if available
    if (existingRating > 0) {
        const stars = modal.querySelectorAll('.star');
        stars.forEach((star, index) => {
            if (index < existingRating) {
                star.textContent = '★';
                star.style.color = '#ff69b4';
            }
        });
    }
    
    // Add event listeners
    modal.querySelector('.feedback-close-btn').addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    modal.querySelector('.cancel-feedback-btn').addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    // Star rating functionality
    const stars = modal.querySelectorAll('.star');
    stars.forEach(star => {
        star.addEventListener('click', () => {
            const value = parseInt(star.getAttribute('data-value'));
            document.getElementById('rating-value').value = value;
            
            // Update stars display
            stars.forEach((s, i) => {
                s.textContent = i < value ? '★' : '☆';
                s.style.color = i < value ? '#ff69b4' : '#ccc';
            });
        });
    });
    
    // Form submission
    modal.querySelector('.feedback-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitFeedback(this);
    });
    
    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    });
}

// Function to submit feedback via AJAX
function submitFeedback(form) {
    const formData = new FormData(form);
    const productId = form.getAttribute('data-product-id');
    const rating = formData.get('rating');
    const feedback = formData.get('feedback');
    
    if (rating == 0) {
        alert('Please select a rating');
        return;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('.feedback-submit-btn');
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    fetch('submit_feedback.php', {
        method: 'POST',
        body: JSON.stringify({
            product_id: productId,
            rating: rating,
            feedback: feedback
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showThankYouMessage();
            // Close the modal and refresh the feedback section
            document.querySelector('.feedback-modal').remove();
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert('Error submitting feedback: ' + data.message);
            submitBtn.textContent = 'Submit Review';
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting feedback.');
        submitBtn.textContent = 'Submit Review';
        submitBtn.disabled = false;
    });
}

// Function to show thank you message
function showThankYouMessage() {
    const thankYou = document.createElement('div');
    thankYou.className = 'thank-you-message';
    thankYou.innerHTML = `
        <div class="thank-you-content">
            <i class="fas fa-heart"></i>
            <h3>Thank You for Your Feedback!</h3>
            <p>Your review helps us grow and serve you better.</p>
        </div>
    `;
    
    document.body.appendChild(thankYou);
    
    // Auto-close after 3 seconds
    setTimeout(() => {
        if (document.body.contains(thankYou)) {
            document.body.removeChild(thankYou);
        }
    }, 3000);
}

// Add event listeners to feedback buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.leave-feedback-btn, .edit-feedback-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemElement = this.closest('.item-feedback');
            const productId = itemElement.getAttribute('data-product-id');
            const productName = itemElement.querySelector('h5').textContent;
            const productImage = itemElement.querySelector('img').src;
            
            // Check if there's existing feedback
            let existingRating = 0;
            let existingFeedback = '';
            
            if (this.classList.contains('edit-feedback-btn')) {
                const ratingElement = itemElement.querySelector('.rating-display');
                existingRating = ratingElement.querySelectorAll('.star.filled').length;
                existingFeedback = itemElement.querySelector('.feedback-preview').textContent.replace('...', '');
            }
            
            showFeedbackModal(productId, productName, productImage, existingRating, existingFeedback);
        });
    });
});
</script>
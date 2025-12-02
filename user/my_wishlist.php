<?php
include '../includes/db.php';
include 'side.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Handle remove wishlist action (self page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_wishlist_id'])) {
    $wishlist_id = intval($_POST['remove_wishlist_id']);
    $stmt = $con->prepare("DELETE FROM wishlist WHERE wishlist_id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $wishlist_id, $customer_id);
    $stmt->execute();
    $stmt->close();

    // Refresh page
    header("Location: my_wishlists.php");
    exit;
}

// Fetch wishlist items
$stmt = $con->prepare("
    SELECT w.wishlist_id, p.product_id, p.product_name, p.product_price,
           pi.image_url
    FROM wishlist w
    JOIN product p ON w.product_id = p.product_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id
    WHERE w.customer_id = ?
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$wishlistItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Wishlist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 3fr));
            gap: 1.5rem;
            margin-top: 2rem;
            padding: 0 1rem;
        }
        .wishlist-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.2s ease;
        }
        .wishlist-card:hover {
            transform: translateY(-5px);
        }
        .wishlist-img img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .wishlist-info {
            padding: 1rem;
        }
        .wishlist-info h4 {
            margin: 0 0 0.5rem;
            font-size: 1rem;
            color: #333;
        }
        .wishlist-price {
            font-weight: 600;
            color: #ff8fab;
        }
        .wishlist-actions {
            margin-top: 1rem;
            display: flex;
            justify-content: space-between;
        }
        .btn {
            background: #ffb6c1;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn:hover {
            background: #ff8fab;
            color: #fff;
        }
        .remove-wishlist {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h2 style="margin:1rem;">My Wishlist</h2>

    <div class="wishlist-grid">
        <?php if (!empty($wishlistItems)): ?>
            <?php foreach ($wishlistItems as $item): ?>
                <div class="wishlist-card">
                    <div class="wishlist-img">
                        <img src="<?= '../uploads/products/' . htmlspecialchars($item['image_url'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                    </div>
                    <div class="wishlist-info">
                        <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                        <span class="wishlist-price"><?= number_format($item['product_price']) ?> Ks </span>
                        <div class="wishlist-actions">
                            <form action="../shop.php" method="post" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button class="btn" type="submit">Buy</button>
                            </form>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="remove_wishlist_id" value="<?= $item['wishlist_id'] ?>">
                                <button class="remove-wishlist" title="Remove" type="submit">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="padding:1rem;">Your wishlist is empty.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

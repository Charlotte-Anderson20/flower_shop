<?php
require 'includes/db.php';

$search = $_POST['search'] ?? '';
$categories = json_decode($_POST['categories'] ?? '[]', true) ?: [];
$minPrice = $_POST['minPrice'] ?? 0;
$maxPrice = $_POST['maxPrice'] ?? 0;


$where = [];

// Search
if ($search !== '') {
    $s = $con->real_escape_string($search);
    $where[] = "(a.name LIKE '%$s%' OR a.description LIKE '%$s%')";
}

// Categories
if (!empty($categories)) {
    $cats = "'" . implode("','", array_map([$con, 'real_escape_string'], $categories)) . "'";
    $where[] = "a.category IN ($cats)";
}

// Price
if ($minPrice > 0) $where[] = "a.price >= $minPrice";
if ($maxPrice > 0 && $maxPrice < 100000) $where[] = "a.price <= $maxPrice";



// Build query to identify bestsellers
$sql = "SELECT a.*, 
        (SELECT COUNT(*) FROM orders_item oi 
         WHERE oi.aid = a.aid AND oi.item_type = 'accessory') AS is_bestseller
        FROM accessories a";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Order by bestsellers first, then by creation date
$sql .= " ORDER BY is_bestseller DESC, a.created_at DESC";

$result = $con->query($sql);

if ($result->num_rows > 0) {
    while ($accessory = $result->fetch_assoc()) {
        // Only show badge for bestsellers (items with at least 5 sales)
        $badge = $accessory['is_bestseller'] >= 5 ? '<span class="card-badge">Bestseller</span>' : '';
        
        ?>
        <div class="accessory-card animate__animated animate__fadeInUp">
            <div class="card-image">
                <?php if (!empty($accessory['image'])): ?>
                    <img src="../uploads/accessories/<?= htmlspecialchars($accessory['image']) ?>" alt="<?= htmlspecialchars($accessory['name']) ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/500x500?text=No+Image" alt="No image available">
                <?php endif; ?>
                <?= $badge ?>
            </div>
            
            <div class="card-content">
                <h3 class="card-title"><?= htmlspecialchars($accessory['name']) ?></h3>
                <p class="card-description"><?= htmlspecialchars($accessory['description']) ?></p>
                <span class="card-category"><?= htmlspecialchars($accessory['category']) ?></span>
                
                <div class="card-footer">
                    <span class="card-price"><?= number_format($accessory['price']) ?>Ks</span>
                    
                    <button class="add-to-cart" 
                            data-id="<?= $accessory['aid'] ?>" 
                            data-name="<?= htmlspecialchars($accessory['name']) ?>" 
                            data-price="<?= $accessory['price'] ?>" 
                            data-image="<?= htmlspecialchars($accessory['image']) ?>"
                            title="Add to Cart">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo '<div class="no-results animate__animated animate__fadeIn">
            <i class="fas fa-box-open"></i>
            <h3>No accessories found</h3>
            <p>Please check back later for new arrivals</p>
          </div>';
}
?>
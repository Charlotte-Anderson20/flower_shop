<?php
require_once 'includes/db.php';

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

// Ratings filter (optional - needs reviews table)
if (!empty($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    $where[] = "p.product_id IN (
        SELECT product_id
        FROM feedback
        GROUP BY product_id
        HAVING AVG(feedback_rating) >= $rating
    )";
}


$sql = "
    SELECT p.*, at.arrangement_name,
    (SELECT image_url FROM product_images WHERE product_id = p.product_id LIMIT 1) AS product_image
    FROM product p
    JOIN arrangement_type at ON p.arrangement_id = at.arrangement_id
    WHERE p.is_active = 1
";

if ($where) {
    $sql .= " AND " . implode(' AND ', $where);
}

$sql .= " ORDER BY p.date DESC";

$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<div class="product-item">';
        echo '    <div class="product-image">';
        echo '        <img src="uploads/products/' . htmlspecialchars($row['product_image']) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
        echo '    </div>';
        echo '    <div class="product-info">';
        echo '        <h3>' . htmlspecialchars($row['product_name']) . '</h3>';
        echo '        <p class="price">MMK ' . number_format($row['product_price'], 0) . '</p>';
        echo '        <a href="product_detail.php?id=' . $row['product_id'] . '" class="btn">View Details</a>';
        echo '    </div>';
        echo '</div>';
    }
} else {
    echo '<p>No products found.</p>';
}


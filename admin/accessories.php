<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_accessory'])) {
        $aid = isset($_POST['aid']) ? intval($_POST['aid']) : 0;
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $category = trim($_POST['category']);
        $image_url = $_POST['existing_image'] ?? '';
        
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadImage($_FILES['image'], '../uploads/accessories/');
            if ($upload['success']) {
                $image_url = $upload['filename'];
                // Delete old image if exists
                if (!empty($_POST['existing_image']) && file_exists("../uploads/accessories/" . $_POST['existing_image'])) {
                    unlink("../uploads/accessories/" . $_POST['existing_image']);
                }
            } else {
                $message = displayAlert($upload['message'], 'danger');
            }
        }
        
        // Validate required fields
        if (empty($name) || empty($category) || $price <= 0) {
            $message = displayAlert('Name, category and valid price are required', 'danger');
        } else {
            if ($aid > 0) {
                // Update existing accessory
                $stmt = $con->prepare("UPDATE accessories SET name=?, description=?, price=?, image=?, category=?, updated_at=NOW() WHERE aid=?");
                $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $category, $aid);
            } else {
                // Insert new accessory
                $stmt = $con->prepare("INSERT INTO accessories (name, description, price, image, category, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->bind_param("ssdss", $name, $description, $price, $image_url, $category);
            }
            
            if ($stmt->execute()) {
                $message = displayAlert('Accessory ' . ($aid > 0 ? 'updated' : 'added') . ' successfully!', 'success');
                // Redirect to avoid form resubmission
                header("Location: manage_accessories.php");
                exit();
            } else {
                $message = displayAlert('Error saving accessory: ' . $stmt->error, 'danger');
            }
            $stmt->close();
        }
    }
}

// Handle edit request
$accessory = null;
if (isset($_GET['edit'])) {
    $aid = intval($_GET['edit']);
    $result = $con->query("SELECT * FROM accessories WHERE aid = $aid");
    if ($result->num_rows > 0) {
        $accessory = $result->fetch_assoc();
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $aid = intval($_GET['delete']);
    
    // First check if this accessory is used in any orders
    $check = $con->query("SELECT COUNT(*) as count FROM order_items WHERE product_type = 'accessory' AND product_id = $aid");
    $count = $check->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = displayAlert('Cannot delete this accessory as it is associated with orders.', 'danger');
    } else {
        // Get image path before deleting
        $result = $con->query("SELECT image FROM accessories WHERE aid = $aid");
        $row = $result->fetch_assoc();
        $image_path = "../uploads/accessories/" . $row['image'];
        
        if ($con->query("DELETE FROM accessories WHERE aid = $aid")) {
            // Delete the image file if exists
            if (!empty($row['image']) && file_exists($image_path)) {
                unlink($image_path);
            }
            $message = displayAlert('Accessory deleted successfully!', 'success');
            // Redirect to avoid refresh issues
            header("Location: manage_accessories.php");
            exit();
        } else {
            $message = displayAlert('Error deleting accessory: ' . $con->error, 'danger');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accessories - Admin Dashboard</title>
    <link rel="shortcut icon" href="../images/flowerb.png" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .image-preview {
            width: 200px;
            height: 200px;
            border: 1px dashed #ccc;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
            background-color: #f9f9f9;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .image-preview .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
            font-size: 14px;
        }
        
        .category-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            background-color: #e0f7fa;
            color: #00838f;
            font-size: 12px;
            font-weight: 500;
        }
        
        .price-tag {
            font-weight: 600;
            color: #388e3c;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>
<div class="dashboard-container">
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-gift"></i> Manage Accessories</h2>
                <div class="card-actions">
                    <a href="manage_accessories.php?edit=0" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Accessory
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                
                <?php if (isset($_GET['edit'])): ?>
                <div class="form-container slide-in">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="aid" value="<?php echo isset($accessory['aid']) ? $accessory['aid'] : 0; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo isset($accessory['image']) ? $accessory['image'] : ''; ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo isset($accessory['name']) ? htmlspecialchars($accessory['name']) : ''; ?>" 
                                       required minlength="2" maxlength="100">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <input type="text" name="category" class="form-control" 
                                       value="<?php echo isset($accessory['category']) ? htmlspecialchars($accessory['category']) : ''; ?>" 
                                       required minlength="2" maxlength="50">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" maxlength="255"><?php echo isset($accessory['description']) ? htmlspecialchars($accessory['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">Ks</span>
                                <input type="number" name="price" class="form-control" 
                                    value="<?php echo isset($accessory['price']) ? intval($accessory['price']) : '0'; ?>" 
                                    step="1" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Image</label>
                            <div class="image-preview">
                                <?php if (isset($accessory['image']) && !empty($accessory['image'])): ?>
                                    <img src="../uploads/accessories/<?php echo $accessory['image']; ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i> No image selected
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="form-text">Recommended size: 500x500px. Leave blank to keep current image.</small>
                        </div>

                        <div class="form-actions">
                            <a href="manage_accessories.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" name="save_accessory" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo isset($accessory['aid']) ? 'Update' : 'Add'; ?> Accessory
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="table-responsive slide-in" style="animation-delay: 0.2s;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php
    $result = $con->query("SELECT * FROM accessories ORDER BY category, name");
    $counter = 1; // start numbering
    while ($row = $result->fetch_assoc()):
    ?>
    <tr>
        <td><?php echo $counter++; ?></td> <!-- show number instead of real ID -->
        <td>
            <?php if (!empty($row['image'])): ?>
                <img src="../uploads/accessories/<?php echo $row['image']; ?>" 
                     style="width: 50px; height: 50px; object-fit: contain; border-radius: 5px;">
            <?php else: ?>
                <i class="fas fa-gift" style="font-size: 24px; color: var(--primary-color);"></i>
            <?php endif; ?>
        </td>
        <td>
            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
            <?php if (!empty($row['description'])): ?>
                <small class="text-muted d-block">
                    <?php echo substr(htmlspecialchars($row['description']), 0, 50); ?>...
                </small>
            <?php endif; ?>
        </td>
        <td><span class="category-badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
        <td class="price-tag">Ks<?php echo number_format($row['price'], 0); ?></td>
        <td class="table-actions">
            <a href="manage_accessories.php?edit=<?php echo $row['aid']; ?>" 
               class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="manage_accessories.php?delete=<?php echo $row['aid']; ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('Are you sure you want to delete this accessory?')">
                <i class="fas fa-trash"></i> Delete
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';

// Get dropdown options
$arrangements = $con->query("SELECT * FROM Arrangement_Type ORDER BY arrangement_name")->fetch_all(MYSQLI_ASSOC);
$flower_types = $con->query("SELECT * FROM Flower_Type ORDER BY flower_name")->fetch_all(MYSQLI_ASSOC);
$occasions = $con->query("SELECT * FROM Occasions ORDER BY occasion_name")->fetch_all(MYSQLI_ASSOC);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $upload_dir = '../uploads/products/';
    $product_id = intval($_POST['product_id']);
    $product_id = intval($_POST['product_id']);
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
    $product_price = floatval($_POST['product_price']);
    $arrangement_id = intval($_POST['arrangement_id']);
    $size = $_POST['size'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $selected_flower_types = $_POST['flower_types'] ?? [];
    $selected_occasions = $_POST['occasions'] ?? [];
    $errors = [];

    // Basic validation
    if (empty($product_name)) $errors[] = "Product name is required";
    if (empty($product_description)) $errors[] = "Product description is required";
    if ($product_price <= 0) $errors[] = "Product price must be greater than 0";
    if ($arrangement_id <= 0) $errors[] = "Please select an arrangement type";

    if (empty($errors)) {
        // Start transaction
        $con->begin_transaction();
        
        try {
            // Update product details
            $stmt = $con->prepare("UPDATE Product SET product_name=?, product_description=?, product_price=?, arrangement_id=?, size=?, is_active=? WHERE product_id=?");
            $stmt->bind_param("ssdssii", $product_name, $product_description, $product_price, $arrangement_id, $size, $is_active, $product_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error updating product: " . $stmt->error);
            }
            $stmt->close();

            // Update flower types
            $con->query("DELETE FROM Product_Flower_Type WHERE product_id = $product_id");
            if (!empty($selected_flower_types)) {
                $stmt = $con->prepare("INSERT INTO Product_Flower_Type (product_id, flower_type_id) VALUES (?, ?)");
                foreach ($selected_flower_types as $flower_type_id) {
                    $stmt->bind_param("ii", $product_id, $flower_type_id);
                    $stmt->execute();
                }
                $stmt->close();
            }

            // Update occasions
            $con->query("DELETE FROM Product_Occasions WHERE product_id = $product_id");
            if (!empty($selected_occasions)) {
                $stmt = $con->prepare("INSERT INTO Product_Occasions (product_id, occasion_id) VALUES (?, ?)");
                foreach ($selected_occasions as $occasion_id) {
                    $stmt->bind_param("ii", $product_id, $occasion_id);
                    $stmt->execute();
                }
                $stmt->close();
            }

            // Handle new image uploads
            if (!empty($_FILES['product_images']['name'][0])) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $upload_dir = '../uploads/products/';

                foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['product_images']['error'][$key] !== UPLOAD_ERR_OK) {
                        continue; // Skip files with upload errors
                    }

                    // Verify MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp_name);
                    finfo_close($finfo);

                    if (!in_array($mime, $allowed_types)) {
                        throw new Exception("Invalid file type: " . $_FILES['product_images']['name'][$key]);
                    }

                    // Generate unique filename
                    $ext = pathinfo($_FILES['product_images']['name'][$key], PATHINFO_EXTENSION);
                    $new_filename = uniqid('img_') . '.' . $ext;
                    $destination = $upload_dir . $new_filename;

                    if (move_uploaded_file($tmp_name, $destination)) {
                        $stmt_img = $con->prepare("INSERT INTO Product_Images (product_id, image_url) VALUES (?, ?)");
                        $stmt_img->bind_param("is", $product_id, $new_filename);
                        if (!$stmt_img->execute()) {
                            throw new Exception("Failed to save image to database");
                        }
                        $stmt_img->close();
                    } else {
                        throw new Exception("Failed to move uploaded file");
                    }
                }
            }

            // Handle image deletions (only after successful uploads)
            if (!empty($_POST['deleted_images'])) {
                $deleted_ids = array_filter(explode(',', $_POST['deleted_images']), 'is_numeric');
                
                foreach ($deleted_ids as $image_id) {
                    // Get image info before deletion
                    $result = $con->query("SELECT image_url FROM Product_Images WHERE image_id = $image_id AND product_id = $product_id");
                    if ($result->num_rows > 0) {
                        $image = $result->fetch_assoc();
                        $file_path = $upload_dir . $image['image_url'];
                        
                        // Delete from database
                        $con->query("DELETE FROM Product_Images WHERE image_id = $image_id AND product_id = $product_id");
                        
                        // Delete from filesystem
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }

            // Commit transaction if everything succeeded
            $con->commit();
            $message = displayAlert('Product updated successfully!', 'success');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $con->rollback();
            $message = displayAlert('Error: ' . $e->getMessage(), 'danger');
        }
    } else {
        $message = displayAlert(implode('<br>', $errors), 'danger');
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $product_id = intval($_GET['edit']);
    $result = $con->query("SELECT * FROM Product WHERE product_id = $product_id");
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Get flower types for this product
        $flower_result = $con->query("SELECT flower_type_id FROM Product_Flower_Type WHERE product_id = $product_id");
        $product['flower_types'] = array_column($flower_result->fetch_all(MYSQLI_ASSOC), 'flower_type_id');
        
        // Get occasions for this product
        $occasion_result = $con->query("SELECT occasion_id FROM Product_Occasions WHERE product_id = $product_id");
        $product['occasions'] = array_column($occasion_result->fetch_all(MYSQLI_ASSOC), 'occasion_id');
        
        // Get images for this product
        $image_result = $con->query("SELECT * FROM Product_Images WHERE product_id = $product_id ORDER BY date_uploaded");
        $product['images'] = $image_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    // First check if this product is referenced in any orders
    $check = $con->query("SELECT COUNT(*) as count FROM Orders_Item WHERE product_id = $product_id");
    $count = $check->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = displayAlert('Cannot delete this product as it is referenced in existing orders', 'danger');
    } else {
        // Start transaction
        $con->begin_transaction();
        
       try {
    // Get images to delete
    $images_to_delete = [];
    $image_result = $con->query("SELECT image_url FROM Product_Images WHERE product_id = $product_id");
    while ($row = $image_result->fetch_assoc()) {
        $images_to_delete[] = $row['image_url'];
    }

    // Delete child records first (IMPORTANT)
    $con->query("DELETE FROM Product_Flower_Type WHERE product_id = $product_id");
    $con->query("DELETE FROM Product_Occasions WHERE product_id = $product_id");
    $con->query("DELETE FROM Product_Images WHERE product_id = $product_id");
    $con->query("DELETE FROM wishlist WHERE product_id = $product_id");

    // Then delete the product
    if (!$con->query("DELETE FROM Product WHERE product_id = $product_id")) {
        throw new Exception("Error deleting product: " . $con->error);
    }

    // Delete images from server
    foreach ($images_to_delete as $filename) {
        $image_path = "../uploads/products/" . $filename;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Commit transaction
    $con->commit();
    $message = displayAlert('Product deleted successfully!', 'success');

} catch (Exception $e) {
    // Rollback transaction on error
    $con->rollback();
    $message = displayAlert('Error deleting product: ' . $e->getMessage(), 'danger');
}
    }
}

// Initialize counter for displaying numbers instead of IDs
$product_counter = 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Dashboard</title>
  <link rel="shortcut icon" href="../images/flowerb.png" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --success-color: #00b894;
            --danger-color: #d63031;
            --warning-color: #fdcb6e;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            transition: var(--transition);
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            overflow: hidden;
            background: white;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 12px;
            color: var(--primary-color);
        }

        .card-actions .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            padding: 8px 16px;
            display: inline-flex;
            align-items: center;
        }

        .card-actions .btn i {
            margin-right: 8px;
        }

        .card-body {
            padding: 25px;
        }

        /* Success Message Styling */
        .alert-success {
            background-color: rgba(0, 184, 148, 0.1);
            border: 1px solid rgba(0, 184, 148, 0.2);
            color: #007a63;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .alert-success i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Form Styling */
        .form-container {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
        }

        /* Multi-select styling */
        .multi-select-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .multi-select-option {
            padding: 8px 15px;
            background: #f1f1f1;
            border-radius: 20px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .multi-select-option.selected {
            background: var(--primary-color);
            color: white;
        }

        .multi-select-option input[type="checkbox"] {
            margin-right: 8px;
            cursor: pointer;
        }

        /* Image Upload Styling */
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 15px 0;
        }

        .image-preview {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 25px;
            height: 25px;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: var(--transition);
        }

        .image-preview:hover .remove-btn {
            opacity: 1;
        }

        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: var(--transition);
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: var(--transition);
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--success-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            margin-top: 20px;
        }

        /* Table Styling */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table thead th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
            border: none;
        }

        .data-table thead th:first-child {
            border-top-left-radius: var(--border-radius);
        }

        .data-table thead th:last-child {
            border-top-right-radius: var(--border-radius);
        }

        .data-table tbody td {
            padding: 15px;
            background: white;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover td {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 6px 10px;
            font-weight: 500;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .badge-success {
            background-color: rgba(0, 184, 148, 0.1);
            color: var(--success-color);
        }

        .badge-secondary {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .table-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.85rem;
            border-radius: var(--border-radius);
            display: inline-flex;
            align-items: center;
        }

        .btn-sm i {
            font-size: 0.8rem;
            margin-right: 5px;
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        .slide-in {
            animation: slideIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-actions {
                margin-top: 15px;
                width: 100%;
            }
            
            .card-actions .btn {
                width: 100%;
                justify-content: center;
            }
            
            .form-row {
                flex-direction: column;
            }
            
            .form-group.col-md-6, 
            .form-group.col-md-8, 
            .form-group.col-md-4 {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .table-actions {
                flex-direction: column;
                gap: 5px;
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
                    <h2 class="card-title"><i class="fas fa-shopping-bag"></i> Manage Products</h2>
                    <div class="card-actions">
                        <a href="add_product.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(isset($message) && strpos($message, 'successfully') !== false): ?>
                    <div class="alert alert-success fade-in">
                        <i class="fas fa-check-circle"></i>
                        <?php echo strip_tags($message); ?>
                    </div>
                    <?php elseif(isset($message)): ?>
                    <div class="alert alert-danger fade-in">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo strip_tags($message); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['edit']) && isset($product)): ?>
                    <div class="form-container slide-in">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <input type="hidden" name="deleted_images" id="deleted_images" value="">
                            
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" name="product_name" 
                                           value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                                </div>
                                
                                <div class="form-group col-md-4">
                                    <label class="form-label">Price (MMK) *</label>
                                    <input type="number" class="form-control" name="product_price" 
                                           value="<?php echo htmlspecialchars($product['product_price']); ?>" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="product_description" rows="4" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="form-label">Arrangement Type *</label>
                                    <select class="form-control" name="arrangement_id" required>
                                        <option value="">Select Arrangement Type</option>
                                        <?php foreach ($arrangements as $arrangement): ?>
                                            <option value="<?php echo $arrangement['arrangement_id']; ?>" 
                                                <?php echo $arrangement['arrangement_id'] == $product['arrangement_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($arrangement['arrangement_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label class="form-label">Size *</label>
                                    <select class="form-control" name="size" required>
                                        <option value="Small" <?php echo $product['size'] == 'Small' ? 'selected' : ''; ?>>Small</option>
                                        <option value="Medium" <?php echo $product['size'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="Large" <?php echo $product['size'] == 'Large' ? 'selected' : ''; ?>>Large</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Flower Types</label>
                                <div class="multi-select-container" id="flower-type-select">
                                    <?php foreach ($flower_types as $flower): ?>
                                        <label class="multi-select-option <?php echo in_array($flower['flower_type_id'], $product['flower_types']) ? 'selected' : ''; ?>">
                                            <input type="checkbox" name="flower_types[]" value="<?php echo $flower['flower_type_id']; ?>"
                                                <?php echo in_array($flower['flower_type_id'], $product['flower_types']) ? 'checked' : ''; ?>>
                                            <?php echo htmlspecialchars($flower['flower_name']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Occasions</label>
                                <div class="multi-select-container" id="occasion-select">
                                    <?php foreach ($occasions as $occasion): ?>
                                        <label class="multi-select-option <?php echo in_array($occasion['occasion_id'], $product['occasions']) ? 'selected' : ''; ?>">
                                            <input type="checkbox" name="occasions[]" value="<?php echo $occasion['occasion_id']; ?>"
                                                <?php echo in_array($occasion['occasion_id'], $product['occasions']) ? 'checked' : ''; ?>>
                                            <?php echo htmlspecialchars($occasion['occasion_name']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Product Images</label>
                                <div class="image-preview-container" id="image-preview-container">
                                    <?php if (!empty($product['images'])): ?>
                                        <?php foreach ($product['images'] as $image): ?>
                                            <div class="image-preview">
                                                <img src="../uploads/products/<?php echo $image['image_url']; ?>" alt="Product Image">
                                                <button type="button" class="remove-btn" data-image-id="<?php echo $image['image_id']; ?>">&times;</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple style="display: none;">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('product_images').click()">
                                        <i class="fas fa-image"></i> Add Images
                                    </button>
                                    <small class="text-muted">Max 5MB each (JPEG, PNG, GIF, WEBP)</small>
                                </div>
                            </div>
                            
                            <div class="form-group d-flex align-items-center">
                                <label class="form-label me-3">Status</label>
                                <label class="switch me-2">
                                    <input type="checkbox" name="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <span class="text-muted"><?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?></span>
                            </div>
                            
                            <div class="form-actions">
                                <a href="manage_products.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" name="save_product" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive slide-in" style="animation-delay: 0.2s;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Arrangement</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $con->query("
                                    SELECT 
                                        p.*, 
                                        a.arrangement_name,
                                        (SELECT image_url FROM Product_Images pi WHERE pi.product_id = p.product_id ORDER BY pi.date_uploaded DESC LIMIT 1) AS product_image
                                    FROM Product p
                                    JOIN Arrangement_Type a ON p.arrangement_id = a.arrangement_id
                                    ORDER BY p.product_name
                                ");

                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $product_counter; ?></td>
                                    <td>
                                        <?php if (!empty($row['product_image'])): ?>
                                            <img src="../uploads/products/<?php echo $row['product_image']; ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 50px; height: 50px;">
                                                <i class="fas fa-shopping-bag text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td>MMK<?php echo number_format($row['product_price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['arrangement_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="table-actions">
                                        <a href="manage_products.php?edit=<?php echo $row['product_id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="manage_products.php?delete=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                $product_counter++;
                                endwhile; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle image previews and deletions
            const deletedImages = [];
            const deletedInput = document.getElementById('deleted_images');
            
            // Setup file input change listener
            document.getElementById('product_images').addEventListener('change', function(e) {
                const previewContainer = document.getElementById('image-preview-container');
                const files = e.target.files;
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!file.type.match('image.*')) continue;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'image-preview';
                        previewDiv.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="remove-btn">&times;</button>
                        `;
                        
                        previewContainer.appendChild(previewDiv);
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Handle remove buttons (both existing and new images)
            document.getElementById('image-preview-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn')) {
                    const previewDiv = e.target.closest('.image-preview');
                    const imageId = e.target.getAttribute('data-image-id');
                    
                    if (imageId) {
                        deletedImages.push(imageId);
                        deletedInput.value = deletedImages.join(',');
                    }
                    
                    previewDiv.remove();
                }
            });
            
            // Make multi-select options clickable
            document.querySelectorAll('.multi-select-option').forEach(option => {
                option.addEventListener('click', function(e) {
                    if (e.target.tagName === 'INPUT') return;
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    this.classList.toggle('selected');
                });
            });

            // Auto-hide success message after 5 seconds
            const successMessage = document.querySelector('.alert-success');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';
                    setTimeout(() => successMessage.remove(), 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>
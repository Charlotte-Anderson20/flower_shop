<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$id = intval($_GET['id']);
$message = '';

// Get product data
$stmt = $con->prepare("SELECT * FROM Product WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: manage_products.php");
    exit();
}

// Get product images
$image_stmt = $con->prepare("SELECT image_id, image_url FROM product_images WHERE product_id = ?");
$image_stmt->bind_param("i", $id);
$image_stmt->execute();
$image_result = $image_stmt->get_result();
$images = $image_result->fetch_all(MYSQLI_ASSOC);
$image_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
    $product_price = floatval($_POST['product_price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($product_name)) {
        $errors[] = "Product name is required";
    }
    
    if ($product_price <= 0) {
        $errors[] = "Product price must be greater than 0";
    }
    
    if ($stock_quantity < 0) {
        $errors[] = "Stock quantity cannot be negative";
    }
    
    if (empty($errors)) {
        $stmt = $con->prepare("UPDATE Product SET 
            product_name = ?, 
            product_description = ?, 
            product_price = ?, 
            stock_quantity = ?, 
            is_active = ? 
            WHERE product_id = ?");
        
        $stmt->bind_param("ssdiii", 
            $product_name, 
            $product_description, 
            $product_price, 
            $stock_quantity, 
            $is_active, 
            $id);
        
        if ($stmt->execute()) {
            // Handle image deletions
            if (!empty($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    $delete_stmt = $con->prepare("DELETE FROM product_images WHERE image_id = ?");
                    $delete_stmt->bind_param("i", $image_id);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }
            }
            
            // Handle new image uploads
            if (!empty($_FILES['new_images']['name'][0])) {
                $uploads = uploadMultipleImages($_FILES['new_images'], '../uploads/products/');
                foreach ($uploads as $upload) {
                    if ($upload['success']) {
                        $image_stmt = $con->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                        $image_stmt->bind_param("is", $id, $upload['filename']);
                        $image_stmt->execute();
                        $image_stmt->close();
                    }
                }
            }
            
            $message = displayAlert('Product updated successfully!', 'success');
            // Refresh product data
            header("Location: edit_product.php?id=$id");
            exit();
        } else {
            $message = displayAlert('Error updating product: ' . $stmt->error, 'danger');
        }
        $stmt->close();
    } else {
        $message = displayAlert(implode('<br>', $errors), 'danger');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Dashboard</title>
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
        }
        
        .existing-images {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .existing-image {
            position: relative;
            width: 150px;
            height: 150px;
            border: 1px solid #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .existing-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .existing-image label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px;
            text-align: center;
            font-size: 12px;
        }
        
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .image-preview {
            position: relative;
            width: 150px;
            height: 150px;
            border: 1px dashed #ccc;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
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
                    <h2 class="card-title"><i class="fas fa-edit"></i> Edit Product</h2>
                    <div class="card-actions">
                        <a href="manage_products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <div class="form-container slide-in">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="form-label" for="product_name">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" 
                                       value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="product_description">Description</label>
                                <textarea class="form-control" id="product_description" name="product_description" rows="4"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="product_price">Price</label>
                                <input type="number" step="0.01" class="form-control" id="product_price" name="product_price" 
                                       value="<?php echo htmlspecialchars($product['product_price']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="stock_quantity">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                       value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Existing Images</label>
                                <?php if (count($images) > 0): ?>
                                    <div class="existing-images">
                                        <?php foreach ($images as $image): ?>
                                            <div class="existing-image">
                                                <img src="../uploads/products/<?php echo $image['image_url']; ?>">
                                                <label>
                                                    <input type="checkbox" name="delete_images[]" value="<?php echo $image['image_id']; ?>">
                                                    Delete
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p>No images uploaded for this product.</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Add New Images</label>
                                <input type="file" id="new_images" name="new_images[]" class="form-control mb-2" multiple accept="image/*">
                                <small class="text-muted">Select additional images to upload</small>
                                <div id="newImagePreviews" class="image-preview-container"></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <label class="switch">
                                    <input type="checkbox" name="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <span style="margin-left: 10px;"><?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?></span>
                            </div>
                            
                            <div class="form-actions">
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle new image previews
        document.getElementById('new_images').addEventListener('change', function(e) {
            const container = document.getElementById('newImagePreviews');
            container.innerHTML = '';
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                    `;
                    container.appendChild(previewDiv);
                };
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
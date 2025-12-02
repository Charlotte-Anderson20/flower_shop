<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$product = [
    'product_id' => '',
    'product_name' => '',
    'product_description' => '',
    'product_price' => '',
    'arrangement_id' => '',
    'size' => 'Small',
    'flower_types' => [],
    'occasions' => [],
    'is_active' => 1
];

// Get all arrangement types for dropdown
$arrangements = $con->query("SELECT * FROM Arrangement_Type ORDER BY arrangement_name")->fetch_all(MYSQLI_ASSOC);

// Get all flower types for multi-select
$flower_types = $con->query("SELECT * FROM Flower_Type ORDER BY flower_name")->fetch_all(MYSQLI_ASSOC);

// Get all occasions for multi-select
$occasions = $con->query("SELECT * FROM Occasions ORDER BY occasion_name")->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_product'])) {
        $product_name = trim($_POST['product_name']);
        $product_description = trim($_POST['product_description']);
        $product_price = floatval($_POST['product_price']);
        $arrangement_id = intval($_POST['arrangement_id']);
        $size = $_POST['size'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $selected_flower_types = isset($_POST['flower_types']) ? $_POST['flower_types'] : [];
        $selected_occasions = isset($_POST['occasions']) ? $_POST['occasions'] : [];
        $product_id = $_POST['product_id'] ?? 0;
        
        // Validate inputs
        $errors = [];
        
        if (empty($product_name)) {
            $errors[] = "Product name is required";
        }
        
        if (empty($product_description)) {
            $errors[] = "Product description is required";
        }
        
        if ($product_price <= 0) {
            $errors[] = "Product price must be greater than 0";
        }
        
        if ($arrangement_id <= 0) {
            $errors[] = "Please select an arrangement type";
        }
        
        if (empty($errors)) {
            if ($product_id > 0) {
                // Update existing product
                $stmt = $con->prepare("UPDATE Product SET 
                    product_name = ?, 
                    product_description = ?, 
                    product_price = ?, 
                    arrangement_id = ?, 
                    size = ?,
                    is_active = ?
                    WHERE product_id = ?");
                $stmt->bind_param("ssdssii", 
                    $product_name, 
                    $product_description, 
                    $product_price, 
                    $arrangement_id, 
                    $size,
                    $is_active,
                    $product_id);
            } else {
                // Insert new product
                $stmt = $con->prepare("INSERT INTO Product (
                    product_name, 
                    product_description, 
                    product_price, 
                    arrangement_id, 
                    size,
                    is_active
                ) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdssi", 
                    $product_name, 
                    $product_description, 
                    $product_price, 
                    $arrangement_id, 
                    $size,
                    $is_active);
            }
            
            if ($stmt->execute()) {
                if ($product_id == 0) {
                    $product_id = $stmt->insert_id;
                }
                
                // Handle flower types
                $con->query("DELETE FROM Product_Flower_Type WHERE product_id = $product_id");
                foreach ($selected_flower_types as $flower_type_id) {
                    $con->query("INSERT INTO Product_Flower_Type (product_id, flower_type_id) VALUES ($product_id, $flower_type_id)");
                }
                
                // Handle occasions
                $con->query("DELETE FROM Product_Occasions WHERE product_id = $product_id");
                foreach ($selected_occasions as $occasion_id) {
                    $con->query("INSERT INTO Product_Occasions (product_id, occasion_id) VALUES ($product_id, $occasion_id)");
                }
                
                // Handle image uploads
                $image_fields = ['product_image', 'product_image2', 'product_image3'];
                foreach ($image_fields as $field) {
                    if (!empty($_FILES[$field]['name'])) {
                        $upload_result = uploadImage($_FILES[$field]);
                        if ($upload_result['success']) {
                            $con->query("UPDATE Product SET $field = '{$upload_result['filename']}' WHERE product_id = $product_id");
                        } else {
                            $errors[] = "Error uploading $field: " . $upload_result['error'];
                        }
                    }
                }
                
                if (empty($errors)) {
                    $message = displayAlert('Product saved successfully!', 'success');
                    // Reset form if new product
                    if ($_POST['product_id'] == 0) {
                        $product = [
                            'product_id' => '',
                            'product_name' => '',
                            'product_description' => '',
                            'product_price' => '',
                            'arrangement_id' => '',
                            'size' => 'Small',
                            'flower_types' => [],
                            'occasions' => [],
                            'is_active' => 1
                        ];
                    } else {
                        // Reload product data
                        $product_result = $con->query("SELECT * FROM Product WHERE product_id = $product_id");
                        if ($product_result->num_rows > 0) {
                            $product = $product_result->fetch_assoc();
                            
                            // Get flower types for this product
                            $flower_result = $con->query("SELECT flower_type_id FROM Product_Flower_Type WHERE product_id = $product_id");
                            $product['flower_types'] = array_column($flower_result->fetch_all(MYSQLI_ASSOC), 'flower_type_id');
                            
                            // Get occasions for this product
                            $occasion_result = $con->query("SELECT occasion_id FROM Product_Occasions WHERE product_id = $product_id");
                            $product['occasions'] = array_column($occasion_result->fetch_all(MYSQLI_ASSOC), 'occasion_id');
                        }
                    }
                } else {
                    $message = displayAlert(implode('<br>', $errors), 'danger');
                }
            } else {
                $message = displayAlert('Error saving product: ' . $stmt->error, 'danger');
            }
            $stmt->close();
        } else {
            $message = displayAlert(implode('<br>', $errors), 'danger');
        }
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
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    // First check if this product is referenced in any orders
    $check = $con->query("SELECT COUNT(*) as count FROM Orders_Item WHERE product_id = $product_id");
    $count = $check->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = displayAlert('Cannot delete this product as it is referenced in existing orders.', 'danger');
    } else {
        // Get images to delete
        $image_fields = ['product_image', 'product_image2', 'product_image3'];
        $images_to_delete = [];
        
        $product_result = $con->query("SELECT " . implode(', ', $image_fields) . " FROM Product WHERE product_id = $product_id");
        if ($product_result->num_rows > 0) {
            $product_data = $product_result->fetch_assoc();
            foreach ($image_fields as $field) {
                if (!empty($product_data[$field])) {
                    $images_to_delete[] = $product_data[$field];
                }
            }
        }
        
        // Delete from database first
        if ($con->query("DELETE FROM Product WHERE product_id = $product_id")) {
            // Delete associated flower types and occasions
            $con->query("DELETE FROM Product_Flower_Type WHERE product_id = $product_id");
            $con->query("DELETE FROM Product_Occasions WHERE product_id = $product_id");
            
            // Delete from wishlist
            $con->query("DELETE FROM wishlist WHERE product_id = $product_id");
            
            // Delete images from server
            foreach ($images_to_delete as $filename) {
                $image_path = "../uploads/products/" . $filename;
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $message = displayAlert('Product deleted successfully!', 'success');
        } else {
            $message = displayAlert('Error deleting product: ' . $con->error, 'danger');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
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
        .image-preview .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255,0,0,0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .multi-select-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .multi-select-option {
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .multi-select-option.selected {
            background: var(--primary-color);
            color: white;
        }
        .multi-select-option input {
            display: none;
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
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <div class="form-container slide-in">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label class="form-label" for="product_name">Product Name</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" 
                                           value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                                </div>
                                
                                <div class="form-group col-md-4">
                                    <label class="form-label" for="product_price">Price ($)</label>
                                    <input type="number" class="form-control" id="product_price" name="product_price" 
                                           value="<?php echo htmlspecialchars($product['product_price']); ?>" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="product_description">Description</label>
                                <textarea class="form-control" id="product_description" name="product_description" rows="4" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="form-label" for="arrangement_id">Arrangement Type</label>
                                    <select class="form-control" id="arrangement_id" name="arrangement_id" required>
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
                                    <label class="form-label" for="size">Size</label>
                                    <select class="form-control" id="size" name="size" required>
                                        <option value="Small" <?php echo $product['size'] == 'Small' ? 'selected' : ''; ?>>Small</option>
                                        <option value="Medium" <?php echo $product['size'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="Large" <?php echo $product['size'] == 'Large' ? 'selected' : ''; ?>>Large</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Flower Types</label>
                                <div class="multi-select-container">
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
                                <div class="multi-select-container">
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
                                <div class="image-preview-container">
                                    <!-- Main Image -->
                                    <div class="image-preview">
                                        <?php if (!empty($product['product_image'])): ?>
                                            <img src="../uploads/products/<?php echo $product['product_image']; ?>" alt="Main Image">
                                            <button type="button" class="remove-btn" data-field="product_image">&times;</button>
                                        <?php else: ?>
                                            <div class="no-image">Main Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" id="product_image" name="product_image" accept="image/*" style="display: none;">
                                    
                                    <!-- Secondary Image 1 -->
                                    <div class="image-preview">
                                        <?php if (!empty($product['product_image2'])): ?>
                                            <img src="../uploads/products/<?php echo $product['product_image2']; ?>" alt="Secondary Image 1">
                                            <button type="button" class="remove-btn" data-field="product_image2">&times;</button>
                                        <?php else: ?>
                                            <div class="no-image">Secondary Image 1</div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" id="product_image2" name="product_image2" accept="image/*" style="display: none;">
                                    
                                    <!-- Secondary Image 2 -->
                                    <div class="image-preview">
                                        <?php if (!empty($product['product_image3'])): ?>
                                            <img src="../uploads/products/<?php echo $product['product_image3']; ?>" alt="Secondary Image 2">
                                            <button type="button" class="remove-btn" data-field="product_image3">&times;</button>
                                        <?php else: ?>
                                            <div class="no-image">Secondary Image 2</div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" id="product_image3" name="product_image3" accept="image/*" style="display: none;">
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('product_image').click()">
                                        <i class="fas fa-image"></i> Main Image
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('product_image2').click()">
                                        <i class="fas fa-image"></i> Secondary 1
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('product_image3').click()">
                                        <i class="fas fa-image"></i> Secondary 2
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <label class="switch">
                                    <input type="checkbox" name="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <span style="margin-left: 10px;"><?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?></span>
                            </div>
                            
                            <button type="submit" name="save_product" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Product
                            </button>
                            
                            <?php if (!empty($product['product_id'])): ?>
                                <a href="manage_products.php" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div class="table-responsive slide-in" style="animation-delay: 0.2s;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
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
                                    SELECT p.*, a.arrangement_name 
                                    FROM Product p
                                    JOIN Arrangement_Type a ON p.arrangement_id = a.arrangement_id
                                    ORDER BY p.product_name
                                ");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $row['product_id']; ?></td>
                                    <td>
                                        <?php if (!empty($row['product_image'])): ?>
                                            <img src="../uploads/products/<?php echo $row['product_image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <i class="fas fa-shopping-bag" style="font-size: 24px; color: var(--primary-color);"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td>$<?php echo number_format($row['product_price'], 2); ?></td>
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
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle image previews
        document.addEventListener('DOMContentLoaded', function() {
            // Setup file input change listeners
            document.getElementById('product_image').addEventListener('change', function(e) {
                handleImagePreview(e, 'product_image');
            });
            
            document.getElementById('product_image2').addEventListener('change', function(e) {
                handleImagePreview(e, 'product_image2');
            });
            
            document.getElementById('product_image3').addEventListener('change', function(e) {
                handleImagePreview(e, 'product_image3');
            });
            
            // Setup remove button listeners
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const field = this.getAttribute('data-field');
                    const preview = this.closest('.image-preview');
                    
                    // Clear the file input
                    document.getElementById(field).value = '';
                    
                    // Reset the preview
                    preview.innerHTML = field === 'product_image' ? 
                        '<div class="no-image">Main Image</div>' : 
                        '<div class="no-image">Secondary Image</div>';
                    
                    // If editing, we need to mark this image for deletion
                    if (<?php echo !empty($product['product_id']) ? 'true' : 'false'; ?>) {
                        // In a real implementation, you might add a hidden input to track images to delete
                        console.log('Marking image for deletion:', field);
                    }
                });
            });
            
            // Make multi-select options clickable
            document.querySelectorAll('.multi-select-option').forEach(option => {
                option.addEventListener('click', function() {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    this.classList.toggle('selected');
                });
            });
        });
        
        function handleImagePreview(event, fieldName) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.querySelector(`.image-preview-container`);
                let previewDiv = previewContainer.querySelector(`.image-preview:has(input[name="${fieldName}"])`);
                
                if (!previewDiv) {
                    previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview';
                    previewContainer.appendChild(previewDiv);
                }
                
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-btn" data-field="${fieldName}">&times;</button>
                `;
                
                // Add event listener to the new remove button
                previewDiv.querySelector('.remove-btn').addEventListener('click', function() {
                    document.getElementById(fieldName).value = '';
                    previewDiv.innerHTML = fieldName === 'product_image' ? 
                        '<div class="no-image">Main Image</div>' : 
                        '<div class="no-image">Secondary Image</div>';
                });
            };
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>
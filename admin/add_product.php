<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch dropdown options
$arrangements = [];
$occasions = [];
$flower_types = [];

$result = $con->query("SELECT arrangement_id, arrangement_name FROM arrangement_type");
while ($row = $result->fetch_assoc()) {
    $arrangements[] = $row;
}
$result = $con->query("SELECT occasion_id, occasion_name FROM occasions");
while ($row = $result->fetch_assoc()) {
    $occasions[] = $row;
}

$result = $con->query("SELECT flower_type_id, flower_name FROM flower_type");
while ($row = $result->fetch_assoc()) {
    $flower_types[] = $row;
}

$message = '';
$product = [
    'product_name' => '',
    'product_description' => '',
    'product_price' => '0',
    'arrangement_id' => '', // Changed from array to single value
    'occasion_ids' => [],
    'flower_type_ids' => [],
    'size' => 'Small',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
    $product_price = floatval($_POST['product_price']);
    $arrangement_id = isset($_POST['arrangement_id']) ? intval($_POST['arrangement_id']) : 0;
    $occasion_ids = isset($_POST['occasion_ids']) ? array_map('intval', $_POST['occasion_ids']) : [];
    $flower_type_ids = isset($_POST['flower_type_ids']) ? array_map('intval', $_POST['flower_type_ids']) : [];
    $size = $_POST['size'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    // Strong validations
    if (empty($product_name)) {
        $errors[] = "Product name is required";
    } elseif (strlen($product_name) > 255) {
        $errors[] = "Product name must be less than 255 characters";
    }
    
    if (empty($product_description)) {
        $errors[] = "Product description is required";
    } elseif (strlen($product_description) > 2000) {
        $errors[] = "Product description must be less than 2000 characters";
    }
    
   if ($product_price < 0) {
    $errors[] = "Product price cannot be negative";
} elseif ($product_price > 100000000) { // e.g., 100 million MMK limit
    $errors[] = "Product price must be less than 100,000,000 MMK";
}

    
    if (empty($arrangement_id)) {
        $errors[] = "An arrangement type is required";
    }
    
    if (empty($occasion_ids)) {
        $errors[] = "At least one occasion is required";
    }
    
    if (empty($flower_type_ids)) {
        $errors[] = "At least one flower type is required";
    }
    
    if (!in_array($size, ['Small', 'Medium', 'Large'])) {
        $errors[] = "Invalid size selected";
    }
    
    // Image validation
    if (!empty($_FILES['images']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_type = mime_content_type($tmp_name);
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Only JPG, PNG, GIF, and WebP images are allowed";
                break;
            }
            
            if ($_FILES['images']['size'][$key] > 5 * 1024 * 1024) { // 5MB
                $errors[] = "Each image must be less than 5MB";
                break;
            }
        }
    }
    
    if (empty($errors)) {
        $con->begin_transaction();
        
        try {
            $stmt = $con->prepare("INSERT INTO Product (
                product_name, 
                product_description, 
                product_price, 
                arrangement_id,
                size,
                is_active
            ) VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssdiss", 
                $product_name, 
                $product_description, 
                $product_price,
                $arrangement_id,
                $size,
                $is_active);
            
            if (!$stmt->execute()) {
                throw new Exception("Error adding product: " . $stmt->error);
            }
            
            $product_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert occasions
            $stmt = $con->prepare("INSERT INTO product_occasions (product_id, occasion_id) VALUES (?, ?)");
            foreach ($occasion_ids as $occasion_id) {
                $stmt->bind_param("ii", $product_id, $occasion_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding occasion: " . $stmt->error);
                }
            }
            $stmt->close();
            
            // Insert flower types
            $stmt = $con->prepare("INSERT INTO product_flower_type (product_id, flower_type_id) VALUES (?, ?)");
            foreach ($flower_type_ids as $flower_type_id) {
                $stmt->bind_param("ii", $product_id, $flower_type_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding flower type: " . $stmt->error);
                }
            }
            $stmt->close();
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploads = uploadMultipleImages($_FILES['images'], '../uploads/products/');
                foreach ($uploads as $upload) {
                    if ($upload['success']) {
                        $image_stmt = $con->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                        $image_stmt->bind_param("is", $product_id, $upload['filename']);
                        if (!$image_stmt->execute()) {
                            throw new Exception("Error adding image: " . $image_stmt->error);
                        }
                        $image_stmt->close();
                    } else {
                        throw new Exception("Image upload failed: " . $upload['error']);
                    }
                }
            }
            
            $con->commit();
            $message = displayAlert('Product added successfully!', 'success');
            
            // Reset form
            $product = [
                'product_name' => '',
                'product_description' => '',
                'product_price' => '0',
                'arrangement_id' => '',
                'occasion_ids' => [],
                'flower_type_ids' => [],
                'size' => 'Small',
                'is_active' => 1
            ];
        } catch (Exception $e) {
            $con->rollback();
            $message = displayAlert($e->getMessage(), 'danger');
            
            // Preserve user input
            $product = [
                'product_name' => $product_name,
                'product_description' => $product_description,
                'product_price' => $product_price,
                'arrangement_id' => $arrangement_id,
                'occasion_ids' => $occasion_ids,
                'flower_type_ids' => $flower_type_ids,
                'size' => $size,
                'is_active' => $is_active
            ];
        }
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
    <title>Add Product - Admin Dashboard</title>
  <link rel="shortcut icon" href="../images/flowerb.png" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        
        #add-more-images {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #add-more-images:hover {
            background-color: #218838;
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }

        .image-preview {
            position: relative;
            width: 150px;
            height: 150px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .image-preview:hover {
            transform: scale(1.05);
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
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-col {
            flex: 1;
        }
        
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .is-invalid .select2-container--default .select2-selection--multiple {
            border-color: #dc3545;
        }
        
        /* Ratio selection box styles */
        .ratio-selection {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
        }
        
        .ratio-option {
            position: relative;
            flex: 1;
            min-width: 100px;
        }
        
        .ratio-option input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .ratio-option label {
            display: block;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .ratio-option input[type="checkbox"]:checked + label {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .ratio-option input[type="checkbox"]:focus + label {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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
                    <h2 class="card-title"><i class="fas fa-plus-circle"></i> Add New Product</h2>
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
                                <textarea class="form-control" id="product_description" name="product_description" rows="4" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label class="form-label" for="product_price">Price (MMK)</label>
                                        <input type="number" step="1" min="0" class="form-control" id="product_price" name="product_price" 
                                            value="<?php echo htmlspecialchars($product['product_price']); ?>" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label class="form-label" for="arrangement_id">Arrangement Type</label>
                                        <select class="form-control" id="arrangement_id" name="arrangement_id" required>
                                            <option value="">Select Arrangement</option>
                                            <?php foreach ($arrangements as $arrangement): ?>
                                            <option value="<?= $arrangement['arrangement_id'] ?>" <?= $arrangement['arrangement_id'] == $product['arrangement_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($arrangement['arrangement_name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label class="form-label">Occasion</label>
                                        <div class="ratio-selection">
                                            <?php foreach ($occasions as $occasion): ?>
                                                <div class="ratio-option">
                                                    <input type="checkbox" id="occasion_<?= $occasion['occasion_id'] ?>" 
                                                           name="occasion_ids[]" value="<?= $occasion['occasion_id'] ?>"
                                                           <?= in_array($occasion['occasion_id'], $product['occasion_ids']) ? 'checked' : '' ?>>
                                                    <label for="occasion_<?= $occasion['occasion_id'] ?>">
                                                        <?= htmlspecialchars($occasion['occasion_name']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label class="form-label">Flower Type</label>
                                        <div class="ratio-selection">
                                            <?php foreach ($flower_types as $flower): ?>
                                                <div class="ratio-option">
                                                    <input type="checkbox" id="flower_<?= $flower['flower_type_id'] ?>" 
                                                           name="flower_type_ids[]" value="<?= $flower['flower_type_id'] ?>"
                                                           <?= in_array($flower['flower_type_id'], $product['flower_type_ids']) ? 'checked' : '' ?>>
                                                    <label for="flower_<?= $flower['flower_type_id'] ?>">
                                                        <?= htmlspecialchars($flower['flower_name']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label class="form-label" for="size">Size</label>
                                        <select class="form-control" id="size" name="size" required>
                                            <option value="Small" <?php echo ($product['size'] == 'Small') ? 'selected' : ''; ?>>Small</option>
                                            <option value="Medium" <?php echo ($product['size'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                            <option value="Large" <?php echo ($product['size'] == 'Large') ? 'selected' : ''; ?>>Large</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Product Images</label>
                                <div id="image-inputs">
                                    <input type="file" name="images[]" class="form-control mb-2" accept="image/*">
                                </div>
                                <button type="button" id="add-more-images" class="btn btn-success btn-sm mt-2">
                                    <i class="fas fa-plus"></i> Add More Images
                                </button>
                                <small class="text-muted d-block mt-2">You can upload multiple images (Max 5MB each)</small>
                                <div id="imagePreviews" class="image-preview-container"></div>
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
                                    <i class="fas fa-save"></i> Save Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add more image input fields
            $('#add-more-images').click(function () {
                const container = $('#image-inputs');
                const newInput = $('<input>').attr({
                    type: 'file',
                    name: 'images[]',
                    class: 'form-control mb-2',
                    accept: 'image/*'
                });

                // Add event for preview
                newInput.on('change', previewImages);

                container.append(newInput);
            });

            // Preview function for all image inputs
            function previewImages() {
                const container = $('#imagePreviews');
                container.empty(); // Clear previous previews

                $('input[name="images[]"]').each(function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            const previewDiv = $('<div>').addClass('image-preview');
                            previewDiv.html(`<img src="${e.target.result}" alt="Preview">`);
                            container.append(previewDiv);
                        };

                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Initial image input preview listener
            $('input[name="images[]"]').first().on('change', previewImages);
            
            // Form validation
            $('form').submit(function(e) {
                let valid = true;
                
                // Validate occasions
                if ($('input[name="occasion_ids[]"]:checked').length === 0) {
                    valid = false;
                    $('.ratio-selection:first').css('border', '1px solid #dc3545').css('padding', '5px').css('border-radius', '4px');
                } else {
                    $('.ratio-selection:first').css('border', '').css('padding', '').css('border-radius', '');
                }
                
                // Validate flower types
                if ($('input[name="flower_type_ids[]"]:checked').length === 0) {
                    valid = false;
                    $('.ratio-selection:last').css('border', '1px solid #dc3545').css('padding', '5px').css('border-radius', '4px');
                } else {
                    $('.ratio-selection:last').css('border', '').css('padding', '').css('border-radius', '');
                }
                
                // Validate single select
                if ($('#arrangement_id').val() === '') {
                    valid = false;
                    $('#arrangement_id').addClass('is-invalid');
                } else {
                    $('#arrangement_id').removeClass('is-invalid');
                }
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill all required fields');
                }
            });
        });
    </script>
</body>
</html>
<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$flower = [
    'flower_type_id' => '',
    'flower_name' => '',
    'image_url' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flower_name = trim($_POST['flower_name']);
    $image_url = null;
    
    // Validate inputs
    $errors = [];
    
    // Flower name validation
    if (empty($flower_name)) {
        $errors[] = "Flower name is required";
    } elseif (strlen($flower_name) < 2 || strlen($flower_name) > 100) {
        $errors[] = "Flower name must be between 2 and 100 characters";
    } else {
        // Check for duplicate flower names (case-insensitive)
        $check_stmt = $con->prepare("SELECT flower_type_id FROM flower_type WHERE LOWER(flower_name) = LOWER(?)");
        $check_stmt->bind_param("s", $flower_name);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $errors[] = "A flower type with this name already exists";
        }
        $check_stmt->close();
    }
    
    // Image validation
    if (!empty($_FILES['main_image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if ($_FILES['main_image']['size'] > $max_size) {
            $errors[] = "Image size must be less than 2MB";
        }
        
        if (!in_array($_FILES['main_image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, GIF, and WEBP images are allowed";
        }
        
        // Check for valid image file
        if (!getimagesize($_FILES['main_image']['tmp_name'])) {
            $errors[] = "Uploaded file is not a valid image";
        }
    } else {
        $errors[] = "Main image is required";
    }
    
    if (empty($errors)) {
        // Handle image upload
        if (!empty($_FILES['main_image']['name'])) {
            $upload = uploadImage($_FILES['main_image'], '../uploads/flowers/');
            if ($upload['success']) {
                $image_url = $upload['filename'];
            } else {
                $errors[] = "Error uploading image: " . $upload['message'];
            }
        }
        
        if (empty($errors)) {
            // Insert new flower
            $stmt = $con->prepare("INSERT INTO flower_type (flower_name, image_url) VALUES (?, ?)");
            $stmt->bind_param("ss", $flower_name, $image_url);
            
            if ($stmt->execute()) {
                $flower_id = $stmt->insert_id;
                $message = displayAlert('Flower type added successfully!', 'success');
                // Reset form
                $flower = [
                    'flower_type_id' => '',
                    'flower_name' => '',
                    'image_url' => ''
                ];
                
                // Clear the file input after successful submission
                echo '<script>document.getElementById("main_image").value = "";</script>';
            } else {
                $message = displayAlert('Error adding flower type: ' . $stmt->error, 'danger');
            }
            $stmt->close();
        } else {
            $message = displayAlert(implode('<br>', $errors), 'danger');
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
    <title>Add Flower Type - Admin Dashboard</title>
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
        
        .image-preview .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            color: #999;
            font-size: 14px;
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
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .invalid-feedback {
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
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
                    <h2 class="card-title"><i class="fas fa-plus-circle"></i> Add New Flower Type</h2>
                    <div class="card-actions">
                        <a href="manage_flowers.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Flowers
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <div class="form-container slide-in">
                        <form method="POST" enctype="multipart/form-data" id="flowerForm">
                            <div class="form-group">
                                <label class="form-label" for="flower_name">Flower Name *</label>
                                <input type="text" class="form-control" id="flower_name" name="flower_name" 
                                       value="<?php echo htmlspecialchars($flower['flower_name']); ?>" 
                                       required
                                       minlength="2"
                                       maxlength="100"
                                       pattern="[A-Za-z0-9\s\-]+"
                                       title="Only letters, numbers, spaces and hyphens are allowed">
                                <small class="form-text">Must be 2-100 characters, unique, and can contain letters, numbers, spaces and hyphens</small>
                                <div class="invalid-feedback" id="flower_name_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Main Image *</label>
                                <div class="image-preview-container">
                                    <div class="image-preview">
                                        <div class="no-image">No image selected</div>
                                    </div>
                                    <input type="file" id="main_image" name="main_image" accept="image/*" style="display: none;" required>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('main_image').click()">
                                    <i class="fas fa-image"></i> Select Image (Max 2MB)
                                </button>
                                <small class="form-text">Allowed formats: JPG, PNG, GIF, WEBP</small>
                                <div class="invalid-feedback" id="main_image_error"></div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Flower Type
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script>
        // Handle image preview for main image
        document.getElementById('main_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.querySelector('.image-preview-container');
            let previewDiv = previewContainer.querySelector('.image-preview');
            
            // Reset error state
            this.classList.remove('is-invalid');
            document.getElementById('main_image_error').textContent = '';
            
            if (!file) {
                previewDiv.innerHTML = '<div class="no-image">No image selected</div>';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                this.classList.add('is-invalid');
                document.getElementById('main_image_error').textContent = 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.';
                this.value = '';
                previewDiv.innerHTML = '<div class="no-image">No image selected</div>';
                return;
            }
            
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                this.classList.add('is-invalid');
                document.getElementById('main_image_error').textContent = 'File size must be less than 2MB';
                this.value = '';
                previewDiv.innerHTML = '<div class="no-image">No image selected</div>';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-btn" data-field="main_image">&times;</button>
                `;
                
                // Add event listener to the remove button
                previewDiv.querySelector('.remove-btn').addEventListener('click', function() {
                    document.getElementById('main_image').value = '';
                    previewDiv.innerHTML = '<div class="no-image">No image selected</div>';
                });
            };
            reader.readAsDataURL(file);
        });
        
        // Form validation
        document.getElementById('flowerForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate flower name
            const flowerName = document.getElementById('flower_name');
            if (flowerName.value.trim().length < 2 || flowerName.value.trim().length > 100) {
                flowerName.classList.add('is-invalid');
                document.getElementById('flower_name_error').textContent = 'Flower name must be 2-100 characters';
                isValid = false;
            } else {
                flowerName.classList.remove('is-invalid');
                document.getElementById('flower_name_error').textContent = '';
            }
            
            // Validate image
            const mainImage = document.getElementById('main_image');
            if (!mainImage.files || mainImage.files.length === 0) {
                mainImage.classList.add('is-invalid');
                document.getElementById('main_image_error').textContent = 'Main image is required';
                isValid = false;
            } else {
                mainImage.classList.remove('is-invalid');
                document.getElementById('main_image_error').textContent = '';
            }
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to the first error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // Reset form completely
        function resetForm() {
            document.getElementById('flowerForm').reset();
            document.querySelector('.image-preview').innerHTML = '<div class="no-image">No image selected</div>';
            // Remove error states
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }
        
        // Live validation for flower name
        document.getElementById('flower_name').addEventListener('input', function() {
            if (this.value.trim().length >= 2 && this.value.trim().length <= 100) {
                this.classList.remove('is-invalid');
                document.getElementById('flower_name_error').textContent = '';
            }
        });
    </script>
</body>
</html>
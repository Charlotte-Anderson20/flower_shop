<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Get flower ID from URL
$flower_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($flower_id <= 0) {
    header("Location: manage_flowers.php");
    exit();
}

// Get flower data
$flower_result = $con->query("SELECT * FROM flower_type WHERE flower_type_id = $flower_id");
if ($flower_result->num_rows === 0) {
    header("Location: manage_flowers.php");
    exit();
}

$flower = $flower_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flower_name = trim($_POST['flower_name']);
    $image_url = $_POST['existing_image'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($flower_name)) {
        $errors[] = "Flower name is required";
    }
    
    if (empty($errors)) {
        // Handle main image upload
        if (!empty($_FILES['main_image']['name'])) {
            $upload = uploadImage($_FILES['main_image'], '../uploads/flowers/');
            if ($upload['success']) {
                $image_url = $upload['filename'];
                // Delete old image if it exists
                if (!empty($_POST['existing_image']) && file_exists("../uploads/flowers/" . $_POST['existing_image'])) {
                    unlink("../uploads/flowers/" . $_POST['existing_image']);
                }
            } else {
                $errors[] = "Error uploading image: " . $upload['message'];
            }
        }
        
        if (empty($errors)) {
            // Update flower
            $stmt = $con->prepare("UPDATE flower_type SET 
                flower_name = ?, 
                image_url = ?
                WHERE flower_type_id = ?");
            $stmt->bind_param("ssi", 
                $flower_name, 
                $image_url,
                $flower_id);
            
            if ($stmt->execute()) {
                $message = displayAlert('Flower type updated successfully!', 'success');
                // Reload flower data
                $flower_result = $con->query("SELECT * FROM flower_type WHERE flower_type_id = $flower_id");
                $flower = $flower_result->fetch_assoc();
            } else {
                $message = displayAlert('Error updating flower type: ' . $stmt->error, 'danger');
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
    <title>Edit Flower Type - Admin Dashboard</title>
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
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="card fade-in">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-edit"></i> Edit Flower Type</h2>
                    <div class="card-actions">
                        <a href="manage_flowers.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Flowers
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <div class="form-container slide-in">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="flower_type_id" value="<?php echo $flower['flower_type_id']; ?>">
                            <input type="hidden" name="existing_image" value="<?php echo $flower['image_url']; ?>">
                            
                            <div class="form-group">
                                <label class="form-label" for="flower_name">Flower Name</label>
                                <input type="text" class="form-control" id="flower_name" name="flower_name" 
                                       value="<?php echo htmlspecialchars($flower['flower_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Main Image</label>
                                <div class="image-preview-container">
                                    <div class="image-preview">
                                        <?php if (!empty($flower['image_url'])): ?>
                                            <img src="../uploads/flowers/<?php echo $flower['image_url']; ?>" alt="Main Image">
                                            <button type="button" class="remove-btn" data-field="main_image">&times;</button>
                                        <?php else: ?>
                                            <div class="no-image">Main Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" id="main_image" name="main_image" accept="image/*" style="display: none;">
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('main_image').click()">
                                    <i class="fas fa-image"></i> Change Image
                                </button>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Flower Type
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle image preview for main image
        document.getElementById('main_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.querySelector('.image-preview-container');
                let previewDiv = previewContainer.querySelector('.image-preview');
                
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-btn" data-field="main_image">&times;</button>
                `;
                
                // Add event listener to the remove button
                previewDiv.querySelector('.remove-btn').addEventListener('click', function() {
                    document.getElementById('main_image').value = '';
                    previewDiv.innerHTML = '<div class="no-image">Main Image</div>';
                });
            };
            reader.readAsDataURL(file);
        });
        
        // Setup remove button for existing main image
        document.querySelector('.image-preview .remove-btn')?.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove this image?')) {
                document.getElementById('main_image').value = '';
                this.closest('.image-preview').innerHTML = '<div class="no-image">Main Image</div>';
                
                // Send request to delete the image from server
                fetch('delete_flower_main_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `flower_id=<?php echo $flower['flower_type_id']; ?>`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Error deleting image:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    </script>
</body>
</html>
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
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'title' => 'Upload Error',
                    'message' => $upload['message'],
                    'duration' => 5000
                ];
            }
        }
        
        // Validate required fields
        if (empty($name) || empty($category) || $price <= 0) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'title' => 'Validation Error',
                'message' => 'Name, category and valid price are required',
                'duration' => 5000
            ];
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
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'title' => 'Success',
                    'message' => 'Accessory ' . ($aid > 0 ? 'updated' : 'added') . ' successfully!',
                    'duration' => 3000
                ];
                // Redirect to avoid form resubmission
                header("Location: manage_accessories.php");
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'title' => 'Database Error',
                    'message' => 'Error saving accessory: ' . $stmt->error,
                    'duration' => 5000
                ];
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
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Deletion Error',
            'message' => 'Cannot delete this accessory as it is associated with orders.',
            'duration' => 5000
        ];
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
            $_SESSION['notification'] = [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Accessory deleted successfully!',
                'duration' => 3000
            ];
            // Redirect to avoid refresh issues
            header("Location: manage_accessories.php");
            exit();
        } else {
            $_SESSION['notification'] = [
                'type' => 'error',
                'title' => 'Database Error',
                'message' => 'Error deleting accessory: ' . $con->error,
                'duration' => 5000
            ];
        }
    }
}

// Check for notification in session
if (isset($_SESSION['notification'])) {
    $message = $_SESSION['notification'];
    unset($_SESSION['notification']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accessories - Admin Dashboard</title>
    <link rel="shortcut icon" href="../images/flowerb.png" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
    :root {
        --primary-color: #e83e8c;
        --primary-light: #ffcce0;
        --secondary-color: #ff85a2;
        --accent-color: #ff4785;
        --light-color: #fff5f7;
        --dark-color: #495057;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --border-radius: 12px;
        --box-shadow: 0 4px 20px rgba(232, 62, 140, 0.15);
        --transition: all 0.3s ease;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #fff9fb;
        color: var(--dark-color);
        line-height: 1.6;
    }
    
    .main-content {
        padding: 2rem;
        animation: fadeIn 0.5s ease;
    }
    
    h2 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        font-weight: 600;
        position: relative;
        padding-bottom: 0.5rem;
    }
    
    h2:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(to right, var(--primary-color), var(--accent-color));
        border-radius: 3px;
    }
    
    .card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: none;
        transition: var(--transition);
    }
    
    .card:hover {
        box-shadow: 0 8px 25px rgba(232, 62, 140, 0.2);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f8e1e7;
    }
    
    .card-title {
        font-size: 1.5rem;
        margin: 0;
    }
    
    .card-title i {
        color: var(--primary-color);
        margin-right: 10px;
    }
    
    .accessories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    
    .accessory-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        transition: var(--transition);
        border: 1px solid #f8e1e7;
    }
    
    .accessory-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(232, 62, 140, 0.2);
    }
    
    .accessory-image {
        height: 200px;
        background-color: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .accessory-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 20px;
    }
    
    .accessory-image i {
        font-size: 3rem;
        color: var(--primary-color);
    }
    
    .accessory-details {
        padding: 1.5rem;
    }
    
    .accessory-name {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--dark-color);
    }
    
    .accessory-category {
        display: inline-block;
        background-color: var(--primary-light);
        color: var(--primary-color);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-bottom: 0.75rem;
    }
    
    .accessory-description {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .accessory-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }
    
    .accessory-price {
        font-weight: 700;
        color: var(--primary-color);
        font-size: 1.1rem;
    }
    
    .accessory-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        border-top: 1px solid #f8e1e7;
        padding-top: 1rem;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        font-family: 'Poppins', sans-serif;
        font-size: 0.85rem;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #d2317a;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(232, 62, 140, 0.2);
    }
    
    .btn-danger {
        background-color: var(--danger-color);
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
    }
    
    .btn-edit {
        background-color: var(--secondary-color);
        color: white;
    }
    
    .btn-edit:hover {
        background-color: #ff6b8b;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 133, 162, 0.2);
    }
    
    .form-container {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 2rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark-color);
    }
    
    input, select, textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #f0c6d4;
        border-radius: var(--border-radius);
        font-family: 'Poppins', sans-serif;
        transition: var(--transition);
        background-color: #fff9fb;
    }
    
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(232, 62, 140, 0.2);
    }
    
    .image-preview {
        width: 150px;
        height: 150px;
        border: 2px dashed #f0c6d4;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        overflow: hidden;
        background-color: #fff9fb;
    }
    
    .image-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .no-image {
        color: #aaa;
        text-align: center;
        padding: 1rem;
    }
    
    .no-image i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        color: var(--primary-light);
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .floating-action-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(232, 62, 140, 0.3);
        transition: var(--transition);
        z-index: 100;
    }
    
    .floating-action-btn:hover {
        transform: scale(1.1) rotate(90deg);
        background-color: var(--accent-color);
    }
    
    .notification {
        position: fixed;
        top: 1rem;
        right: 1rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        max-width: 350px;
        z-index: 1000;
        border-left: 4px solid;
    }
    
    .notification.success {
        border-left-color: var(--success-color);
    }
    
    .notification.error {
        border-left-color: var(--danger-color);
    }
    
    .notification i {
        font-size: 1.5rem;
    }
    
    .notification.success i {
        color: var(--success-color);
    }
    
    .notification.error i {
        color: var(--danger-color);
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .accessories-grid {
            grid-template-columns: 1fr;
        }
        
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
</style>
</head>
<body>
<?php include 'admin_header.php'; ?>
<div class="dashboard-container">
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <!-- Notification Container -->
        <?php if (!empty($message)): ?>
        <div class="notification <?php echo $message['type']; ?> animate__animated animate__fadeInRight">
            <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <div class="notification-content">
                <div class="notification-title"><?php echo htmlspecialchars($message['title']); ?></div>
                <div><?php echo htmlspecialchars($message['message']); ?></div>
            </div>
        </div>
        <script>
            // Auto-hide notification after duration
            setTimeout(() => {
                document.querySelector('.notification').classList.add('animate__fadeOutRight');
            }, <?php echo $message['duration']; ?>);
        </script>
        <?php endif; ?>
        
        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-gift"></i> Manage Accessories</h2>
                <div class="card-actions">
                    <a href="manage_accessories.php?edit=0" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Accessory
                    </a>
                </div>
            </div>
            
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
                                value="<?php echo isset($accessory['price']) ? $accessory['price'] : '0'; ?>" 
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

            <div class="accessories-grid">
                <?php
                $result = $con->query("SELECT * FROM accessories ORDER BY category, name");
                while ($row = $result->fetch_assoc()):
                ?>
                <div class="accessory-card">
                    <div class="accessory-image">
                        <?php if (!empty($row['image'])): ?>
                            <img src="../uploads/accessories/<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <?php else: ?>
                            <i class="fas fa-gift"></i>
                        <?php endif; ?>
                    </div>
                    <div class="accessory-details">
                        <h3 class="accessory-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <span class="accessory-category"><?php echo htmlspecialchars($row['category']); ?></span>
                        <?php if (!empty($row['description'])): ?>
                            <p class="accessory-description"><?php echo htmlspecialchars($row['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="accessory-meta">
                            <div class="accessory-price">Ks <?php echo number_format($row['price'], 0); ?></div>
                        </div>
                        
                        <div class="accessory-actions">
                            <a href="manage_accessories.php?edit=<?php echo $row['aid']; ?>" 
                               class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="manage_accessories.php?delete=<?php echo $row['aid']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this accessory?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Floating Action Button -->
        <a href="manage_accessories.php?edit=0" class="floating-action-btn animate__animated animate__bounceIn">
            <i class="fas fa-plus"></i>
        </a>
    </div>
</div>

<script>
    // Image preview functionality
    document.querySelector('input[type="file"]')?.addEventListener('change', function(e) {
        const preview = document.querySelector('.image-preview');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}">`;
            }
            
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<div class="no-image"><i class="fas fa-image"></i> No image selected</div>';
        }
    });
    
    // Auto-hide notifications after their duration
    document.querySelectorAll('.notification').forEach(notification => {
        const duration = notification.dataset.duration || 3000;
        setTimeout(() => {
            notification.style.display = 'none';
        }, duration);
    });
</script>
</body>
</html>
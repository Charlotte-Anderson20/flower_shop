<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_flower'])) {
        $flower_name = trim($_POST['flower_name']);
        $flower_id = intval($_POST['flower_type_id']);
        $image_url = $_POST['existing_image'] ?? '';

        if (!empty($_FILES['main_image']['name'])) {
            $upload = uploadImage($_FILES['main_image'], '../uploads/flowers/');
            if ($upload['success']) {
                $image_url = $upload['filename'];
                if (!empty($_POST['existing_image']) && file_exists("../uploads/flowers/" . $_POST['existing_image'])) {
                    unlink("../uploads/flowers/" . $_POST['existing_image']);
                }
            } else {
                $message = displayAlert($upload['message'], 'danger');
            }
        }

        if (empty($flower_name)) {
            $message = displayAlert('Flower name is required', 'danger');
        } else {
            $stmt = $con->prepare("UPDATE flower_type SET flower_name=?, image_url=? WHERE flower_type_id=?");
            $stmt->bind_param("ssi", $flower_name, $image_url, $flower_id);
            
            if ($stmt->execute()) {
                $message = displayAlert('Flower type updated successfully!', 'success');
            } else {
                $message = displayAlert('Error updating flower type: ' . $stmt->error, 'danger');
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['edit'])) {
    $flower_id = intval($_GET['edit']);
    $result = $con->query("SELECT * FROM flower_type WHERE flower_type_id = $flower_id");
    if ($result->num_rows > 0) {
        $flower = $result->fetch_assoc();
    }
}

if (isset($_GET['delete'])) {
    $flower_id = intval($_GET['delete']);
    $check = $con->query("SELECT COUNT(*) as count FROM Product_Flower_Type WHERE flower_type_id = $flower_id");
    $count = $check->fetch_assoc()['count'];
    if ($count > 0) {
        $message = displayAlert('Cannot delete this flower type as it is used in products.', 'danger');
    } else {
        $result = $con->query("SELECT image_url FROM flower_type WHERE flower_type_id = $flower_id");
        $row = $result->fetch_assoc();
        $image_path = "../uploads/flowers/" . $row['image_url'];

        if ($con->query("DELETE FROM flower_type WHERE flower_type_id = $flower_id")) {
            if (!empty($row['image_url']) && file_exists($image_path)) {
                unlink($image_path);
            }
            $message = displayAlert('Flower type deleted successfully!', 'success');
        } else {
            $message = displayAlert('Error deleting flower type: ' . $con->error, 'danger');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Flower Types - Admin Dashboard</title>
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
            width: 150px;
            height: 150px;
            border: 1px dashed #ccc;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                <h2 class="card-title"><i class="fas fa-spa"></i> Manage Flower Types</h2>
                <div class="card-actions">
                    <a href="add_flower.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Flower Type
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                
                <?php if (isset($_GET['edit']) && isset($flower)): ?>
                <div class="form-container slide-in">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="flower_type_id" value="<?php echo $flower['flower_type_id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo $flower['image_url']; ?>">

                        <div class="form-group">
                            <label class="form-label">Flower Name *</label>
                            <input type="text" name="flower_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($flower['flower_name']); ?>" 
                                   required minlength="2" maxlength="100">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Main Image</label>
                            <div class="image-preview">
                                <?php if (!empty($flower['image_url'])): ?>
                                    <img src="../uploads/flowers/<?php echo $flower['image_url']; ?>">
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999;">
                                        No image selected
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="main_image" class="form-control" accept="image/*">
                            <small class="form-text">Leave blank to keep current image</small>
                        </div>

                        <div class="form-actions">
                            <a href="manage_flowers.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" name="save_flower" class="btn btn-primary">
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
                                <th>ID</th>
                                <th>Image</th>
                                <th>Flower Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                       <tbody>
    <?php
    $result = $con->query("SELECT * FROM flower_type ORDER BY flower_name");
    $counter = 1; // start numbering
    while ($row = $result->fetch_assoc()):
    ?>
    <tr>
        <td><?php echo $counter++; ?></td> <!-- show number instead of real ID -->
        <td>
            <?php if (!empty($row['image_url'])): ?>
                <img src="../uploads/flowers/<?php echo $row['image_url']; ?>" 
                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
            <?php else: ?>
                <i class="fas fa-spa" style="font-size: 24px; color: var(--primary-color);"></i>
            <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($row['flower_name']); ?></td>
        <td class="table-actions">
            <a href="manage_flowers.php?edit=<?php echo $row['flower_type_id']; ?>" 
               class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="manage_flowers.php?delete=<?php echo $row['flower_type_id']; ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('Are you sure you want to delete this flower type?')">
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
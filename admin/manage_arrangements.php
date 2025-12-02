<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$arrangement = [
    'arrangement_id' => '',
    'arrangement_name' => '',
    'icon_image' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_arrangement'])) {
        $arrangement_name = trim($_POST['arrangement_name']);
        $arrangement_id = isset($_POST['arrangement_id']) ? intval($_POST['arrangement_id']) : 0;
        $current_image = isset($_POST['current_image']) ? $_POST['current_image'] : '';
        $delete_image = isset($_POST['delete_image']) ? true : false;
        
        // Handle file upload
        $icon_image = $current_image;
        if (isset($_FILES['icon_image']) && $_FILES['icon_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/arrangement_icons/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = 'icon_' . uniqid() . '.' . pathinfo($_FILES['icon_image']['name'], PATHINFO_EXTENSION);
            $file_path = $upload_dir . $file_name;
            
            // Check if file is an image
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $_FILES['icon_image']['tmp_name']);
            
            if (in_array($mime_type, $allowed_types)) {
                if (move_uploaded_file($_FILES['icon_image']['tmp_name'], $file_path)) {
                    $icon_image = 'uploads/arrangement_icons/' . $file_name;
                    
                    // Delete old image if exists
                    if (!empty($current_image)) {
                        $old_file_path = '../' . $current_image;
                        if (file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                    }
                } else {
                    $message = displayAlert('Error uploading file.', 'danger');
                }
            } else {
                $message = displayAlert('Only JPG, PNG, and GIF images are allowed.', 'danger');
            }
        } elseif ($delete_image && !empty($current_image)) {
            // Delete existing image
            $file_path = '../' . $current_image;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $icon_image = '';
        }

        if (empty($arrangement_name)) {
            $message = displayAlert('Arrangement name is required.', 'danger');
        } else {
            if ($arrangement_id > 0) {
                // Update existing
                $stmt = $con->prepare("UPDATE arrangement_type SET arrangement_name = ?, icon_image = ? WHERE arrangement_id = ?");
                $stmt->bind_param("ssi", $arrangement_name, $icon_image, $arrangement_id);
            } else {
                // Insert new
                $stmt = $con->prepare("INSERT INTO arrangement_type (arrangement_name, icon_image) VALUES (?, ?)");
                $stmt->bind_param("ss", $arrangement_name, $icon_image);
            }

            if ($stmt->execute()) {
                $message = displayAlert('Arrangement saved successfully.', 'success');
                $arrangement = ['arrangement_id' => '', 'arrangement_name' => '', 'icon_image' => ''];
            } else {
                $message = displayAlert('Database error: ' . $stmt->error, 'danger');
            }
            $stmt->close();
        }
    }
}

// Handle edit
if (isset($_GET['edit'])) {
    $arrangement_id = intval($_GET['edit']);
    $result = $con->query("SELECT * FROM arrangement_type WHERE arrangement_id = $arrangement_id");
    if ($result->num_rows > 0) {
        $arrangement = $result->fetch_assoc();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $arrangement_id = intval($_GET['delete']);
    // First get the image path to delete the file
    $result = $con->query("SELECT icon_image FROM arrangement_type WHERE arrangement_id = $arrangement_id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['icon_image'])) {
            $file_path = '../' . $row['icon_image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    
    if ($con->query("DELETE FROM arrangement_type WHERE arrangement_id = $arrangement_id")) {
        $message = displayAlert('Arrangement deleted successfully.', 'success');
    } else {
        $message = displayAlert('Error deleting arrangement: ' . $con->error, 'danger');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Arrangements - Admin Dashboard</title>
  <link rel="shortcut icon" href="../images/flowerb.png" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: <?php echo !empty($arrangement['icon_image']) ? 'block' : 'none'; ?>;
        }
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 15px;
        }
        .file-upload-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .remove-image {
            color: #dc3545;
            cursor: pointer;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

<div class="dashboard-container">
    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h2>Manage Arrangements</h2>
            </div>
            <div class="card-body">
                <?php echo $message; ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="arrangement_id" value="<?php echo $arrangement['arrangement_id']; ?>">
                    <input type="hidden" name="current_image" value="<?php echo $arrangement['icon_image']; ?>">
                    
                    <div class="form-group">
                        <label>Arrangement Name</label>
                        <input type="text" name="arrangement_name" class="form-control" required
                               value="<?php echo htmlspecialchars($arrangement['arrangement_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Icon Image</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="icon_image" id="icon_image" class="file-upload-input" accept="image/*">
                        </div>
                        
                        <?php if (!empty($arrangement['icon_image'])): ?>
                            <div class="image-preview-container">
                                <img src="../<?php echo $arrangement['icon_image']; ?>" class="image-preview" id="imagePreview">
                                <div>
                                    <label>
                                        <input type="checkbox" name="delete_image" value="1"> Remove image
                                    </label>
                                </div>
                            </div>
                        <?php else: ?>
                            <img src="" class="image-preview" id="imagePreview" style="display: none;">
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="save_arrangement" class="btn btn-primary">Save Arrangement</button>
                    <?php if (!empty($arrangement['arrangement_id'])): ?>
                        <a href="manage_arrangements.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>

                <hr>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Arrangement Name</th>
                            <th>Icon</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php
    $result = $con->query("SELECT * FROM arrangement_type ORDER BY arrangement_id DESC");
    $counter = 1; // start numbering
    while ($row = $result->fetch_assoc()):
    ?>
        <tr>
            <td><?php echo $counter++; ?></td> <!-- show number instead of real ID -->
            <td><?php echo htmlspecialchars($row['arrangement_name']); ?></td>
            <td>
                <?php if (!empty($row['icon_image'])): ?>
                    <img src="../<?php echo $row['icon_image']; ?>" style="max-width: 50px; max-height: 50px;">
                <?php endif; ?>
            </td>
            <td>
                <a href="manage_arrangements.php?edit=<?php echo $row['arrangement_id']; ?>" 
                   class="btn btn-sm btn-primary">Edit</a>
                <a href="manage_arrangements.php?delete=<?php echo $row['arrangement_id']; ?>" 
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Are you sure you want to delete this arrangement?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

                </table>

            </div>
        </div>
    </div>
</div>

<script>
    // Preview image before upload
    document.getElementById('icon_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>
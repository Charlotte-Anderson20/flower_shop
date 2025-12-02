<?php
include '../includes/db.php';
include 'side.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

// Get logged-in customer ID (from session)
$customer_id = $_SESSION['customer_id'] ?? null;


// Fetch current profile
$stmt = $con->prepare("SELECT * FROM customer WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - Tiny Flower</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-pink: #e83e8c;
            --light-pink: #f8d7da;
            --soft-pink: #fff5f7;
            --dark-pink: #d63384;
            --text-dark: #495057;
            --text-light: #6c757d;
            --shadow: 0 5px 15px rgba(232, 62, 140, 0.1);
        }
        
        body {
            background-color: #f9f0f5;
            color: var(--text-dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
       
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            border: none;
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-pink), #c2185b);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .profile-body {
            padding: 30px;
        }
        
        .form-control, .form-select {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-pink);
            box-shadow: 0 0 0 0.25rem rgba(232, 62, 140, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-pink), #c2185b);
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--dark-pink), #ad1457);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .profile-image-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--soft-pink);
            box-shadow: var(--shadow);
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin-top: 10px;
        }
        
        .file-upload-btn {
            background: var(--light-pink);
            color: var(--primary-pink);
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .file-upload-btn:hover {
            background: var(--primary-pink);
            color: white;
        }
        
        .file-upload input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        
        .section-title {
            color: var(--primary-pink);
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-pink);
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                text-align: center;
            }
            
            .sidebar-brand h2 {
                font-size: 1.2rem;
            }
            
            .sidebar-menu span {
                display: none;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
            }
            
            .sidebar-menu li {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
 
    <div class="main-content">
        <div class="container">
            <h2 class="section-title">Update Profile</h2>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="profile-card">
                        <div class="profile-header">
                            <h3><i class="fas fa-user-edit me-2"></i> Personal Information</h3>
                            <p class="mb-0">Update your profile details below</p>
                        </div>
                        
                        <div class="profile-body">
                            <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?>">
                                
                                <div class="profile-image-container">
                                    <img src="../<?= $customer['customer_image'] ?: 'uploads/customers/default-profile.jpg' ?>" 
                                         class="profile-image" id="profile-preview">
                                    <div class="file-upload mt-3">
                                        <div class="file-upload-btn">
                                            <i class="fas fa-camera me-2"></i> Choose Image
                                            <input type="file" name="customer_image" id="profile-image" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" name="customer_name" class="form-control" 
                                                   value="<?= htmlspecialchars($customer['customer_name']) ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" name="customer_email" class="form-control" 
                                                   value="<?= htmlspecialchars($customer['customer_email']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="text" name="customer_phone" class="form-control" 
                                                   value="<?= htmlspecialchars($customer['customer_phone']) ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-home"></i></span>
                                            <input type="text" name="customer_address" class="form-control" 
                                                   value="<?= htmlspecialchars($customer['customer_address']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync-alt me-2"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('profile-image').addEventListener('change', function(e) {
            const preview = document.getElementById('profile-preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
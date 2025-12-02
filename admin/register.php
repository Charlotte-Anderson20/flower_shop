<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "tinny_flower_shop");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($con, $_POST['admin_name']);
    $email = mysqli_real_escape_string($con, $_POST['admin_email']);
    $password = mysqli_real_escape_string($con, $_POST['admin_pass']);
    
    // Check if email already exists
    $check_query = "SELECT * FROM Admin WHERE admin_email = '$email'";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Email already exists!";
    } else {
        // Handle file upload
        $target_dir = "uploads/admin/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["admin_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["admin_image"]["tmp_name"]);
        if ($check === false) {
            $error = "File is not an image.";
        } elseif ($_FILES["admin_image"]["size"] > 500000) {
            $error = "Sorry, your file is too large.";
        } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES["admin_image"]["tmp_name"], $target_file)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO Admin (admin_name, admin_email, admin_pass, admin_image) 
                      VALUES ('$name', '$email', '$hashed_password', '$target_file')";
            
            if (mysqli_query($con, $query)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Error: " . mysqli_error($con);
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .register-container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        h2 {
            color: #8e6c88;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        input[type="file"] {
            padding: 0.3rem;
        }
        
        .form-submit {
            background-color: #8e6c88;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .form-submit:hover {
            background-color: #b75d69;
        }
        
        .error {
            color: #b75d69;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .success {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .login-link a {
            color: #8e6c88;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="register-container">
        <h2>Admin Registration</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="admin_name">Full Name</label>
                <input type="text" id="admin_name" name="admin_name" required>
            </div>
            <div class="form-group">
                <label for="admin_email">Email</label>
                <input type="email" id="admin_email" name="admin_email" required>
            </div>
            <div class="form-group">
                <label for="admin_pass">Password</label>
                <input type="password" id="admin_pass" name="admin_pass" required>
            </div>
            <div class="form-group">
                <label for="admin_image">Profile Image</label>
                <input type="file" id="admin_image" name="admin_image" required accept="image/*">
            </div>
            <button type="submit" class="form-submit">Register</button>
        </form>
        
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>
</html>
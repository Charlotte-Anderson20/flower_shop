<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "tinny_flower_shop");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($con, $_POST['admin_email']);
    $password = mysqli_real_escape_string($con, $_POST['admin_pass']);
    
    $query = "SELECT * FROM Admin WHERE admin_email = '$email'";
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $admin['admin_pass'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['admin_name'];
            $_SESSION['admin_email'] = $admin['admin_email'];
            $_SESSION['admin_image'] = $admin['admin_image'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Admin not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
        
        .login-container {
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
        
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .register-link a {
            color: #8e6c88;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="admin_email">Email</label>
                <input type="email" id="admin_email" name="admin_email" required>
            </div>
            <div class="form-group">
                <label for="admin_pass">Password</label>
                <input type="password" id="admin_pass" name="admin_pass" required>
            </div>
            <button type="submit" class="form-submit">Login</button>
        </form>
        
    
    </div>
</body>
</html>
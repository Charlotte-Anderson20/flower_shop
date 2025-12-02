<?php
session_start();
require_once 'includes/db.php';
header('Content-Type: application/json'); // changed to JSON header

// Auto-login using remember_token
if (!isset($_SESSION['customer_id']) && isset($_COOKIE['remember_token'])) {
    $token = mysqli_real_escape_string($con, $_COOKIE['remember_token']);
    $now = date('Y-m-d H:i:s');

    $query = mysqli_query($con, "SELECT * FROM customer WHERE remember_token = '$token' AND remember_expiry > '$now'");
    if ($user = mysqli_fetch_assoc($query)) {
        $_SESSION['customer_id'] = $user['customer_id'];
        $_SESSION['customer_name'] = $user['customer_name'];
        $_SESSION['customer_email'] = $user['customer_email'];
        if (!empty($user['customer_image'])) {
            $_SESSION['customer_image'] = $user['customer_image'];
        }
    } else {
        // Expired or invalid token — remove cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'register') {
        $name = mysqli_real_escape_string($con, $_POST['customer_name']);
        $email = mysqli_real_escape_string($con, $_POST['customer_email']);
        $phone = mysqli_real_escape_string($con, $_POST['customer_phone'] ?? '');
        $address = mysqli_real_escape_string($con, $_POST['customer_address'] ?? '');
        $password = password_hash($_POST['customer_password'], PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');
        $imagePath = null;

        $check = mysqli_query($con, "SELECT * FROM customer WHERE customer_email='$email'");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already registered.']);
            exit();
        }

        // Image upload
        if (isset($_FILES['customer_image']) && $_FILES['customer_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png'];
            $fileType = $_FILES['customer_image']['type'];

            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = 'uploads/customers/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $extension = pathinfo($_FILES['customer_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('customer_') . '.' . $extension;
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['customer_image']['tmp_name'], $destination)) {
                    $imagePath = $destination;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error uploading image.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid image type. Only JPG and PNG allowed.']);
                exit();
            }
        }

        $sql = "INSERT INTO customer (customer_name, customer_email, customer_phone, customer_address, customer_password, customer_created_at, customer_image)
                VALUES ('$name', '$email', '$phone', '$address', '$password', '$created_at', " . ($imagePath ? "'$imagePath'" : "NULL") . ")";
        if (mysqli_query($con, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
        } else {
            if ($imagePath && file_exists($imagePath)) unlink($imagePath);
            echo json_encode(['status' => 'error', 'message' => mysqli_error($con)]);
        }
        exit();
    }

    elseif ($action === 'login') {
        $email = mysqli_real_escape_string($con, $_POST['customer_email']);
        $password = $_POST['customer_password'];

        $query = mysqli_query($con, "SELECT * FROM customer WHERE customer_email='$email'");
        if (mysqli_num_rows($query) === 1) {
            $user = mysqli_fetch_assoc($query);
            $currentTime = time();
            $lastAttemptTime = strtotime($user['last_login_attempt'] ?? '1970-01-01 00:00:00');
            $loginAttempts = $user['login_attempts'];

            if ($loginAttempts >= 3 && ($currentTime - $lastAttemptTime) < 60) {
                $remaining = 60 - ($currentTime - $lastAttemptTime);
                echo json_encode(['status' => 'error', 'message' => "Too many failed attempts. Try again in {$remaining} seconds."]);
                exit();
            }

            if (password_verify($password, $user['customer_password'])) {
                // Successful login
                mysqli_query($con, "UPDATE customer SET login_attempts = 0, last_login_attempt = NULL WHERE customer_id = {$user['customer_id']}");

                $_SESSION['customer_id'] = $user['customer_id'];
                $_SESSION['customer_name'] = $user['customer_name'];
                $_SESSION['customer_email'] = $user['customer_email'];
                if (!empty($user['customer_image'])) {
                    $_SESSION['customer_image'] = $user['customer_image'];
                }

                // ✅ Remember Me Logic
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));

                    setcookie('remember_token', $token, time() + (7 * 24 * 60 * 60), '/');
                    mysqli_query($con, "UPDATE customer SET remember_token = '$token', remember_expiry = '$expiry' WHERE customer_id = {$user['customer_id']}");
                }

                echo json_encode(['status' => 'success', 'message' => 'Login successful', 'redirect' => 'user/dashboard.php']);
            } else {
                $newAttempts = ($currentTime - $lastAttemptTime > 60) ? 1 : $loginAttempts + 1;
                mysqli_query($con, "UPDATE customer SET login_attempts = $newAttempts, last_login_attempt = NOW() WHERE customer_id = {$user['customer_id']}");
                echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Account not found.']);
        }
        exit();
    }

    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

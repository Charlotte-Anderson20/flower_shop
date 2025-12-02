<?php
session_start();
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_POST['customer_id'];
    $name = $_POST['customer_name'];
    $email = $_POST['customer_email'];
    $phone = $_POST['customer_phone'];
    $address = $_POST['customer_address'];

    // Handle profile image upload
    $imagePath = null;
    if (!empty($_FILES['customer_image']['name'])) {
        $targetDir = "uploads/customers/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = "customer_" . uniqid() . "." . pathinfo($_FILES['customer_image']['name'], PATHINFO_EXTENSION);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['customer_image']['tmp_name'], "../" . $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // Update SQL
    if ($imagePath) {
        $sql = "UPDATE customer SET customer_name=?, customer_email=?, customer_phone=?, customer_address=?, customer_image=? WHERE customer_id=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $phone, $address, $imagePath, $customer_id);
    } else {
        $sql = "UPDATE customer SET customer_name=?, customer_email=?, customer_phone=?, customer_address=? WHERE customer_id=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $phone, $address, $customer_id);
    }

    if ($stmt->execute()) {
        // Update session
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_email'] = $email;
        if ($imagePath) {
            $_SESSION['customer_image'] = $imagePath;
        }
        header("Location: profile.php?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

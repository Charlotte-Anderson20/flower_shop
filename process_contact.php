<?php
session_start();
require 'includes/db.php';  // Make sure $con is your mysqli connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name    = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email   = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email   = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    $phone   = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error_message'] = "Please fill in all required fields.";
        header("Location: contact.php");
        exit();
    }

    if (!$email) {
        $_SESSION['error_message'] = "Please enter a valid email address.";
        header("Location: contact.php");
        exit();
    }

    // Prepare statement
    $stmt = $con->prepare("INSERT INTO contacts (name, email, phone, subject, message, created_at) 
                           VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        $_SESSION['error_message'] = "Database error: " . $con->error;
        header("Location: contact.php");
        exit();
    }

    // Bind parameters and execute
    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Thank you for contacting us! We'll get back to you soon.";
    } else {
        $_SESSION['error_message'] = "There was an error submitting your message. Please try again later.";
    }

    $stmt->close();
    header("Location: contact.php");
    exit();

} else {
    header("Location: contact.php");
    exit();
}
?>

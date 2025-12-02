<?php
// Database connection details
$host = "localhost"; // Usually localhost
$username = "root";  // Your MySQL username
$password = "";      // Your MySQL password
$database = "tinny_flower_shop"; // Your database name

// Create connection
$con = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: Show success message
// echo "Connected successfully";
?>

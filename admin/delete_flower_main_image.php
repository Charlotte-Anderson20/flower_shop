<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$flower_id = isset($_POST['flower_id']) ? intval($_POST['flower_id']) : 0;

if ($flower_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid flower ID']);
    exit();
}

// Get current image filename
$result = $con->query("SELECT image_url FROM Flower_Type WHERE flower_type_id = $flower_id");
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Flower not found']);
    exit();
}

$flower = $result->fetch_assoc();
$filename = $flower['image_url'];

// Update database to remove the image reference
if ($con->query("UPDATE Flower_Type SET image_url = NULL WHERE flower_type_id = $flower_id")) {
    // Delete the image file if it exists
    if (!empty($filename)) {
        $image_path = "../uploads/flowers/" . $filename;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $con->error]);
}
?>
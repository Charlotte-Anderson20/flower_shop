<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? '';
    $image_id = intval($_POST['image_id'] ?? 0);
    $relation_id = intval($_POST['relation_id'] ?? 0);
    
    if (in_array($table, ['Flower_Images', 'Arrangement_Images', 'Occasion_Images']) && $image_id > 0 && $relation_id > 0) {
        $success = setPrimaryImage($table, $image_id, $relation_id);
        echo json_encode(['success' => $success, 'message' => $success ? 'Primary image updated' : 'Error updating primary image']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}
?>
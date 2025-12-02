<?php
/**
 * Uploads an image file to the server
 * 
 * @param array $file The $_FILES array element
 * @param string $target_dir The directory to upload to (default: "../uploads/flowers/")
 * @param int $max_size Maximum file size in bytes (default: 5MB)
 * @return array Result array with success status and message/filename
 */
function uploadImage($file, $target_dir = "../uploads/flowers/", $max_size = 5000000) {
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory.'];
        }
    }

    $original_name = basename($file["name"]);
    $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $imageFileType;
    $destination = $target_dir . $new_filename;
    
    // Check if file is an actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size
    if ($file["size"] > $max_size) {
        $max_size_mb = round($max_size / 1024 / 1024, 1);
        return ['success' => false, 'message' => "File is too large. Maximum size is {$max_size_mb}MB."];
    }
    
    // Allow certain file formats
    $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
    if(!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];
        return ['success' => false, 'message' => $error_messages[$file['error']] ?? 'Unknown upload error.'];
    }
    
    // Try to upload file
    if (move_uploaded_file($file["tmp_name"], $destination)) {
        // Create a web-friendly path (relative to the site root)
        $web_path = str_replace('../', '', $destination);
        return ['success' => true, 'filename' => $new_filename, 'path' => $destination, 'web_path' => $web_path];
    } else {
        return ['success' => false, 'message' => 'Sorry, there was an error uploading your file.'];
    }
}

/**
 * Uploads multiple image files to the server
 * 
 * @param array $files The $_FILES array for multiple uploads
 * @param string $target_dir The directory to upload to
 * @param int $max_size Maximum file size in bytes (default: 5MB)
 * @return array Array of uploaded files info or error messages
 */
function uploadMultipleImages($files, $target_dir = "../uploads/flowers/", $max_size = 5000000) {
    $results = [];
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory.'];
        }
    }

    foreach ($files['tmp_name'] as $key => $tmp_name) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];
            
            $result = uploadImage($file, $target_dir, $max_size);
            $results[] = $result;
        }
    }
    
    return $results;
}

/**
 * Displays a styled alert message
 * 
 * @param string $message The message to display
 * @param string $type Alert type (success, danger, warning, info)
 * @return string HTML for the alert
 */
function displayAlert($message, $type = 'success') {
    $icons = [
        'success' => 'check-circle',
        'danger' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'info-circle'
    ];
    
    $icon = $icons[$type] ?? 'info-circle';
    
    return <<<HTML
    <div class="alert alert-{$type} fade-in">
        <i class="fas fa-{$icon}"></i>
        {$message}
    </div>
HTML;
}

/**
 * Generates select options from a database table
 * 
 * @param string $table Database table name
 * @param string $value_field Field to use as option value
 * @param string $text_field Field to use as option text
 * @param mixed $selected Currently selected value(s)
 * @param string $where Additional WHERE clause
 * @param bool $multiple Whether to allow multiple selections
 * @return string HTML options
 */
function getSelectOptions($table, $value_field, $text_field, $selected = null, $where = '', $multiple = false) {
    global $con;
    $options = '';
    $query = "SELECT {$value_field}, {$text_field} FROM {$table}";
    
    if (!empty($where)) {
        $query .= " WHERE {$where}";
    }
    
    $query .= " ORDER BY {$text_field}";
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        error_log("Database error in getSelectOptions: " . mysqli_error($con));
        return '';
    }
    
    while ($row = mysqli_fetch_assoc($result)) {
        $value = htmlspecialchars($row[$value_field]);
        $text = htmlspecialchars($row[$text_field]);
        
        if ($multiple && is_array($selected)) {
            $is_selected = in_array($value, $selected) ? 'selected' : '';
        } else {
            $is_selected = ($selected == $value) ? 'selected' : '';
        }
        
        $options .= "<option value='{$value}' {$is_selected}>{$text}</option>";
    }
    
    return $options;
}

/**
 * Deletes an image file and its database record
 * 
 * @param string $table Database table name
 * @param int $image_id ID of the image to delete
 * @param string $base_dir Base directory for images
 * @return bool True on success, false on failure
 */
function deleteImageRecord($table, $image_id, $base_dir = '../uploads/') {
    global $con;
    
    // First get the image path
    $result = $con->query("SELECT image_url FROM $table WHERE image_id = $image_id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = $base_dir . $row['image_url'];
        
        // Delete from database
        if ($con->query("DELETE FROM $table WHERE image_id = $image_id")) {
            // Delete image file if exists
            if (!empty($row['image_url']) && file_exists($image_path)) {
                if (!unlink($image_path)) {
                    error_log("Failed to delete image file: $image_path");
                }
            }
            return true;
        } else {
            error_log("Database error deleting image record: " . $con->error);
        }
    }
    return false;
}

/**
 * Sets an image as the primary image for a record
 * 
 * @param string $table Database table name
 * @param int $image_id ID of the image to set as primary
 * @param int $relation_id ID of the related record
 * @return bool True on success, false on failure
 */
function setPrimaryImage($table, $image_id, $relation_id) {
    global $con;
    
    // First reset all primary images for this relation
    $relation_field = str_replace('_Images', '_id', $table);
    $reset_sql = "UPDATE $table SET is_primary = FALSE WHERE $relation_field = $relation_id";
    
    if (!$con->query($reset_sql)) {
        error_log("Error resetting primary images: " . $con->error);
        return false;
    }
    
    // Set the new primary image
    $set_sql = "UPDATE $table SET is_primary = TRUE WHERE image_id = $image_id";
    return $con->query($set_sql);
}
?>
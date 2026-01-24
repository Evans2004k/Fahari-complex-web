<?php
// Sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number (Kenyan format)
function validate_phone($phone) {
    return preg_match('/^(\+254|0)[17]\d{8}$/', $phone);
}

// Upload image
function upload_image($file, $upload_dir = 'uploads/') {
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($file['name']);
    $target_path = $upload_dir . $file_name;
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.'];
    }
    
    if ($file['size'] > $max_size) {
        return ['error' => 'File is too large. Maximum size is 5MB.'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'file_path' => $target_path];
    } else {
        return ['error' => 'Failed to upload file.'];
    }
}
?>
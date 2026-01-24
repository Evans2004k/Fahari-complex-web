<?php
// Set headers for CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include configuration
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// API endpoints
if ($uri[1] === 'api') {
    switch ($uri[2]) {
        case 'products':
            require_once 'api/products.php';
            break;
        case 'categories':
            require_once 'api/categories.php';
            break;
        case 'contact':
            require_once 'api/contact.php';
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} else {
    // Serve frontend if not an API request
    if (file_exists('frontend/index.html')) {
        readfile('frontend/index.html');
    } else {
        echo json_encode(['message' => 'Fahari Complex API is running']);
    }
}
?>
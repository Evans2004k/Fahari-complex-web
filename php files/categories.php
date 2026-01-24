<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all categories
        $query = "SELECT * FROM categories ORDER BY name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }
        
        echo json_encode($categories);
        break;
        
    case 'POST':
        // Create new category (admin only)
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->name)) {
            $slug = strtolower(str_replace(' ', '-', $data->name));
            
            $query = "INSERT INTO categories (name, slug) VALUES (:name, :slug)";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':name', $data->name);
            $stmt->bindParam(':slug', $slug);
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode([
                    'message' => 'Category created successfully',
                    'id' => $db->lastInsertId()
                ]);
            } else {
                http_response_code(503);
                echo json_encode(['error' => 'Unable to create category']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Category name required']);
        }
        break;
}
?>
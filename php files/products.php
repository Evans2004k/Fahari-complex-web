<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all products or single product
        if (isset($_GET['id'])) {
            // Get single product
            $product_id = $_GET['id'];
            $query = "SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $product_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($product);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
            }
        } else {
            // Get all products with filters
            $category = isset($_GET['category']) ? $_GET['category'] : '';
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
            $max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
            
            $query = "SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.status = 'available'";
            
            $params = [];
            
            if (!empty($category)) {
                $query .= " AND c.slug = :category";
                $params[':category'] = $category;
            }
            
            if (!empty($search)) {
                $query .= " AND (p.title LIKE :search OR p.description LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if (!empty($min_price)) {
                $query .= " AND p.price >= :min_price";
                $params[':min_price'] = $min_price;
            }
            
            if (!empty($max_price)) {
                $query .= " AND p.price <= :max_price";
                $params[':max_price'] = $max_price;
            }
            
            $query .= " ORDER BY p.created_at DESC";
            
            $stmt = $db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            $products = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $row;
            }
            
            echo json_encode($products);
        }
        break;
        
    case 'POST':
        // Create new product
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->title) && !empty($data->price)) {
            $query = "INSERT INTO products 
                     (title, description, price, category_id, image_url, seller_name, seller_phone, location) 
                     VALUES (:title, :description, :price, :category_id, :image_url, :seller_name, :seller_phone, :location)";
            
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':title', $data->title);
            $stmt->bindParam(':description', $data->description);
            $stmt->bindParam(':price', $data->price);
            $stmt->bindParam(':category_id', $data->category_id);
            $stmt->bindParam(':image_url', $data->image_url);
            $stmt->bindParam(':seller_name', $data->seller_name);
            $stmt->bindParam(':seller_phone', $data->seller_phone);
            $stmt->bindParam(':location', $data->location);
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode([
                    'message' => 'Product created successfully',
                    'id' => $db->lastInsertId()
                ]);
            } else {
                http_response_code(503);
                echo json_encode(['error' => 'Unable to create product']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
        }
        break;
        
    case 'PUT':
        // Update product
        $data = json_decode(file_get_contents("php://input"));
        $product_id = isset($_GET['id']) ? $_GET['id'] : '';
        
        if (!empty($product_id)) {
            $query = "UPDATE products 
                     SET title = :title, description = :description, price = :price, 
                         category_id = :category_id, image_url = :image_url, 
                         seller_name = :seller_name, seller_phone = :seller_phone, 
                         location = :location, status = :status 
                     WHERE id = :id";
            
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':id', $product_id);
            $stmt->bindParam(':title', $data->title);
            $stmt->bindParam(':description', $data->description);
            $stmt->bindParam(':price', $data->price);
            $stmt->bindParam(':category_id', $data->category_id);
            $stmt->bindParam(':image_url', $data->image_url);
            $stmt->bindParam(':seller_name', $data->seller_name);
            $stmt->bindParam(':seller_phone', $data->seller_phone);
            $stmt->bindParam(':location', $data->location);
            $stmt->bindParam(':status', $data->status);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Product updated successfully']);
            } else {
                http_response_code(503);
                echo json_encode(['error' => 'Unable to update product']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID required']);
        }
        break;
        
    case 'DELETE':
        // Delete product
        $product_id = isset($_GET['id']) ? $_GET['id'] : '';
        
        if (!empty($product_id)) {
            $query = "DELETE FROM products WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $product_id);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Product deleted successfully']);
            } else {
                http_response_code(503);
                echo json_encode(['error' => 'Unable to delete product']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID required']);
        }
        break;
}
?>
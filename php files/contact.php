<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->name) && !empty($data->email) && !empty($data->message)) {
        $query = "INSERT INTO contact_messages (name, email, phone, message) 
                 VALUES (:name, :email, :phone, :message)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':name', $data->name);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':phone', $data->phone);
        $stmt->bindParam(':message', $data->message);
        
        if ($stmt->execute()) {
            // Send email notification (optional)
            $to = "info@faharicomplex.com";
            $subject = "New Contact Message from Fahari Complex Website";
            $message_body = "Name: " . $data->name . "\n";
            $message_body .= "Email: " . $data->email . "\n";
            $message_body .= "Phone: " . ($data->phone ?? 'Not provided') . "\n";
            $message_body .= "Message:\n" . $data->message . "\n";
            
            // mail($to, $subject, $message_body);
            
            http_response_code(201);
            echo json_encode(['message' => 'Message sent successfully']);
        } else {
            http_response_code(503);
            echo json_encode(['error' => 'Unable to send message']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
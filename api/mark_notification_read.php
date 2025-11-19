<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

include "../config/db.php";

// Get JSON body
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? null;
$notification_ids = $data['ids'] ?? [];

// Convert single ID to array if provided
if (isset($data['notification_id'])) {
    $notification_ids = [$data['notification_id']];
}

if (!$user_id || empty($notification_ids)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "user_id and either ids array or notification_id are required"
    ]);
    exit();
}

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }

    // Create placeholders for the IN clause
    $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
    
    // Prepare types string for bind_param
    $types = str_repeat('s', count($notification_ids)) . 's'; // Extra 's' for user_id
    
    // Create params array
    $params = array_merge($notification_ids, [$user_id]);
    
    // Update notifications
    $sql = "UPDATE notifications SET is_read = 1 
            WHERE id IN ($placeholders) AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    echo json_encode([
        "status" => "success"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error"
    ]);
}

$conn->close();
?>
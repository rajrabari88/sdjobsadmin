<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Bearer token check
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
$validToken = "9313069472";

if (!$auth || $auth !== "Bearer $validToken") {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token"]);
    exit();
}

include "../config/db.php";

// Validate user_id
$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    echo json_encode(["unread" => false]);
    exit();
}

// Prepared statement to count unread messages
$stmt = $conn->prepare("
    SELECT COUNT(*) AS unread 
    FROM messages 
    WHERE chat_id IN (
        SELECT chat_id FROM chat_threads WHERE user_id = ?
    ) 
    AND sender_name = 'Admin' 
    AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$hasUnread = ($result['unread'] ?? 0) > 0;

echo json_encode(["unread" => $hasUnread]);
?>

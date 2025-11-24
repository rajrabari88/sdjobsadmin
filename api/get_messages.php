<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

include __DIR__ . "/../config/db.php";

// Validate user_id
$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    echo json_encode([]);
    exit();
}

// Secure prepared statement to get chat_id
$thread_stmt = $conn->prepare("SELECT chat_id FROM chat_threads WHERE user_id = ?");
$thread_stmt->bind_param("i", $user_id);
$thread_stmt->execute();
$thread = $thread_stmt->get_result()->fetch_assoc();
$thread_stmt->close();

if (!$thread) {
    echo json_encode([]);
    exit();
}

$chat_id = $thread['chat_id'];

// Fetch messages
$stmt = $conn->prepare("
    SELECT sender_name AS sender, message, created_at 
    FROM messages 
    WHERE chat_id = ? 
    ORDER BY created_at ASC
");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
echo json_encode($data);
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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

// Read JSON body (Flutter sends JSON)
$input = json_decode(file_get_contents("php://input"), true);
$user_id = intval($input['user_id'] ?? 0);
$message = trim($input['message'] ?? '');

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "msg" => "Invalid user_id"]);
    exit();
}

if ($message === '') {
    echo json_encode(["status" => "error", "msg" => "Message empty"]);
    exit();
}

// Check if chat thread exists
$thread_stmt = $conn->prepare("SELECT chat_id FROM chat_threads WHERE user_id = ?");
$thread_stmt->bind_param("i", $user_id);
$thread_stmt->execute();
$thread = $thread_stmt->get_result()->fetch_assoc();
$thread_stmt->close();

if (!$thread) {
    $stmt2 = $conn->prepare("INSERT INTO chat_threads (user_id, subject) VALUES (?, 'New Support Request')");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $chat_id = $stmt2->insert_id;
    $stmt2->close();
} else {
    $chat_id = $thread['chat_id'];
}

// Insert the message
$sender_name = 'user';
$subject = null;

$stmt = $conn->prepare("
    INSERT INTO messages (chat_id, user_id, sender_name, subject, message, is_read, created_at)
    VALUES (?, ?, ?, ?, ?, 0, NOW())
");
$stmt->bind_param("iisss", $chat_id, $user_id, $sender_name, $subject, $message);
$stmt->execute();
$stmt->close();

echo json_encode(["status" => "success", "chat_id" => $chat_id]);
?>

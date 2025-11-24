<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include __DIR__ . "/../config/db.php";

$chat_id = intval($_POST['chat_id']);
$subject = trim($_POST['subject']);
$message = trim($_POST['message']);

if (empty($message)) exit(json_encode(["status"=>"error","msg"=>"Message empty"]));

// Get user_id from chat thread
$thread = $conn->query("SELECT user_id FROM chat_threads WHERE chat_id=$chat_id")->fetch_assoc();
if (!$thread) exit(json_encode(["status"=>"error","msg"=>"Chat thread not found"]));

$user_id = $thread['user_id'];

// Insert admin message
$stmt = $conn->prepare("
    INSERT INTO messages (chat_id, user_id, sender_name, subject, message, is_read, created_at)
    VALUES (?, ?, 'Admin', ?, ?, 0, NOW())
");
$stmt->bind_param("iiss", $chat_id, $user_id, $subject, $message);
$stmt->execute();
$stmt->close();

echo json_encode(["status"=>"success"]);

<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Correct include path
include __DIR__ . "/../config/db.php";

$user_id = $_POST['user_id'];
$message = $_POST['message'];

$conn->query("
    INSERT INTO messages (chat_id, sender, user_id, message)
    VALUES ($user_id, 'user', $user_id, '$message')
");

echo json_encode(["status" => "success"]);

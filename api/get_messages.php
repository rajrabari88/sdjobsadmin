<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Correct include path
include __DIR__ . "/../config/db.php";

$user_id = $_GET['user_id'];

$sql = $conn->query("
    SELECT sender, message, created_at
    FROM messages
    WHERE chat_id = $user_id
    ORDER BY created_at ASC
");

$data = [];
while ($row = $sql->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

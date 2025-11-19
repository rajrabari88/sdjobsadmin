<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Correct include path
include __DIR__ . "/../config/db.php";

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode(["status" => "missing_user_id"]);
    exit;
}

$user_id = $conn->real_escape_string($_GET['user_id']);

$sql = "SELECT id, file_name, file_url, uploaded_on 
        FROM user_documents 
        WHERE user_id='$user_id' 
        ORDER BY id DESC";

$result = $conn->query($sql);

$documents = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
}

echo json_encode(["status" => "success", "documents" => $documents], JSON_UNESCAPED_SLASHES);

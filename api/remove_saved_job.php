<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include "../config/db.php";

// Handle OPTIONS
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
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);
$user_id = $input['user_id'] ?? '';
$job_id = $input['job_id'] ?? '';

if (!$user_id || !$job_id) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit();
}

// Delete saved job
$stmt = $conn->prepare("DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?");
$stmt->bind_param("ss", $user_id, $job_id);
if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>

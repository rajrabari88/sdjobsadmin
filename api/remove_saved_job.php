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

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? null;
$job_id  = $data['job_id'] ?? null;

if (!$user_id || !$job_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing parameters"
    ]);
    exit();
}

// Delete saved job
$stmt = $conn->prepare("DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?");
$stmt->bind_param("ss", $user_id, $job_id);
$stmt->execute();

echo json_encode([
    "status" => "success",
    "message" => "Job removed successfully"
]);
?>

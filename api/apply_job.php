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
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if (!$auth || $auth !== 'Bearer 9313069472') {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token"]);
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
$job_id = $data['job_id'] ?? null;
$cover_letter = $data['cover_letter'] ?? null;

// Validate required fields
if (!$user_id || !$job_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "user_id and job_id are required"]);
    exit();
}

try {
    // Check if job exists
    $stmt = $conn->prepare("SELECT 1 FROM jobs WHERE id = ?");
    $stmt->bind_param("s", $job_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Job not found"]);
        exit();
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT resume_url FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    if ($user_result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }
    $user = $user_result->fetch_assoc();

    // Check if already applied
    $stmt = $conn->prepare("SELECT 1 FROM job_applications WHERE user_id = ? AND job_id = ?");
    $stmt->bind_param("ss", $user_id, $job_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Already applied to this job"]);
        exit();
    }

    // Create application
    $stmt = $conn->prepare("INSERT INTO job_applications (user_id, job_id, cover_letter, resume_url, status, applied_date) 
                           VALUES (?, ?, ?, ?, 'applied', NOW())");
    $stmt->bind_param("ssss", $user_id, $job_id, $cover_letter, $user['resume_url']);
    $stmt->execute();
    $application_id = $stmt->insert_id;

    echo json_encode([
        "status" => "success",
        "message" => "Applied successfully",
        "application_id" => (string)$application_id
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
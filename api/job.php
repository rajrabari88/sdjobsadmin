<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

include "../config/db.php";

$job_id = $_GET['id'] ?? null;
$user_id = $_GET['user_id'] ?? null; // Optional, for getting saved/applied status

if (!$job_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "job id is required"]);
    exit();
}

try {
    // Get job details
    $stmt = $conn->prepare("SELECT id, title, company, location, salary_min, salary_max, 
                           salary_display, type, logo_text, logo_url, description, 
                           requirements, experience, created_at as posted_date,
                           apply_url, category, company_description
                           FROM jobs WHERE id = ?");
    $stmt->bind_param("s", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();

    if (!$job) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Job not found"]);
        exit();
    }

    // Format salary
    $job['salary'] = $job['salary_display'] ?? 
        ("₹" . $job['salary_min'] . "k - " . $job['salary_max'] . "k/m");

    // Add saved/applied status if user_id provided
    if ($user_id) {
        $saved_stmt = $conn->prepare("SELECT 1 FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        $saved_stmt->bind_param("ss", $user_id, $job_id);
        $saved_stmt->execute();
        $job['is_saved'] = $saved_stmt->get_result()->num_rows > 0;

        $applied_stmt = $conn->prepare("SELECT 1 FROM applications WHERE user_id = ? AND job_id = ?");
        $applied_stmt->bind_param("ss", $user_id, $job_id);
        $applied_stmt->execute();
        $job['applied'] = $applied_stmt->get_result()->num_rows > 0;
    }

    echo json_encode([
        "status" => "success",
        "job" => $job
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
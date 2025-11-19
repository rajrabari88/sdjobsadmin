<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Preflight check
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

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "user_id is required"]);
    exit();
}

try {
    // ✅ Get User Data
    $stmt = $conn->prepare("SELECT id, name, email, location, designation, experience, avatar_url, resume_url 
                            FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }

    // ✅ Get Featured Job
    // ✅ Get All Featured Jobs
    $featured_jobs = [];
    $featured_query = "SELECT id, title, company, location, salary_min, salary_max, salary_display, 
                  type, logo_text, logo_url, description, experience, created_at as posted_date
                  FROM jobs WHERE is_featured = 1 ORDER BY created_at DESC";
    $result = $conn->query($featured_query);

    while ($job = $result->fetch_assoc()) {
        $min = $job['salary_min'] ?? 0;
        $max = $job['salary_max'] ?? 0;
        $job['salary'] = $job['salary_display'] ?: "₹{$min}k - {$max}k/m";

        // Saved Status
        $stmt = $conn->prepare("SELECT 1 FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        $stmt->bind_param("ss", $user_id, $job['id']);
        $stmt->execute();
        $job['is_saved'] = $stmt->get_result()->num_rows > 0;

        // Applied Status
        $stmt = $conn->prepare("SELECT 1 FROM applications WHERE user_id = ? AND job_id = ?");
        $stmt->bind_param("ss", $user_id, $job['id']);
        $stmt->execute();
        $job['applied'] = $stmt->get_result()->num_rows > 0;

        $featured_jobs[] = $job;
    }


    // ✅ Get Recent Jobs
    $recent_jobs = [];
    $recent_query = "SELECT id, title, company, location, salary_min, salary_max, salary_display,
                    type, logo_text, logo_url, description, experience, created_at as posted_date 
                    FROM jobs ORDER BY created_at DESC LIMIT 10";
    $recent_result = $conn->query($recent_query);

    while ($job = $recent_result->fetch_assoc()) {
        $min = $job['salary_min'] ?? 0;
        $max = $job['salary_max'] ?? 0;
        $job['salary'] = $job['salary_display'] ?: "₹{$min}k - {$max}k/m";

        $stmt = $conn->prepare("SELECT 1 FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        $stmt->bind_param("ss", $user_id, $job['id']);
        $stmt->execute();
        $job['is_saved'] = $stmt->get_result()->num_rows > 0;

        $stmt = $conn->prepare("SELECT 1 FROM applications WHERE user_id = ? AND job_id = ?");
        $stmt->bind_param("ss", $user_id, $job['id']);
        $stmt->execute();
        $job['applied'] = $stmt->get_result()->num_rows > 0;

        $recent_jobs[] = $job;
    }

    // ✅ Get Categories (fallback if empty)
    $categories = [];
    $cat_result = $conn->query("SELECT DISTINCT category FROM jobs WHERE category IS NOT NULL AND category != '' ORDER BY category");
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    if (empty($categories)) {
        $categories = ["Development", "Design", "Marketing"];
    }

    // ✅ Final Response
    echo json_encode([
        "status" => "success",
        "user" => $user,
        "featured_jobs" => $featured_jobs,
        "categories" => $categories,
        "recent_jobs" => $recent_jobs
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Internal server error"]);
}
?>
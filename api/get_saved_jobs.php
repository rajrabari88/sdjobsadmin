<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include "../config/db.php";

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 🔑 Bearer token check
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
$validToken = "9313069472";

if (!$auth || $auth !== "Bearer $validToken") {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$user_id = $_GET['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(["jobs" => []]);
    exit();
}

// Secure prepared statement
$stmt = $conn->prepare("SELECT jobs.* FROM jobs 
                        JOIN saved_jobs ON saved_jobs.job_id = jobs.id
                        WHERE saved_jobs.user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode(["jobs" => $data]);
?>

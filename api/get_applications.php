<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");
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

// ✅ Correct include path
include __DIR__ . "/../config/db.php";

$user_id = $_GET['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(["status" => "error", "message" => "Missing user_id"]);
    exit;
}

$sql = "SELECT 
            a.id,
            a.job_id,
            j.title,
            j.company,
            a.status,
            a.created_at
        FROM applications a
        LEFT JOIN jobs j ON j.id = a.job_id
        WHERE a.user_id = ?
        ORDER BY a.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];

while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

echo json_encode(["status" => "success", "data" => $applications]);
?>

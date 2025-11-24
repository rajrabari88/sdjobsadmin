<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight
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
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token"]);
    exit();
}

include __DIR__ . "/../config/db.php";

// Validate user_id
$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    echo json_encode(["status" => "missing_user_id", "documents" => []]);
    exit();
}

// Prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id, file_name, file_url, uploaded_on 
                        FROM user_documents 
                        WHERE user_id = ? 
                        ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$documents = [];
while ($row = $result->fetch_assoc()) {
    $documents[] = $row;
}

$stmt->close();

echo json_encode(["status" => "success", "documents" => $documents], JSON_UNESCAPED_SLASHES);
?>

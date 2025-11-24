<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS preflight
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
$user_id = intval($_POST['user_id'] ?? 0);
if ($user_id <= 0) {
    echo json_encode(["status" => "missing_user_id"]);
    exit();
}

// Check file
if (!isset($_FILES['file'])) {
    echo json_encode(["status" => "no_file"]);
    exit();
}

$fileName = basename($_FILES['file']['name']);
$fileTmp = $_FILES['file']['tmp_name'];

// Upload directory
$uploadDir = __DIR__ . "/../uploads/"; 
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Create unique filename
$newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", $fileName);
$filePath = $uploadDir . $newFileName;

if (!move_uploaded_file($fileTmp, $filePath)) {
    echo json_encode(["status" => "upload_error"]);
    exit();
}

$fileUrl = "http://192.168.1.4/sdjobs/uploads/" . $newFileName;

// Insert into DB securely
$stmt = $conn->prepare("INSERT INTO user_documents (user_id, file_name, file_url) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $newFileName, $fileUrl);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "file_url" => $fileUrl]);
} else {
    echo json_encode(["status" => "db_error", "message" => $conn->error]);
}

$stmt->close();
?>

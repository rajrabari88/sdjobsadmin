<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
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
include "../config/db.php";


// Accept JSON input
$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$name || !$email || !$password) {
    echo json_encode(["status" => "error", "message" => "All fields required"]);
    exit();
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Check duplicate email
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
    exit();
}

// Insert user
$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $passwordHash);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Account created successfully", "user_id" => $stmt->insert_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to register"]);
}

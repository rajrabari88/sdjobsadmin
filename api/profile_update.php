<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "../config/db.php";

$user_id = $_POST['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "user_id is required"]);
    exit();
}

// Check if user exists
$check = $conn->prepare("SELECT id FROM users WHERE id = ?");
$check->bind_param("s", $user_id);
$check->execute();
if ($check->get_result()->num_rows == 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit();
}

$base_url = "http://" . $_SERVER['SERVER_NAME'] . "/sdjobs/uploads/";
$upload_dir = "../uploads/";

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// -------- Avatar Upload ----------
$avatar_url = null;
if (!empty($_FILES['avatar']['name'])) {
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        echo json_encode(["status" => "error", "message" => "Invalid avatar file type"]);
        exit();
    }
    $avatar_filename = "avatar_{$user_id}." . $ext;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $avatar_filename);
    $avatar_url = $base_url . $avatar_filename;
}

// -------- Resume Upload ----------
$resume_url = null;
if (!empty($_FILES['resume']['name'])) {
    $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
        echo json_encode(["status" => "error", "message" => "Invalid resume file type"]);
        exit();
    }
    $resume_filename = "resume_{$user_id}." . $ext;
    move_uploaded_file($_FILES['resume']['tmp_name'], $upload_dir . $resume_filename);
    $resume_url = $base_url . $resume_filename;
}

// -------- Update Profile Fields ----------
$fields = ['name', 'email', 'location', 'designation', 'experience', 'phone'];
$updates = [];
$params = [];
$types = "";

foreach ($fields as $field) {
    if (isset($_POST[$field])) {
        $updates[] = "$field=?";
        $types .= "s";
        $params[] = $_POST[$field];
    }
}

if ($avatar_url) {
    $updates[] = "avatar_url=?";
    $types .= "s";
    $params[] = $avatar_url;
}

if ($resume_url) {
    $updates[] = "resume_url=?";
    $types .= "s";
    $params[] = $resume_url;
}

if (!empty($updates)) {
    $updates = implode(", ", $updates);
    $types .= "s";
    $params[] = $user_id;

    $sql = "UPDATE users SET $updates WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
}

// Return Updated User
$stmt = $conn->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

echo json_encode(["status" => "success", "user" => $user]);
$conn->close();

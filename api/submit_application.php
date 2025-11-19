<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

include "../config/db.php";

$user_id        = $_POST['user_id'] ?? '';
$job_id         = $_POST['job_id'] ?? '';
$name           = $_POST['name'] ?? '';
$email          = $_POST['email'] ?? '';
$phone          = $_POST['phone'] ?? '';
$experience     = $_POST['experience'] ?? '';   // <-- from app
$cover_letter   = $_POST['cover_letter'] ?? '';
$additional     = $_POST['additional_notes'] ?? '';

// REQUIRED VALIDATION
if (empty($user_id) || empty($job_id)) {
    echo json_encode(["status" => "error", "message" => "Required fields missing"]);
    exit;
}

$sql = "INSERT INTO job_applications
        (user_id, job_id, name, email, phone, experience_level, cover_letter, additional_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iissssss",
    $user_id,
    $job_id,
    $name,
    $email,
    $phone,
    $experience,       // <-- mapped correctly
    $cover_letter,
    $additional
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Application submitted"]);
} else {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $stmt->error]);
}
?>

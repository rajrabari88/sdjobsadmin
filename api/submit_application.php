<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

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

include "../config/db.php";

// Read JSON body
$input = json_decode(file_get_contents("php://input"), true);
$user_id = $input['user_id'] ?? '';
$job_id = $input['job_id'] ?? '';
$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$phone = $input['phone'] ?? '';
$experience = $input['experience'] ?? '';
$cover_letter = $input['cover_letter'] ?? '';
$additional = $input['additional_notes'] ?? '';

// Validation
if (empty($user_id) || empty($job_id)) {
    echo json_encode(["status" => "error", "message" => "Required fields missing"]);
    exit();
}

// 1️⃣ Check duplicate
$check = $conn->prepare("SELECT id FROM job_applications WHERE user_id = ? AND job_id = ?");
$check->bind_param("ii", $user_id, $job_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        "status" => "duplicate",
        "message" => "You have already applied for this job"
    ]);
    exit();
}

// 2️⃣ Insert into job_applications
$sql1 = "INSERT INTO job_applications
        (user_id, job_id, name, email, phone, experience_level, cover_letter, additional_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("iissssss",
    $user_id, $job_id, $name, $email, $phone, $experience, $cover_letter, $additional
);
$ok1 = $stmt1->execute();

// 3️⃣ Insert into applications
$message = $cover_letter . "\n\n" . $additional;
$sql2 = "INSERT INTO applications (user_id, job_id, message) VALUES (?, ?, ?)";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("iis", $user_id, $job_id, $message);
$ok2 = $stmt2->execute();

// 4️⃣ Response
if ($ok1 && $ok2) {
    echo json_encode(["status" => "success", "message" => "Application submitted"]);
} else {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $conn->error]);
}
?>

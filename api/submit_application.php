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
$experience     = $_POST['experience'] ?? '';
$cover_letter   = $_POST['cover_letter'] ?? '';
$additional     = $_POST['additional_notes'] ?? '';

// REQUIRED VALIDATION
if (empty($user_id) || empty($job_id)) {
    echo json_encode(["status" => "error", "message" => "Required fields missing"]);
    exit;
}

/* -------------------------------------------------
   ❗ 1️⃣ CHECK IF USER ALREADY APPLIED
------------------------------------------------- */
$check = $conn->prepare("SELECT id FROM job_applications WHERE user_id = ? AND job_id = ?");
$check->bind_param("ii", $user_id, $job_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        "status" => "duplicate",
        "message" => "You have already applied for this job"
    ]);
    exit;
}

/* -------------------------------------------------
   2️⃣ INSERT INTO job_applications TABLE
------------------------------------------------- */
$sql1 = "INSERT INTO job_applications
        (user_id, job_id, name, email, phone, experience_level, cover_letter, additional_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("iissssss",
    $user_id,
    $job_id,
    $name,
    $email,
    $phone,
    $experience,
    $cover_letter,
    $additional
);

$ok1 = $stmt1->execute();

/* -------------------------------------------------
   3️⃣ INSERT INTO applications TABLE
------------------------------------------------- */
$message = $cover_letter . "\n\n" . $additional;

$sql2 = "INSERT INTO applications (user_id, job_id, message) VALUES (?, ?, ?)";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("iis", $user_id, $job_id, $message);

$ok2 = $stmt2->execute();

/* -------------------------------------------------
   4️⃣ FINAL RESPONSE
------------------------------------------------- */
if ($ok1 && $ok2) {
    echo json_encode(["status" => "success", "message" => "Application submitted"]);
} else {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $conn->error]);
}

?>

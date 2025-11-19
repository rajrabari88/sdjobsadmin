<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header('Content-Type: application/json');

include "../config/db.php";

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid user"]);
    exit;
}

// Fetch user details
$userQuery = $conn->prepare("SELECT id, name, email, location, designation, experience, avatar_url, resume_url FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

// Count Saved Jobs
$savedQuery = $conn->prepare("SELECT COUNT(*) AS total FROM saved_jobs WHERE user_id = ?");
$savedQuery->bind_param("i", $user_id);
$savedQuery->execute();
$savedCount = $savedQuery->get_result()->fetch_assoc()['total'];

// Count Applied Jobs
$appliedQuery = $conn->prepare("SELECT COUNT(*) AS total FROM applications WHERE user_id = ?");
$appliedQuery->bind_param("i", $user_id);
$appliedQuery->execute();
$appliedCount = $appliedQuery->get_result()->fetch_assoc()['total'];

// Count Unread Notifications
$notifQuery = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
$notifQuery->bind_param("i", $user_id);
$notifQuery->execute();
$notifCount = $notifQuery->get_result()->fetch_assoc()['total'];

$response = [
    "status" => "success",
    "user" => $user,
    "saved_jobs_count" => intval($savedCount),
    "applied_jobs_count" => intval($appliedCount),
    "notifications_count" => intval($notifCount)
];

echo json_encode($response);
?>

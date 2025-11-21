<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

include "../config/db.php";

$user_id = $_GET['user_id'] ?? null;
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = max(1, min(50, intval($_GET['per_page'] ?? 20)));

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "user_id is required"]);
    exit();
}

try {

    // Validate user exists
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }

    // Count total notifications
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_row()[0];

    // Pagination offset
    $offset = ($page - 1) * $per_page;

    // Fetch notifications
    $sql = "SELECT 
                id,
                title,
                message AS body,
                is_read,
                created_at
            FROM notifications 
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $row['is_read'] = (bool)$row['is_read'];
        $notifications[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "total" => (int)$total,
        "page" => $page,
        "per_page" => $per_page,
        "notifications" => $notifications
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error"
    ]);
}

$conn->close();
?>

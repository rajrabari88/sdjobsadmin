<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
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

// Get search parameters
$query = $_GET['q'] ?? null;
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = max(1, min(50, intval($_GET['per_page'] ?? 20)));
$type = $_GET['type'] ?? null;
$location = $_GET['location'] ?? null;
$min_salary = $_GET['min_salary'] ?? null;
$category = $_GET['category'] ?? null;
$sort = $_GET['sort'] ?? 'recent';
$user_id = $_GET['user_id'] ?? null; // Optional, for getting saved/applied status

if (!$query) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing query parameter q"]);
    exit();
}

try {
    // Build base query
    $sql = "SELECT SQL_CALC_FOUND_ROWS id, title, company, location, salary_min, salary_max, 
            salary_display, type, logo_text, logo_url, description, experience, 
            created_at as posted_date FROM jobs WHERE 1=1";
    $params = [];
    $types = "";

    // Add search conditions
    $sql .= " AND (title LIKE ? OR company LIKE ? OR description LIKE ?)";
    $search_term = "%{$query}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";

    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
        $types .= "s";
    }

    if ($location) {
        $sql .= " AND location LIKE ?";
        $params[] = "%{$location}%";
        $types .= "s";
    }

    if ($min_salary) {
        $sql .= " AND salary_min >= ?";
        $params[] = $min_salary;
        $types .= "i";
    }

    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }

    // Add sorting
    $sql .= match($sort) {
        'salary_high' => " ORDER BY salary_max DESC, created_at DESC",
        'salary_low' => " ORDER BY salary_min ASC, created_at DESC",
        default => " ORDER BY created_at DESC"
    };

    // Add pagination
    $offset = ($page - 1) * $per_page;
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";

    // Execute query
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Get total count
    $total_result = $conn->query("SELECT FOUND_ROWS()");
    $total = $total_result->fetch_row()[0];

    // Fetch results
    $jobs = [];
    while ($job = $result->fetch_assoc()) {
        // Format salary
        $job['salary'] = $job['salary_display'] ?? 
            ("₹" . $job['salary_min'] . "k - " . $job['salary_max'] . "k/m");

        // Add saved/applied status if user_id provided
        if ($user_id) {
            $saved_stmt = $conn->prepare("SELECT 1 FROM saved_jobs WHERE user_id = ? AND job_id = ?");
            $saved_stmt->bind_param("ss", $user_id, $job['id']);
            $saved_stmt->execute();
            $job['is_saved'] = $saved_stmt->get_result()->num_rows > 0;

            $applied_stmt = $conn->prepare("SELECT 1 FROM applications WHERE user_id = ? AND job_id = ?");
            $applied_stmt->bind_param("ss", $user_id, $job['id']);
            $applied_stmt->execute();
            $job['applied'] = $applied_stmt->get_result()->num_rows > 0;
        }

        $jobs[] = $job;
    }

    echo json_encode([
        "status" => "success",
        "total" => (int)$total,
        "page" => $page,
        "per_page" => $per_page,
        "results" => $jobs
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
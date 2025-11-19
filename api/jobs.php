<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

include "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$sql = "SELECT id, title, company, location, salary, job_type, logo_text FROM jobs ORDER BY id DESC";
$result = $conn->query($sql);

$jobs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = [
            "id" => $row["id"],
            "title" => $row["title"],
            "company" => $row["company"],
            "location" => $row["location"],
            "salary" => $row["salary"],
            "type" => $row["job_type"],
            "logoText" => $row["logo_text"]
        ];
    }

    echo json_encode(["status" => "success", "data" => $jobs]);
} else {
    echo json_encode(["status" => "success", "data" => []]);
}

$conn->close();
?>

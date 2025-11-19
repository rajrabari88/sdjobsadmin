<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

include "../config/db.php";

$user_id = $_GET['user_id'];

$q = $conn->query("SELECT jobs.* FROM jobs 
JOIN saved_jobs ON saved_jobs.job_id = jobs.id
WHERE saved_jobs.user_id = '$user_id'");

$data = [];
while($row = $q->fetch_assoc()){
  $data[] = $row;
}

echo json_encode($data);
?>

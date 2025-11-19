<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

include "../config/db.php";

$user_id = $_POST['user_id'];
$job_id  = $_POST['job_id'];

if(!$user_id || !$job_id){
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;
}

// check if already saved
$check = $conn->query("SELECT id FROM saved_jobs WHERE user_id='$user_id' AND job_id='$job_id'");
if($check->num_rows > 0){
    echo json_encode(["status" => "exists"]);
    exit;
}

// insert
$q = $conn->query("INSERT INTO saved_jobs (user_id, job_id) VALUES ('$user_id', '$job_id')");

if($q){
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>

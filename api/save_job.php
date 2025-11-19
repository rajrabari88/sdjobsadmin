<?php
include 'config.php';

$user_id = $_POST['user_id'];
$job_id = $_POST['job_id'];

$q = $conn->query("INSERT INTO saved_jobs (user_id, job_id) VALUES ('$user_id', '$job_id')");

if($q){
  echo json_encode(["status" => "success"]);
} else {
  echo json_encode(["status" => "error"]);
}
?>

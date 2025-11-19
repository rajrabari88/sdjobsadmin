<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Correct config path
include __DIR__ . "/../config/db.php";

if(!isset($_POST['doc_id']) || !isset($_POST['file_url'])){
    echo json_encode(["status" => "missing_parameters"]);
    exit;
}

$doc_id = $_POST['doc_id'];
$file_url = $_POST['file_url'];

// ✅ Convert URL to actual file path
$fileName = basename($file_url);
$filePath = __DIR__ . "/../uploads/" . $fileName;

// ✅ Delete file if exists
if(file_exists($filePath)){
    unlink($filePath);
}

// ✅ Delete from database
$conn->query("DELETE FROM user_documents WHERE id='$doc_id'");

echo json_encode(["status" => "success"]);

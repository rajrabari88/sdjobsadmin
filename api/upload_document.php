<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

include __DIR__ . "/../config/db.php"; // ✅ Correct path

if(!isset($_POST['user_id'])){
    echo json_encode(["status" => "missing_user_id"]);
    exit;
}

$user_id = $_POST['user_id'];

if(isset($_FILES['file'])){
    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];

    $uploadDir = __DIR__ . "/../uploads/"; 
    $newFileName = time() . "_" . $fileName;
    $filePath = $uploadDir . $newFileName;

    if(move_uploaded_file($fileTmp, $filePath)){
        $fileUrl = "http://192.168.1.194/sdjobs/uploads/" . $newFileName;

        $sql = "INSERT INTO user_documents (user_id, file_name, file_url) VALUES ('$user_id', '$newFileName', '$fileUrl')";

        if($conn->query($sql)){
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "db_error", "message" => $conn->error]);
        }
    } else {
        echo json_encode(["status" => "upload_error"]);
    }

} else {
    echo json_encode(["status" => "no_file"]);
}

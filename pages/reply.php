<?php
include "../config/db.php";

$user_id = $_POST['user_id'];
$message = $_POST['message'];

$conn->query("
    INSERT INTO messages (chat_id, sender, user_id, message)
    VALUES ($user_id, 'admin', $user_id, '$message')
");

header("Location: inbox.php");

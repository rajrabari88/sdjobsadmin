<?php
include __DIR__ . '/../config/db.php';

$message_id = $_POST['message_id'];
$reply_text = $conn->real_escape_string($_POST['reply_text']);

$conn->query("INSERT INTO message_replies (message_id, reply_text) VALUES ($message_id, '$reply_text')");

header("Location: view_message.php?id=$message_id");
exit;

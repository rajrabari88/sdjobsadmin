<?php
include __DIR__ . '/../config/db.php';

if (!isset($_GET['chat_id'])) {
    die("Chat ID missing");
}

$chat_id = intval($_GET['chat_id']);

// Mark messages as read
$conn->query("UPDATE messages SET is_read = 1 WHERE chat_id = $chat_id");

// Fetch chat messages
$msgQuery = $conn->query("SELECT * FROM messages WHERE chat_id = $chat_id ORDER BY created_at ASC");

// On reply submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $message = $conn->real_escape_string($_POST['message']);

    $conn->query("
        INSERT INTO messages (chat_id, sender, user_id, sender_name, subject, message, is_read)
        VALUES ($chat_id, 'admin', 0, 'Admin', 'Reply', '$message', 0)
    ");

    header("Location: view_chat.php?chat_id=$chat_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Chat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.chat-box { height:500px; overflow-y:auto; }
.user-msg { text-align:left; }
.admin-msg { text-align:right; }
.bubble {
    display:inline-block; padding:10px 15px; border-radius:12px; max-width:70%;
}
.user-bubble { background:#f1f1f1; }
.admin-bubble { background:#007bff; color:white; }
</style>
</head>

<body class="p-4">

<h4 class="mb-3">Chat #<?= $chat_id ?></h4>

<div class="card shadow">
<div class="card-body chat-box">

<?php while ($row = $msgQuery->fetch_assoc()) { ?>

    <div class="<?= $row['sender'] == 'admin' ? 'admin-msg' : 'user-msg' ?> mb-3">
        <div class="bubble <?= $row['sender'] == 'admin' ? 'admin-bubble' : 'user-bubble' ?>">
            <strong><?= ucfirst($row['sender']) ?>:</strong><br>
            <?= nl2br(htmlspecialchars($row['message'])) ?>
        </div>
        <div class="small text-muted">
            <?= date("d M, h:i A", strtotime($row['created_at'])) ?>
        </div>
    </div>

<?php } ?>

</div>
</div>

<form method="POST" class="mt-3">
    <textarea class="form-control" name="message" required placeholder="Type your reply..."></textarea>
    <button class="btn btn-primary mt-2">Send Reply</button>
</form>

</body>
</html>

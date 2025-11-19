<?php
include __DIR__ . '/../config/db.php';

$id = $_GET['id'];

// Mark message as read
$conn->query("UPDATE messages SET is_read = 1 WHERE id = $id");

// Fetch message details
$msg = $conn->query("SELECT * FROM messages WHERE id = $id")->fetch_assoc();

// Fetch replies
$replyResult = $conn->query("SELECT * FROM message_replies WHERE message_id = $id ORDER BY replied_at ASC");
?>

<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4 p-4">

        <h4 class="fw-bold mb-2"><?= $msg['subject'] ?></h4>
        <p class="text-muted mb-1">From: <strong><?= $msg['sender_name'] ?></strong></p>
        <p class="text-muted small"><?= $msg['created_at'] ?></p>
        <hr>

        <p class="fs-6"><?= nl2br($msg['message']); ?></p>

        <hr>

        <!-- Reply Section -->
        <h5 class="fw-bold mb-3">Replies</h5>

        <?php while($reply = $replyResult->fetch_assoc()) { ?>
            <div class="alert alert-secondary py-2">
                <small class="text-muted"><?= $reply['replied_at'] ?></small><br>
                <?= nl2br($reply['reply_text']) ?>
            </div>
        <?php } ?>

        <form action="reply_message.php" method="POST">
            <input type="hidden" name="message_id" value="<?= $id ?>">
            <textarea name="reply_text" class="form-control mb-3" rows="3" placeholder="Write your reply..." required></textarea>
            <button type="submit" class="btn btn-success rounded-pill">Send Reply</button>
        </form>

        <hr>
        <a href="inbox.php" class="btn btn-primary mt-2 rounded-pill">← Back to Inbox</a>
    </div>
</div>

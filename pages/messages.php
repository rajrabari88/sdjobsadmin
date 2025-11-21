<?php
include __DIR__ . '/../config/db.php';
if ($conn->connect_error) {
    die("DB Failed: " . $conn->connect_error);
}

// Count unread
$newCount = $conn->query("SELECT COUNT(*) AS total FROM messages WHERE is_read = 0")
                 ->fetch_assoc()['total'];

// Fetch latest message per chat_id
$sql = "
SELECT m1.*
FROM messages m1
INNER JOIN (
    SELECT chat_id, MAX(id) AS last_id
    FROM messages
    GROUP BY chat_id
) m2 ON m1.id = m2.last_id
ORDER BY m1.created_at DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inbox</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.bg-light-blue { background:#f4f9ff !important; }
.avatar-circle {
    width:44px;height:44px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-weight:600;
}
.message-list-scroll { max-height:500px; overflow-y:auto; }
</style>
</head>

<body>
<div class="container py-5">

<h3 class="fw-bold mb-4">
📩 Inbox
<span class="badge bg-danger ms-2"><?= $newCount ?> New</span>
</h3>

<div class="card shadow-sm rounded-4">
<div class="list-group list-group-flush message-list-scroll">

<?php 
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $isUnread = $row['is_read'] == 0;
        $messageClass = $isUnread ? "bg-light-blue fw-bold" : "text-muted";

        $sender = !empty(trim($row['sender_name'])) ? trim($row['sender_name']) : "Unknown";
        $initial = strtoupper(substr($sender, 0, 1));
        $shortMsg = substr($row['message'], 0, 40) . "...";
?>
    <a href="/sdjobs/pages/view_chat.php?chat_id=<?= $row['chat_id'] ?>"
 
        class="list-group-item list-group-item-action d-flex <?= $messageClass ?>">

        <div class="avatar-circle bg-primary text-white me-3">
            <?= $initial ?>
        </div>

        <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
                <h6 class="mb-0"><?= $sender ?></h6>
                <small class="text-muted">
                    <?= date("d M, h:i A", strtotime($row['created_at'])) ?>
                </small>
            </div>

            <p class="small mb-1"><?= htmlspecialchars($shortMsg) ?></p>

            <?php if ($isUnread) { ?>
                <span class="badge bg-warning">New</span>
            <?php } ?>
        </div>
    </a>

<?php }} else { ?>

<div class="p-5 text-center text-muted">
    <p>No messages found.</p>
</div>

<?php } ?>

</div>
</div>

</div>
</body>
</html>

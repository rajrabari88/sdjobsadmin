<?php
include __DIR__ . '/../config/db.php';

// Handle sending a reply
if (isset($_POST['reply_message'])) {
    $chat_id = intval($_POST['chat_id']);
    $sender_name = 'Admin';
    $subject = 'Re: ' . $_POST['subject'];
    $message = $_POST['reply_message'];

    $stmt = $conn->prepare("
    INSERT INTO messages (chat_id, user_id, sender_name, subject, message, is_read, created_at) 
    VALUES (?, ?, 'Admin', ?, ?, 0, NOW())
");
    $stmt->bind_param("iiss", $chat_id, $user_id, $subject, $message);
    $stmt->execute();
    $stmt->close();

}

// Fetch chat threads
$chatThreads = [];
$result = $conn->query("
    SELECT ct.chat_id,
           u.name AS user_name,
           ct.subject,
           MAX(m.created_at) AS last_msg_time,
           SUM(m.is_read=0 AND m.sender_name!='Admin') AS unread_count
    FROM chat_threads ct
    LEFT JOIN users u ON u.id = ct.user_id
    LEFT JOIN messages m ON m.chat_id = ct.chat_id
    GROUP BY ct.chat_id
    ORDER BY last_msg_time DESC
");

while ($row = $result->fetch_assoc()) {
    $chatThreads[] = $row;
}

// Fetch selected chat messages
$chatMessages = [];
$selectedChat = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : 0;

if ($selectedChat > 0) {
    $stmt = $conn->prepare("
        SELECT m.*, u.name AS user_name
        FROM messages m
        LEFT JOIN chat_threads ct ON ct.chat_id = m.chat_id
        LEFT JOIN users u ON u.id = ct.user_id
        WHERE m.chat_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->bind_param("i", $selectedChat);
    $stmt->execute();
    $chatMessages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Mark messages as read
    $conn->query("UPDATE messages SET is_read=1 WHERE chat_id=$selectedChat AND sender_name != 'Admin'");
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="fw-bold text-primary">💬 Messages</h3>
            <p class="text-muted mb-0">View and reply to user messages in a clean interface.</p>
        </div>
    </div>

    <div class="row">
        <!-- Chat Threads -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chat Threads</h5>
                </div>
                <div class="list-group list-group-flush overflow-auto" style="max-height: 70vh;">
                    <?php if (empty($chatThreads)): ?>
                        <div class="p-3 text-center text-muted">No messages yet.</div>
                    <?php endif; ?>
                    <?php foreach ($chatThreads as $thread):
                        $last_time = new DateTime($thread['last_msg_time'] ?? 'now');
                        $display_time = $last_time->format('M j, H:i');
                        ?>
                        <a href="index.php?page=messages&chat_id=<?= $thread['chat_id'] ?>"
                            class="list-group-item list-group-item-action position-relative <?= ($selectedChat == $thread['chat_id']) ? 'active' : '' ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 text-truncate">
                                    <i class="fas fa-user me-2"></i><?= htmlspecialchars($thread['user_name'] ?? 'User') ?>
                                </h6>
                                <small class="<?= ($selectedChat == $thread['chat_id']) ? '' : 'text-muted' ?>">
                                    <?= $display_time ?>
                                </small>
                            </div>
                            <p class="mb-1 text-truncate">
                                <small>Subject:
                                    <strong><?= htmlspecialchars($thread['subject'] ?? 'No Subject') ?></strong></small>
                            </p>
                            <?php if ($thread['unread_count'] > 0): ?>
                                <span class="badge bg-danger rounded-pill position-absolute top-0 end-0 mt-2 me-2">
                                    <?= $thread['unread_count'] ?> New
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Chat Box -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100 d-flex flex-column">
                <?php if ($selectedChat > 0): ?>
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-comments me-2"></i> Conversation:
                            <?= htmlspecialchars($chatMessages[0]['subject'] ?? 'No Subject') ?>
                        </h5>
                    </div>

                    <div class="card-body flex-grow-1 overflow-auto p-3" style="max-height: 60vh;">
                        <?php foreach ($chatMessages as $msg):
                            $isAdmin = $msg['sender_name'] == 'Admin';
                            $displayName = $isAdmin ? 'Admin' : $msg['user_name'];
                            ?>
                            <div class="d-flex mb-3 <?= $isAdmin ? 'justify-content-end' : 'justify-content-start' ?>">
                                <div class="p-3 rounded-3 shadow-sm <?= $isAdmin ? 'bg-info text-white' : 'bg-light border' ?>"
                                    style="max-width: 80%;">
                                    <strong class="d-block mb-1">
                                        <?= htmlspecialchars($displayName) ?>
                                        <small class="text-black-50 ms-2" style="font-size:0.75rem;">
                                            <?= (new DateTime($msg['created_at']))->format('H:i') ?>
                                        </small>
                                    </strong>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card-footer bg-white border-top">
                        <form method="POST">
                            <input type="hidden" name="chat_id" value="<?= $selectedChat ?>">
                            <input type="hidden" name="subject"
                                value="<?= htmlspecialchars($chatMessages[0]['subject'] ?? '') ?>">
                            <div class="input-group">
                                <textarea name="reply_message" class="form-control" placeholder="Type your reply..."
                                    rows="2" required></textarea>
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-paper-plane me-1"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="lead">Select a chat thread to view messages and reply.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Scroll chat to bottom
    const chatBox = document.querySelector('.card-body.flex-grow-1');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
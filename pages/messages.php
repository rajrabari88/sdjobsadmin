<?php
include __DIR__ . '/../config/db.php';


// Count unread messages
$newCount = $conn->query("SELECT COUNT(*) as total FROM messages WHERE is_read = 0")->fetch_assoc()['total'];

// Fetch messages
$result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
?>

<div class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <h4 class="fw-bold text-dark mb-3 mb-md-0">
            📩 Inbox
            <span class="badge bg-danger rounded-pill ms-2 pulse-badge"><?= $newCount ?> New</span>
        </h4>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="list-group list-group-flush message-list-scroll">

            <?php while ($row = $result->fetch_assoc()) { ?>
                <a href="view_message.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action d-flex align-items-center 
                <?= $row['is_read'] == 0 ? 'bg-light-blue fw-bold' : 'text-muted' ?>">

                    <div class="avatar-circle bg-primary text-white me-3">
                        <?= strtoupper(substr($row['sender_name'], 0, 1)); ?>
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 <?= $row['is_read'] == 0 ? 'text-dark fw-bold' : '' ?>">
                                <?= $row['sender_name']; ?>
                            </h6>
                            <small class="text-muted">
                                <?= date("d M, h:i A", strtotime($row['created_at'])); ?>
                            </small>
                        </div>

                        <p class="mb-1 small text-truncate <?= $row['is_read'] == 0 ? 'text-dark' : '' ?>">
                            <strong>Subject:</strong> <?= $row['subject']; ?>
                        </p>

                        <?php if ($row['is_read'] == 0) { ?>
                            <span class="badge bg-warning small">New</span>
                        <?php } ?>
                    </div>
                </a>
            <?php } ?>

        </div>

    </div>
</div>

<style>
    .bg-light-blue {
        background-color: #f4f9ff !important;
    }

    .avatar-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .pulse-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    .message-list-scroll {
        max-height: 500px;
        overflow-y: auto;
    }
</style>
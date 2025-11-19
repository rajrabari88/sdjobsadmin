<?php
include __DIR__ . '/../config/db.php'; // Only DB include — no HTML

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current = isset($_GET['current']) ? (int)$_GET['current'] : 0;

$new_status = ($current == 1) ? 0 : 1;

$stmt = $conn->prepare("UPDATE jobs SET is_featured = ? WHERE id = ?");
$stmt->bind_param("ii", $new_status, $id);
$stmt->execute();
$stmt->close();

// ✅ IMPORTANT: No echo, no HTML, no spaces
header("Location: ../index.php?page=job_post");
exit;

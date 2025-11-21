<?php
include __DIR__ . '/../config/db.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $application_id = intval($_GET['id']);
    $new_status = $_GET['status']; // e.g., 'approved', 'rejected', 'interview', 'pending'

    // Validate status to prevent SQL injection for enum type
    $allowed_statuses = ['pending', 'approved', 'rejected', 'interview'];
    if (!in_array($new_status, $allowed_statuses)) {
        die("Invalid status provided.");
    }

    // Use prepared statements for security
    $stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $application_id);

    if ($stmt->execute()) {
        // Redirect back to the main applications list page
        header("Location: job_applications_list.php?success=status_updated");
        exit();
    } else {
        // Handle error
        die("Error updating status: " . $stmt->error);
    }

    $stmt->close();
} else {
    // If ID or status is missing, redirect back
    header("Location: job_applications_list.php");
    exit();
}
?>
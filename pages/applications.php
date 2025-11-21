<?php
ob_start(); // Start output buffering

include __DIR__ . '/../config/db.php'; // DB first

// ----- Handle status update BEFORE ANY OUTPUT -----
if (isset($_GET['id'], $_GET['status'])) {
    $app_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $status = strtolower($_GET['status']);
    $allowed_status = ['approved', 'rejected', 'interview', 'pending'];

    if ($app_id && in_array($status, $allowed_status) && isset($conn)) {
        // Get user_id and job_id
        $stmt = $conn->prepare("SELECT user_id, job_id FROM job_applications WHERE id=?");
        $stmt->bind_param("i", $app_id);
        $stmt->execute();
        $stmt->bind_result($user_id, $job_id);
        $stmt->fetch();
        $stmt->close();

        if ($user_id && $job_id) {
            // Update job_applications
            $stmt1 = $conn->prepare("UPDATE job_applications SET status=? WHERE id=?");
            $stmt1->bind_param("si", $status, $app_id);
            $stmt1->execute();
            $stmt1->close();

            // Update applications
            $stmt2 = $conn->prepare("UPDATE applications SET status=? WHERE user_id=? AND job_id=?");
            $stmt2->bind_param("sii", $status, $user_id, $job_id); // <-- FIXED
            $stmt2->execute();
            $stmt2->close();

            
        }
    }
}






// ----- Include sidebar after redirect logic -----
include __DIR__ . '/../includes/sidebar.php';

// ----- Function to get status badge -----
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'approved': return "bg-success text-white";
        case 'rejected': return "bg-danger text-white";
        case 'interview': return "bg-info text-dark";
        case 'pending':
        default: return "bg-warning text-dark";
    }
}

// ----- Fetch Applications with Users + Jobs -----
$sql = "SELECT 
            ja.id,
            ja.user_id,
            ja.job_id,
            ja.status,
            ja.created_at,
            ja.name AS applicant_name,
            ja.email AS applicant_email,
            ja.phone,
            ja.experience_level,
            ja.cover_letter,
            ja.additional_notes,
            u.avatar_url,
            u.resume_url,
            j.title AS job_title,
            j.company AS job_company
        FROM job_applications ja
        LEFT JOIN users u ON ja.user_id = u.id
        LEFT JOIN jobs j ON ja.job_id = j.id
        ORDER BY ja.id DESC";

$result = $conn->query($sql);
if (!$result) die("Error executing query: " . $conn->error);

// Flush buffer at the very end
ob_end_flush();
?>


<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="fw-bold text-primary">🎯 Job Applications Tracker</h3>
            <p class="text-muted mb-0">Track, filter, and review applications submitted by candidates.</p>
        </div>
    </div>

    <hr>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#ID</th>
                            <th>Applicant</th>
                            <th>Job Applied For</th>
                            <th>Status</th>
                            <th>Date Applied</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                // Sanitize data for safety
                                $app_id = htmlspecialchars($row['id']);
                                $applicant_name = htmlspecialchars($row['applicant_name']);
                                $applicant_email = htmlspecialchars($row['applicant_email']);
                                $job_title = htmlspecialchars($row['job_title'] ?? 'N/A');
                                $job_company = htmlspecialchars($row['job_company'] ?? 'Unknown Company');
                                $status_text = htmlspecialchars($row['status']);
                                $initials = strtoupper(substr($applicant_name, 0, 2));
                                $avatar = !empty($row['avatar_url']) 
                                                    ? htmlspecialchars($row['avatar_url']) 
                                                    : "https://placehold.co/40x40/495057/FFF?text=" . $initials;
                                $badge_class = getStatusBadge($status_text);
                                $date_applied = date("d M Y", strtotime($row['created_at']));
                            ?>
                            <tr>
                                <td><?= $app_id ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $avatar ?>" class="rounded-circle me-3 border" width="40" height="40" alt="<?= $applicant_name ?>'s avatar">
                                        <div>
                                            <span class="fw-semibold text-dark"><?= $applicant_name ?></span><br>
                                            <small class="text-muted"><?= $applicant_email ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium text-info"><?= $job_title ?></span>
                                    <span class="text-muted small"> at <?= $job_company ?></span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2 <?= $badge_class ?> fw-bold"><?= ucfirst($status_text) ?></span>
                                </td>
                                <td><?= $date_applied ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="?page=applications&id=<?= $app_id ?>&status=approved" class="btn btn-sm btn-success me-1" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </a>
                                    <a href="?page=applications&id=<?= $app_id ?>&status=interview" class="btn btn-sm btn-info me-1" title="Interview">
                                        <i class="bi bi-telephone"></i>
                                    </a>
                                    <a href="?page=applications&id=<?= $app_id ?>&status=rejected" class="btn btn-sm btn-danger me-1" title="Reject">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#viewModal<?= $app_id ?>" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="viewModal<?= $app_id ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Application Details - <?= $applicant_name ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3"><strong>Name:</strong> <?= $applicant_name ?></div>
                                                <div class="col-md-6 mb-3"><strong>Email:</strong> <?= $applicant_email ?></div>
                                                <div class="col-md-6 mb-3"><strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?></div>
                                                <div class="col-md-6 mb-3"><strong>Experience Level:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($row['experience_level']) ?></span></div>
                                            </div>
                                            <hr>
                                            <p class="mb-1"><strong>Job Applied:</strong></p>
                                            <p class="lead text-info">
                                                <?= $job_title ?> 
                                                <small class="text-muted"> at <?= $job_company ?></small>
                                            </p>
                                            <p class="mb-1"><strong>Status:</strong> <span class="badge <?= $badge_class ?>"><?= ucfirst($status_text) ?></span></p>
                                            <p class="mb-3"><strong>Date Applied:</strong> <?= $date_applied ?></p>
                                            
                                            <hr>
                                            
                                            <p class="fw-semibold mb-1">Cover Letter:</p>
                                            <div class="p-3 border rounded mb-3 bg-light text-wrap" style="white-space: pre-wrap;">
                                                <?= nl2br(htmlspecialchars($row['cover_letter'] ?? 'No cover letter provided.')) ?>
                                            </div>
                                            
                                            <p class="fw-semibold mb-1">Additional Notes (Internal):</p>
                                            <div class="p-3 border rounded mb-3 bg-light text-wrap" style="white-space: pre-wrap;">
                                                <?= nl2br(htmlspecialchars($row['additional_notes'] ?? 'No additional notes.')) ?>
                                            </div>

                                            <?php if (!empty($row['resume_url'])): ?>
                                                <p class="mt-3">
                                                    <a href="<?= htmlspecialchars($row['resume_url']) ?>" target="_blank" class="btn btn-outline-dark">
                                                        <i class="bi bi-file-earmark-arrow-down-fill me-2"></i>Download/View Resume
                                                    </a>
                                                </p>
                                            <?php endif; ?>
                                            
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No Job Applications Found
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Close the database connection 
if (isset($conn)) $conn->close();
ob_end_flush(); // Flush buffer and send output
?>
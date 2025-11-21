<?php
include __DIR__ . '/../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: job_applications_list.php");
    exit();
}

$app_id = intval($_GET['id']);

// Fetch detailed application data
$sql = "SELECT 
            ja.*,
            u.name AS user_name,
            u.email AS user_email,
            u.phone AS user_phone,
            u.avatar_url,
            u.resume_url, /* Resume link from the user's profile */
            j.title AS job_title,
            j.company AS job_company
        FROM job_applications ja
        LEFT JOIN users u ON ja.user_id = u.id
        LEFT JOIN jobs j ON ja.job_id = j.id
        WHERE ja.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $app_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
$stmt->close();

if (!$application) {
    die("Application not found.");
}

// Get user documents from the user_documents table
$docs_sql = "SELECT file_name, file_url FROM user_documents WHERE user_id = ?";
$docs_stmt = $conn->prepare($docs_sql);
$docs_stmt->bind_param("i", $application['user_id']);
$docs_stmt->execute();
$docs_result = $docs_stmt->get_result();
$documents = $docs_result->fetch_all(MYSQLI_ASSOC);
$docs_stmt->close();

// Helper function for status badge (can be put in a common file)
function getStatusBadgeDetails($status) {
    switch(strtolower($status)) {
        case 'approved': return "bg-success text-white";
        case 'rejected': return "bg-danger text-white";
        case 'interview': return "bg-info text-dark";
        case 'pending':
        default: return "bg-warning text-dark";
    }
}

$badge_class = getStatusBadgeDetails($application['status']);
$avatar_url = $application['avatar_url'] 
    ? $application['avatar_url'] 
    : "https://placehold.co/80x80/495057/FFF?text=" . strtoupper(substr($application['user_name'],0,2));
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-dark">Application Details #<?php echo $application['id']; ?></h1>
            <p class="text-muted">Review of application for **<?php echo $application['job_title']; ?>** at **<?php echo $application['job_company']; ?>**</p>
        </div>
        <div>
            <a href="job_applications_list.php" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left"></i> Back to List</a>
            <span class='badge rounded-pill fs-6 px-3 py-2 <?php echo $badge_class; ?>'>Status: <?php echo ucfirst($application['status']); ?></span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-3">Applicant Information</h4>
                    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                        <img src="<?php echo $avatar_url; ?>" class="rounded-circle me-4" width="80" height="80" alt="Applicant Avatar">
                        <div>
                            <h5 class="mb-0 fw-semibold"><?php echo $application['user_name']; ?></h5>
                            <p class="text-muted mb-0"><i class="bi bi-envelope me-2"></i><?php echo $application['user_email']; ?></p>
                            <p class="text-muted mb-0"><i class="bi bi-phone me-2"></i><?php echo $application['user_phone'] ?: 'N/A'; ?></p>
                        </div>
                    </div>

                    <h5 class="fw-semibold mt-4 mb-2">Application Details</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between">
                            **Job Title:** <span><?php echo $application['job_title']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            **Date Applied:** <span><?php echo date("d M Y h:i A", strtotime($application['created_at'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            **Experience Level:** <span><?php echo $application['experience_level']; ?></span>
                        </li>
                    </ul>
                    
                    <h5 class="fw-semibold mt-4 mb-2">Cover Letter / Message</h5>
                    <p class="alert alert-light border border-light-subtle p-3">
                        <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                    </p>
                    
                    <?php if (!empty($application['additional_notes'])): ?>
                    <h5 class="fw-semibold mt-4 mb-2">Additional Notes</h5>
                    <p class="alert alert-secondary p-3">
                        <?php echo nl2br(htmlspecialchars($application['additional_notes'])); ?>
                    </p>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary mb-3"><i class="bi bi-file-earmark-lock me-2"></i> Application Status Actions</h5>
                    <div class="d-grid gap-2">
                        <a href='app_action.php?id=<?php echo $app_id; ?>&status=approved' class='btn btn-success btn-lg'><i class="bi bi-check-circle me-2"></i> Approve</a>
                        <a href='app_action.php?id=<?php echo $app_id; ?>&status=interview' class='btn btn-info btn-lg text-dark'><i class="bi bi-calendar-check me-2"></i> Schedule Interview</a>
                        <a href='app_action.php?id=<?php echo $app_id; ?>&status=rejected' class='btn btn-danger btn-lg'><i class="bi bi-x-circle me-2"></i> Reject</a>
                        <a href='app_action.php?id=<?php echo $app_id; ?>&status=pending' class='btn btn-warning btn-lg text-dark'><i class="bi bi-hourglass me-2"></i> Set to Pending</a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3"><i class="bi bi-paperclip me-2"></i> Applicant Documents</h5>
                    
                    <?php 
                    // 1. User's main Resume (from users table)
                    if (!empty($application['resume_url'])): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <span class="fw-medium">Primary Resume</span>
                            <a href="<?php echo $application['resume_url']; ?>" class="btn btn-sm btn-outline-dark" download>
                                <i class="bi bi-download me-1"></i> Download
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php 
                    // 2. Other User Documents (from user_documents table)
                    if (!empty($documents)): ?>
                        <p class="text-muted small mt-3 mb-1">Uploaded Supporting Files:</p>
                        <?php foreach ($documents as $doc): ?>
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span><?php echo htmlspecialchars($doc['file_name']); ?></span>
                                <a href="<?php echo $doc['file_url']; ?>" class="btn btn-sm btn-outline-primary" download>
                                    <i class="bi bi-download me-1"></i> Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php 
                    // No documents found
                    if (empty($application['resume_url']) && empty($documents)): ?>
                        <div class="alert alert-light text-center">
                            No documents uploaded for this user.
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
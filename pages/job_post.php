<?php
// ===== Include Database Connection =====
include __DIR__ . '/../config/db.php';

// Success/Error Message Variable
$message = '';

// ===== Handle Job Form Submission =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $company = $_POST['company'] ?? '';
    $job_type = $_POST['type'] ?? '';
    $location = $_POST['location'] ?? '';

    $salary_min = filter_var($_POST['salary_min'] ?? 0, FILTER_VALIDATE_INT);
    $salary_max = filter_var($_POST['salary_max'] ?? 0, FILTER_VALIDATE_INT);
    $experience = $_POST['experience'] ?? '';
    $category = $_POST['category'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;


    $logo_text = strtoupper(substr($company, 0, 2));
    $salary_display = "₹" . number_format($salary_min) . " - ₹" . number_format($salary_max);

    $sql = "INSERT INTO jobs (title, company, location, salary_min, salary_max, salary_display, type, logo_text, description, requirements, experience, category, is_featured)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "sssiisssssssii",
            $title,
            $company,
            $location,
            $salary_min,
            $salary_max,
            $salary_display,
            $job_type,
            $logo_text,
            $description,
            $requirements,
            $experience,
            $category,
            $is_featured
        );

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ <strong>Success!</strong> Job posted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ <strong>Error!</strong> Could not post job. (' . $stmt->error . ')
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }
}

// ===== Pagination =====
$limit = 10;
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$start = ($page - 1) * $limit;

$total_result = $conn->query("SELECT COUNT(*) AS total FROM jobs");
$total_jobs = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_jobs / $limit);

$jobs = $conn->query("SELECT id, title, company, location, salary_display, type, is_featured, created_at 
                      FROM jobs 
                      ORDER BY created_at DESC 
                      LIMIT $start, $limit");

?>

<div class="container py-4">
    <div class="p-4 p-md-5 bg-white rounded-5 shadow-lg border border-light-subtle">

        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <h2 class="fw-bolder text-dark mb-1">💼 Job Post Manager</h2>
                <p class="text-muted mb-0">Create new listings and oversee all posted jobs.</p>
            </div>

            <!-- ✅ Add Button to Show/Hide Form -->
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="collapse"
                data-bs-target="#jobForm">
                <i class="bi bi-plus-circle-fill me-2"></i> Add New Job Post
            </button>
        </div>

        <?= $message ?>

        <!-- ✅ Form Now Collapsible (FORM CODE SAME AS BEFORE) -->
        <div class="collapse" id="jobForm">
            <h3 class="mb-4 pt-3 fw-bold text-primary"><i class="bi bi-plus-circle-fill me-2"></i> Post A New Job</h3>

            <form class="row g-4 mb-5 pb-5 border-bottom border-light-subtle" method="POST" action="">

                <div class="col-md-6">
                    <label class="form-label fw-medium">Job Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-4" name="title" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Posting Company <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-4" name="company" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Job Type <span class="text-danger">*</span></label>
                    <select class="form-select rounded-4" name="type" required>
                        <option selected disabled value="">Select Type</option>
                        <option value="Full-Time">Full-Time</option>
                        <option value="Part-Time">Part-Time</option>
                        <option value="Contract">Contract</option>
                        <option value="Internship">Internship</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Location <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-4" name="location" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Category</label>
                    <input type="text" class="form-control rounded-4" name="category">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Experience</label>
                    <input type="text" class="form-control rounded-4" name="experience">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Salary Range (Min)</label>
                    <input type="number" class="form-control rounded-4" name="salary_min">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Salary Range (Max)</label>
                    <input type="number" class="form-control rounded-4" name="salary_max">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium d-block">Featured Job</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1">
                        <label class="form-check-label">Mark as Featured</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Detailed Job Description</label>
                    <textarea class="form-control rounded-4" name="description" rows="5"></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Key Requirements / Skills</label>
                    <textarea class="form-control rounded-4" name="requirements" rows="4"></textarea>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow-lg">
                        <i class="bi bi-send-fill me-2"></i> Post Job Now
                    </button>
                    <button type="reset" class="btn btn-outline-secondary btn-lg ms-3 rounded-pill">Reset Form</button>
                </div>

            </form>
        </div>

        <h3 class="mb-4 fw-bold text-secondary pt-4">📋 Active Job Listings</h3>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle rounded-4 shadow-sm">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>#</th>
                        <th>Job Title & Type</th>
                        <th>Company & Location</th>
                        <th>Salary</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($jobs->num_rows > 0):
                        $count = 1;
                        while ($job = $jobs->fetch_assoc()): ?>
                            <tr>
                                <td><?= $count++ ?></td>
                                <td><?= $job['title'] ?><br><small class="badge bg-info text-dark"><?= $job['type'] ?></small>
                                </td>
                                <td><?= $job['company'] ?><br><small class="text-muted"><?= $job['location'] ?></small></td>
                                <td class="text-success fw-bold"><?= $job['salary_display'] ?></td>
                                <td><?= date("M d, Y", strtotime($job['created_at'])) ?></td>
                                <td class="text-center">

                                    <!-- ⭐ Featured Toggle Button -->
                                    <?php if ($job['is_featured'] == 1): ?>
                                        <a href="pages/toggle_feature.php?id=<?= $job['id'] ?>&current=1"
                                            class="btn btn-sm btn-warning rounded-pill">⭐ Featured</a>
                                    <?php else: ?>
                                        <a href="pages/toggle_feature.php?id=<?= $job['id'] ?>&current=0"
                                            class="btn btn-sm btn-outline-warning rounded-pill">☆ Make Featured</a>
                                    <?php endif; ?>


                                    <!-- Edit & Delete -->
                                    <a href="?page=edit_job&id=<?= $job['id'] ?>"
                                        class="btn btn-sm btn-info text-white rounded-pill"><i class="bi bi-pencil"></i></a>
                                    <a href="?page=delete_job&id=<?= $job['id'] ?>" class="btn btn-sm btn-danger rounded-pill"
                                        onclick="return confirm('Delete this job?');"><i class="bi bi-trash"></i></a>

                                </td>

                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">😔 No active jobs found. Post one!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ✅ Pagination -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=job_post&p=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

    </div>
</div>
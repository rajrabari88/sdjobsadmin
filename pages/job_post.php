<?php
// Note: Assuming 'config/db.php' is correctly included and sets up $conn

include __DIR__ . '/../config/db.php';

// --- Default message variable ---
$message = '';

/* ================= DELETE JOB ================= */
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $job_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($job_id) {
        // Use prepared statements for security
        $stmt_delete = $conn->prepare("DELETE FROM jobs WHERE id=?");
        $stmt_delete->bind_param("i", $job_id);
        if ($stmt_delete->execute()) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><strong>Success!</strong> Job deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-octagon-fill me-2"></i><strong>Error!</strong> Failed to delete job: ' . htmlspecialchars($stmt_delete->error) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
}

/* ================= ADD / EDIT JOB ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize POST data
    $job_id       = $_POST['job_id'] ?? 0;
    $title        = trim($_POST['title'] ?? '');
    $company      = trim($_POST['company'] ?? '');
    $job_type     = $_POST['type'] ?? '';
    $location     = trim($_POST['location'] ?? '');
    $salary_min   = filter_var($_POST['salary_min'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    $salary_max   = filter_var($_POST['salary_max'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    $experience   = $_POST['experience'] ?? '';
    $category     = $_POST['category'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $description  = $_POST['description'] ?? '';
    $is_featured  = isset($_POST['is_featured']) ? 1 : 0;
    
    // Derived fields
    $logo_text      = strtoupper(substr($company, 0, 2));
    $salary_display = "₹" . number_format($salary_min) . " - ₹" . number_format($salary_max);

    $success_message = '';
    $error_message = '';

    if ($job_id > 0) {
        // UPDATE
        $sql = "UPDATE jobs SET title=?, company=?, location=?, salary_min=?, salary_max=?, salary_display=?, type=?, logo_text=?, description=?, requirements=?, experience=?, category=?, is_featured=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        // Bind types: string, string, string, integer, integer, string, string, string, string, string, string, string, integer, integer
        $stmt->bind_param("sssiissssssiii", $title, $company, $location, $salary_min, $salary_max, $salary_display, $job_type, $logo_text, $description, $requirements, $experience, $category, $is_featured, $job_id);
        if ($stmt->execute()) {
            $success_message = 'Job updated successfully.';
        } else {
            $error_message = 'Failed to update job: ' . htmlspecialchars($stmt->error);
        }
    } else {
        // INSERT
        $sql = "INSERT INTO jobs (title, company, location, salary_min, salary_max, salary_display, type, logo_text, description, requirements, experience, category, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        // Bind types: string, string, string, integer, integer, string, string, string, string, string, string, string, integer
        $stmt->bind_param("sssiisssssssi", $title, $company, $location, $salary_min, $salary_max, $salary_display, $job_type, $logo_text, $description, $requirements, $experience, $category, $is_featured);
        if ($stmt->execute()) {
            $success_message = 'Job posted successfully.';
        } else {
            $error_message = 'Failed to post job: ' . htmlspecialchars($stmt->error);
        }
    }

    // Set message based on operation result
    if ($success_message) {
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><strong>Success!</strong> ' . $success_message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } elseif ($error_message) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i><strong>Error!</strong> ' . $error_message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

/* ================= SEARCH + FILTER ================= */
$search_term = $_GET['search'] ?? '';
$filter_type = $_GET['job_type_filter'] ?? '';
$where = []; $params = []; $types = '';

if ($search_term) {
    // Search in title, company, or location
    $where[] = "(title LIKE ? OR company LIKE ? OR location LIKE ?)";
    $like = "%$search_term%";
    $params = array_merge($params, [$like,$like,$like]);
    $types .= "sss";
}
if ($filter_type) {
    // Filter by job type
    $where[] = "type=?";
    $params[] = $filter_type;
    $types .= "s";
}
$where_sql = count($where) ? " WHERE " . implode(" AND ", $where) : "";

/* ================= PAGINATION ================= */
$limit = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$start = ($page - 1) * $limit;

// Count total jobs matching the criteria
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM jobs $where_sql");
if ($stmt_count) {
    if($params) $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $total_jobs = $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();
} else $total_jobs = 0;
$total_pages = max(1, ceil($total_jobs / $limit));

/* ================= FETCH JOBS ================= */
$sql_jobs = "SELECT * FROM jobs $where_sql ORDER BY created_at DESC LIMIT ?,?";
$stmt_jobs = $conn->prepare($sql_jobs);
$types_jobs = $types."ii";
$params_jobs = array_merge($params, [$start,$limit]);
// The ... operator unpacks the array elements as arguments
$stmt_jobs->bind_param($types_jobs, ...$params_jobs); 
$stmt_jobs->execute();
$jobs = $stmt_jobs->get_result();

// --- JOB TYPES ARRAY (for forms and filters) ---
$job_types = ["Full-Time", "Part-Time", "Contract", "Internship", "Remote"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Post Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Custom styles for a modern look */
        body {
            background-color: #f8f9fa; /* Light grey background */
        }
        .card-manager {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
        }
        .btn {
            border-radius: 0.5rem;
        }
        .table thead th {
            background-color: #0d6efd; /* Primary blue header */
            color: white;
            border-bottom: 2px solid #0d6efd;
        }
        .table tbody tr:hover {
            background-color: #e2f4ff; /* Light hover effect */
        }
        .featured-badge {
            background-color: #ffc107;
            color: #212529;
            font-size: 0.75rem;
            padding: 0.25em 0.5em;
            border-radius: 0.375rem;
        }
        /* Style for the logo text circle in the table (optional) */
        .logo-circle {
            display: inline-flex;
            width: 40px;
            height: 40px;
            background-color: #343a40;
            color: white;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
            font-size: 0.9rem;
        }
        /* Responsive table improvement */
        @media (max-width: 767.98px) {
            .table-responsive .table {
                font-size: 0.85rem;
            }
            .table-responsive th, .table-responsive td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>

<div class="container ">
    <div class="card card-manager p-4 p-md-5">

        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <h2 class="fw-bold mb-0 text-primary"><i class="bi bi-briefcase-fill me-2"></i>Job Post Manager</h2>
                <p class="text-muted mb-0">Manage all job listings. Total Jobs: <strong><?= $total_jobs ?></strong></p>
            </div>
            <button class="btn btn-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addJobModal">
                <i class="bi bi-plus-circle-fill me-1"></i> Add New Job
            </button>
        </div>

        <?= $message ?>

        <form class="row g-3 mb-4 p-3 bg-light rounded-3 shadow-sm" method="GET">
            <input type="hidden" name="page" value="job_post"> <div class="col-lg-6 col-md-5">
                <label for="search" class="form-label fw-bold">Search (Title, Company, Location)</label>
                <input type="text" name="search" id="search" value="<?= htmlspecialchars($search_term) ?>" class="form-control" placeholder="Search...">
            </div>
            
            <div class="col-lg-3 col-md-4">
                <label for="job_type_filter" class="form-label fw-bold">Job Type Filter</label>
                <select name="job_type_filter" id="job_type_filter" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach($job_types as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>" <?= $filter_type==$t?'selected':'' ?>>
                            <?= htmlspecialchars($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-lg-3 col-md-3 d-flex align-items-end">
                <button class="btn btn-dark w-50 me-2"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="?page=job_post" class="btn btn-outline-secondary w-50"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
            </div>
        </form>

        <h3 class="mb-3 text-secondary"><i class="bi bi-list-task me-1"></i> Job Listings (Page <?= $page ?> of <?= $total_pages ?>)</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle border">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col" class="text-nowrap">Job Title & Type</th>
                        <th scope="col">Company & Location</th>
                        <th scope="col">Salary</th>
                        <th scope="col" class="text-nowrap">Posted Date</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $count = $start + 1;
                if($jobs->num_rows > 0):
                    while($job = $jobs->fetch_assoc()):
                ?>
                    <tr class="<?= $job['is_featured'] ? 'table-warning bg-opacity-10' : '' ?>">
                        <th scope="row"><?= $count++ ?></th>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="logo-circle bg-primary me-2"><?= htmlspecialchars($job['logo_text']) ?></span>
                                <div>
                                    <strong class="text-primary"><?= htmlspecialchars($job['title']) ?></strong>
                                    <br><span class="badge bg-secondary"><?= htmlspecialchars($job['type']) ?></span>
                                    <?php if($job['is_featured']): ?>
                                        <span class="featured-badge ms-1"><i class="bi bi-star-fill"></i> Featured</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($job['company']) ?></strong>
                            <br><small class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($job['location']) ?></small>
                        </td>
                        <td class="text-success fw-bold text-nowrap"><?= $job['salary_display'] ?></td>
                        <td class="text-nowrap"><?= date("M d, Y", strtotime($job['created_at'])) ?></td>
                        <td class="text-center text-nowrap">
                            <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#editJobModal<?= $job['id'] ?>">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <a href="?page=job_post&action=delete&id=<?= $job['id'] ?>" 
                               onclick="return confirm('Are you sure you want to permanently delete the job: <?= addslashes(htmlspecialchars($job['title'])) ?>?');" 
                               class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash-fill"></i> Delete
                            </a>
                        </td>
                    </tr>

                    <div class="modal fade" id="editJobModal<?= (int)$job['id'] ?>" tabindex="-1" aria-labelledby="editJobModalLabel<?= (int)$job['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content card-manager">
                                <div class="modal-header bg-info text-white rounded-top-4">
                                    <h5 class="modal-title fw-bold" id="editJobModalLabel<?= (int)$job['id'] ?>">
                                        <i class="bi bi-pencil-fill me-2"></i>Edit Job: <?= htmlspecialchars($job['title']) ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <form method="POST" class="row g-4">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                                        <div class="col-md-6">
                                            <label for="edit_title_<?= $job['id'] ?>" class="form-label fw-bold">Job Title*</label>
                                            <input type="text" name="title" id="edit_title_<?= $job['id'] ?>" class="form-control" required value="<?= htmlspecialchars($job['title']) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_company_<?= $job['id'] ?>" class="form-label fw-bold">Company*</label>
                                            <input type="text" name="company" id="edit_company_<?= $job['id'] ?>" class="form-control" required value="<?= htmlspecialchars($job['company']) ?>">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label for="edit_type_<?= $job['id'] ?>" class="form-label fw-bold">Job Type*</label>
                                            <select name="type" id="edit_type_<?= $job['id'] ?>" class="form-select" required>
                                                <?php foreach($job_types as $t): ?>
                                                    <option value="<?= htmlspecialchars($t) ?>" <?= $job['type']==$t?'selected':'' ?>><?= $t ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="edit_location_<?= $job['id'] ?>" class="form-label fw-bold">Location*</label>
                                            <input type="text" name="location" id="edit_location_<?= $job['id'] ?>" class="form-control" required value="<?= htmlspecialchars($job['location']) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="edit_category_<?= $job['id'] ?>" class="form-label fw-bold">Category</label>
                                            <input type="text" name="category" id="edit_category_<?= $job['id'] ?>" class="form-control" value="<?= htmlspecialchars($job['category'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="edit_experience_<?= $job['id'] ?>" class="form-label fw-bold">Experience</label>
                                            <input type="text" name="experience" id="edit_experience_<?= $job['id'] ?>" class="form-control" value="<?= htmlspecialchars($job['experience'] ?? '') ?>">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="edit_salary_min_<?= $job['id'] ?>" class="form-label fw-bold">Salary Min (₹)</label>
                                            <input type="number" name="salary_min" id="edit_salary_min_<?= $job['id'] ?>" class="form-control" value="<?= htmlspecialchars($job['salary_min']) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_salary_max_<?= $job['id'] ?>" class="form-label fw-bold">Salary Max (₹)</label>
                                            <input type="number" name="salary_max" id="edit_salary_max_<?= $job['id'] ?>" class="form-control" value="<?= htmlspecialchars($job['salary_max']) ?>">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="edit_description_<?= $job['id'] ?>" class="form-label fw-bold">Description</label>
                                            <textarea name="description" id="edit_description_<?= $job['id'] ?>" class="form-control" rows="4"><?= htmlspecialchars($job['description']) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="edit_requirements_<?= $job['id'] ?>" class="form-label fw-bold">Requirements</label>
                                            <textarea name="requirements" id="edit_requirements_<?= $job['id'] ?>" class="form-control" rows="4"><?= htmlspecialchars($job['requirements']) ?></textarea>
                                        </div>
                                        
                                        <div class="col-12 d-flex justify-content-between align-items-center">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="is_featured" id="edit_is_featured_<?= $job['id'] ?>" class="form-check-input" value="1" <?= $job['is_featured']?'checked':'' ?>>
                                                <label class="form-check-label fw-bold" for="edit_is_featured_<?= $job['id'] ?>">Mark as Featured</label>
                                            </div>
                                            <button type="submit" class="btn btn-primary px-5"><i class="bi bi-arrow-clockwise me-2"></i>Update Job</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php 
                    endwhile; 
                else: 
                ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-x-octagon-fill me-2"></i> No jobs found matching your criteria.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-4" aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php 
                // Base URL for pagination links
                $base="?page=job_post";
                if($search_term) $base.="&search=".urlencode($search_term);
                if($filter_type) $base.="&job_type_filter=".urlencode($filter_type);
                ?>

                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $base ?>&p=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php for($i=1;$i<=$total_pages;$i++): ?>
                    <li class="page-item <?= $i==$page?'active':'' ?>">
                        <a class="page-link" href="<?= $base ?>&p=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $base ?>&p=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</div>

<div class="modal fade" id="addJobModal" tabindex="-1" aria-labelledby="addJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-manager">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="addJobModalLabel"><i class="bi bi-clipboard-plus-fill me-2"></i> Post a New Job</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form class="row g-4" method="POST">
                    <input type="hidden" name="job_id" value="0">
                    
                    <div class="col-md-6">
                        <label for="add_title" class="form-label fw-bold">Job Title*</label>
                        <input type="text" name="title" id="add_title" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="add_company" class="form-label fw-bold">Company*</label>
                        <input type="text" name="company" id="add_company" class="form-control" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="add_type" class="form-label fw-bold">Job Type*</label>
                        <select name="type" id="add_type" class="form-select" required>
                            <?php foreach($job_types as $t) echo "<option value=\"".htmlspecialchars($t)."\">$t</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="add_location" class="form-label fw-bold">Location*</label>
                        <input type="text" name="location" id="add_location" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label for="add_category" class="form-label fw-bold">Category</label>
                        <input type="text" name="category" id="add_category" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="add_experience" class="form-label fw-bold">Experience</label>
                        <input type="text" name="experience" id="add_experience" class="form-control">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="add_salary_min" class="form-label fw-bold">Salary Min (₹)</label>
                        <input type="number" name="salary_min" id="add_salary_min" class="form-control" value="0">
                    </div>
                    <div class="col-md-6">
                        <label for="add_salary_max" class="form-label fw-bold">Salary Max (₹)</label>
                        <input type="number" name="salary_max" id="add_salary_max" class="form-control" value="0">
                    </div>
                    
                    <div class="col-12">
                        <label for="add_description" class="form-label fw-bold">Description</label>
                        <textarea name="description" id="add_description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="col-12">
                        <label for="add_requirements" class="form-label fw-bold">Requirements</label>
                        <textarea name="requirements" id="add_requirements" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_featured" id="add_is_featured" class="form-check-input" value="1">
                            <label class="form-check-label fw-bold" for="add_is_featured">Mark as Featured</label>
                        </div>
                        <button class="btn btn-primary px-5"><i class="bi bi-send-fill me-2"></i>Post Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close statements and connection
if (isset($stmt_jobs)) $stmt_jobs->close();
if (isset($conn)) $conn->close();
?>
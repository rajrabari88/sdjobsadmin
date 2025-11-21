<?php
// ... Your PHP code for data fetching remains the same ...
include __DIR__ . '/../config/db.php';

// ==== FETCH STATS ==== //
$total_jobs = $conn->query("SELECT COUNT(*) AS total FROM jobs")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_applications = $conn->query("SELECT COUNT(*) AS total FROM job_applications")->fetch_assoc()['total'];

// ==== FETCH LATEST JOBS ==== //
$latest_jobs = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 5");

// ==== FETCH RECENT USERS ==== //
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 4");
?>

<div class="container-fluid">
    
    <div class="row g-4 mb-5 justify-content-start">
        
        <div class="col-6 col-lg-4 col-md-6">
            <div class="card p-4 shadow-sm border-start border-primary border-5 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-uppercase text-muted mb-1 small">Total Jobs</p>
                        <h3 class="fw-bold text-primary mb-0"><?= $total_jobs ?></h3>
                    </div>
                    <i class="fas fa-briefcase fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-md-6">
            <div class="card p-4 shadow-sm border-start border-success border-5 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-uppercase text-muted mb-1 small">Total Users</p>
                        <h3 class="fw-bold text-success mb-0"><?= $total_users ?></h3>
                    </div>
                    <i class="fas fa-users fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-md-6">
            <div class="card p-4 shadow-sm border-start border-warning border-5 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-uppercase text-muted mb-1 small">Applications</p>
                        <h3 class="fw-bold text-warning mb-0"><?= $total_applications ?></h3>
                    </div>
                    <i class="fas fa-file-alt fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        
    </div>

    ---

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h4 class="fw-semibold text-dark">🧾 Latest Job Posts</h4>
        <a href="index.php?page=jobs" class="btn btn-outline-secondary btn-sm">View All <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="table-responsive shadow-sm rounded-3 border">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark"> <tr>
                    <th scope="col">#</th>
                    <th scope="col">Job Title</th>
                    <th scope="col">Company</th>
                    <th scope="col">Type</th>
                    <th scope="col">Location</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($latest_jobs && $latest_jobs->num_rows > 0):
                    $count = 1;
                    while ($job = $latest_jobs->fetch_assoc()):
                ?>
                    <tr>
                        <th scope="row"><?= $count++ ?></th>
                        <td><a href="#" class="text-decoration-none fw-medium text-primary"><?= htmlspecialchars($job['title']) ?></a></td>
                        <td><?= htmlspecialchars($job['company']) ?></td>
                        <td><span class="badge rounded-pill bg-success-subtle text-success fw-medium"><?= htmlspecialchars($job['type']) ?></span></td> 
                        <td><?= htmlspecialchars($job['location']) ?></td>
                        <td class="text-muted small"><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No recent jobs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    ---

    <div class="mt-5 mb-4 d-flex justify-content-between align-items-center">
        <h4 class="fw-semibold text-dark">👥 Recent Registered Users</h4>
        <a href="index.php?page=users" class="btn btn-outline-secondary btn-sm">View All <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
        <?php
        if ($recent_users && $recent_users->num_rows > 0):
            while ($user = $recent_users->fetch_assoc()):
        ?>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card user-card text-center p-4 shadow-sm h-100 border-0">
                    <img src="https://placehold.co/80x80/007bff/ffffff?text=<?= strtoupper(substr($user['name'], 0, 2)) ?>"
                         class="rounded-circle mb-3 mx-auto border border-3 border-light" width="80" height="80" alt="User Avatar">
                    <h6 class="mb-1 fw-semibold text-truncate"><?= htmlspecialchars($user['name']) ?></h6>
                    <p class="text-muted small mb-0 text-truncate">#ID: <?= htmlspecialchars($user['id'] ?? 'N/A') ?></p>
                </div>
            </div>
        <?php
            endwhile;
        else:
        ?>
            <div class="col-12"><p class="text-muted text-center py-3">No recent users found.</p></div>
        <?php endif; ?>
    </div>
</div>
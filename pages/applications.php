<?php 
include __DIR__ . '/../config/db.php';

// Fetch applications with user and job info
$sql = "SELECT 
            applications.id,
            applications.status,
            applications.created_at,
            users.name AS user_name,
            users.email AS user_email,
            users.avatar_url,
            jobs.title AS job_title,
            jobs.company AS job_company
        FROM applications
        LEFT JOIN users ON applications.user_id = users.id
        LEFT JOIN jobs ON applications.job_id = jobs.id
        ORDER BY applications.id DESC";

$result = $conn->query($sql);
?>

<div class="p-4 p-md-5 bg-white rounded-4 shadow-sm border border-light-subtle">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark">Job Applications</h3>
            <p class="text-muted mb-0">Track, filter, and review applications submitted by candidates.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-download me-2"></i> Export Data
        </button>
    </div>

    <hr>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Applicant</th>
                    <th>Job Title</th>
                    <th>Status</th>
                    <th>Date Applied</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {

                    // Avatar Image Default
                    $avatar = $row['avatar_url'] ? $row['avatar_url'] : "https://placehold.co/35x35/EEE/666?text=" . strtoupper(substr($row['user_name'],0,2));

                    // Status Badge Color
                    switch($row['status']) {
                        case 'approved': $badge="bg-success text-white"; break;
                        case 'rejected': $badge="bg-danger text-white"; break;
                        case 'interview': $badge="bg-info text-dark"; break;
                        default: $badge="bg-warning text-dark"; break;
                    }

                    echo "
                    <tr>
                        <td>{$row['id']}</td>
                        
                        <td>
                            <div class='d-flex align-items-center'>
                                <img src='$avatar' class='rounded-circle me-3' width='35' height='35'>
                                <div>
                                    <span class='fw-semibold'>{$row['user_name']}</span><br>
                                    <small class='text-muted'>{$row['user_email']}</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class='fw-medium text-primary'>{$row['job_title']}</span>
                            <span class='text-muted small'> at {$row['job_company']}</span>
                        </td>

                        <td><span class='badge rounded-pill px-3 py-2 $badge'>{$row['status']}</span></td>

                        <td>" . date("d M Y", strtotime($row['created_at'])) . "</td>

                        <td class='text-center'>
                            <a href='app_action.php?id={$row['id']}&status=approved' class='btn btn-sm btn-outline-success me-1'>
                                <i class='bi bi-check-lg'></i>
                            </a>
                            <a href='app_action.php?id={$row['id']}&status=rejected' class='btn btn-sm btn-outline-danger me-1'>
                                <i class='bi bi-x-lg'></i>
                            </a>
                            <a href='view_application.php?id={$row['id']}' class='btn btn-sm btn-outline-secondary'>
                                <i class='bi bi-eye'></i>
                            </a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center text-muted py-4'>No Applications Found</td></tr>";
            }
            ?>
            </tbody>

        </table>
    </div>

</div>

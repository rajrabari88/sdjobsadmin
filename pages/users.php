<?php
// ===== Include Database Connection =====
include __DIR__ . '/../config/db.php';

// Fetch Users
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);
?>

<div class="container-fluid mt-4">
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">

        <!-- Header -->
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <h4 class="mb-3 mb-md-0 fw-bold text-dark">👥 Registered Users</h4>

                <div class="d-flex flex-wrap align-items-center">
                    <input type="text" class="form-control me-3 mb-2 mb-md-0 shadow-sm" 
                    placeholder="Search by Name or Email..." style="width: 250px;">

                    <button class="btn btn-primary fw-semibold shadow-sm">
                        <i class="bi bi-person-plus"></i> Add New User
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email / Phone</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php 
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                // If resume or details missing → Uncomplete Profile
                                $status = ($row['resume_url']) 
                                    ? '<span class="badge rounded-pill bg-success-subtle text-success fw-semibold px-3 py-2">Active</span>' 
                                    : '<span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold px-3 py-2">Incomplete</span>';

                                echo "
                                <tr class='table-row-hover'>
                                    <td>#{$row['id']}</td>
                                    <td class='fw-semibold'>{$row['name']}</td>
                                    <td>
                                        {$row['email']}
                                        <span class='d-block small text-primary'>{$row['phone']}</span>
                                    </td>
                                    <td>" . date("d M Y", strtotime($row['created_at'])) . "</td>
                                    <td>$status</td>
                                    <td class='text-center'>
                                        <div class='btn-group'>
                                            <a href='view_user.php?id={$row['id']}' class='btn btn-sm btn-outline-info' title='View Profile'>
                                                <i class='bi bi-person-vcard'></i>
                                            </a>
                                            <a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-outline-warning' title='Edit/Update'>
                                                <i class='bi bi-pencil-square'></i>
                                            </a>
                                            <a href='delete_user.php?id={$row['id']}' onclick='return confirm(\"Confirm Delete User?\")' class='btn btn-sm btn-outline-danger' title='Delete User'>
                                                <i class='bi bi-trash'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted py-4'>No Users Found</td></tr>";
                        }
                        ?>
                    </tbody>

                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-white border-top p-3 text-muted small">
            Showing <?php echo $result->num_rows; ?> users.
        </div>

    </div>
</div>

<!-- Custom Styling -->
<style>
.bg-success-subtle { background-color: #d1e7dd !important; }
.bg-danger-subtle { background-color: #f8d7da !important; }
.bg-warning-subtle { background-color: #fff3cd !important; }
.table-row-hover:hover { background-color: #f1f7ff !important; transition: 0.2s; }
.btn-group .btn { border-radius: 6px !important; margin-right: 4px; }
.card:hover { transform: translateY(-2px); box-shadow: 0 0.75rem 1.25rem rgba(0,0,0,0.08); }
</style>

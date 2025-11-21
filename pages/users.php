<?php
ob_start();
// index.php - Single Page CRUD (PHP 8.2+)

// 1. Database Connection
include __DIR__ . '/../config/db.php';

$message = '';

/* ========================================================
    2. HANDLE ADD / EDIT USER
======================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST['action'] ?? '';

    // ADD USER
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? null);
        $password = trim($_POST['password'] ?? '');

        if ($name && $email && $password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
            $stmt->execute()
                ? $message = "Success: New user added!"
                : $message = "Error: " . $stmt->error;

            $stmt->close();
        } else {
            $message = "Error: Name, Email, and Password are required.";
        }
    }

    // EDIT USER
    if ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($id && $name && $email) {

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, password=? WHERE id=?");
                $stmt->bind_param("ssssi", $name, $email, $phone, $hashed_password, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
                $stmt->bind_param("sssi", $name, $email, $phone, $id);
            }

            $stmt->execute()
                ? $message = "Success: User #$id updated!"
                : $message = "Error updating user: " . $stmt->error;

            $stmt->close();
        } else {
            $message = "Error: Missing required fields.";
        }
    }


}

/* ========================================================
    3. HANDLE DELETE USER (GET)
======================================================== */
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {

    header("Content-Type: application/json");

    $id = (int) $_GET['id'];

    if ($id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "msg" => "User #$id deleted."]);
        } else {
            echo json_encode(["status" => "error", "msg" => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "msg" => "Invalid user ID."]);
    }

    exit;
}


// Message
$message = $_GET['msg'] ?? '';

/* ========================================================
    4. FETCH USERS
======================================================== */
$result = $conn->query("SELECT id, name, email, phone, created_at, resume_url FROM users ORDER BY id DESC");

$user_count = $result ? $result->num_rows : 0;

$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

?>

<div class="container-fluid" style="max-width: 1400px;">

    <?php if (!empty($message)): ?>
        <div class="alert alert-info alert-dismissible fade show mt-3">
            <strong>Status:</strong> <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-lg border-0 rounded-4 overflow-hidden mt-3">

        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="fw-bold mb-2">👥 Registered Users</h4>

            <div class="d-flex gap-3 flex-wrap">
                <input id="searchUser" type="text" class="form-control shadow-sm"
                    placeholder="Search by Name or Email..." style="width:250px;">
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus"></i> Add New User
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" id="usersTable">
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

                        <?php if ($user_count > 0): ?>
                            <?php foreach ($users as $row): ?>

                                <?php
                                $status = !empty($row['resume_url'])
                                    ? '<span class="badge bg-success-subtle text-success px-3 py-2">Active</span>'
                                    : '<span class="badge bg-warning-subtle text-warning px-3 py-2">Incomplete</span>';

                                $user_phone = !empty($row['phone']) ? $row['phone'] : 'N/A';
                                $resume = $row['resume_url'] ?? '';
                                ?>

                                <tr id="user-<?= $row['id'] ?>">
                                    <td>#<?= $row['id'] ?></td>

                                    <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>

                                    <td>
                                        <span><?= htmlspecialchars($row['email']) ?></span>
                                        <span class="d-block small text-primary"><?= $user_phone ?></span>
                                    </td>

                                    <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>

                                    <td><?= $status ?></td>

                                    <td class="text-center">
                                        <div class="btn-group">

                                            <button class="btn btn-sm btn-outline-info viewUserBtn" data-bs-toggle="modal"
                                                data-bs-target="#viewUserModal" data-id="<?= $row['id'] ?>"
                                                data-name="<?= htmlspecialchars($row['name']) ?>"
                                                data-email="<?= htmlspecialchars($row['email']) ?>"
                                                data-phone="<?= $user_phone ?>"
                                                data-joined="<?= date("d M Y H:i", strtotime($row['created_at'])) ?>"
                                                data-status="<?= strip_tags($status) ?>" data-resume="<?= $resume ?>">
                                                <i class="bi bi-person-vcard"></i>
                                            </button>

                                            <button class="btn btn-sm btn-outline-warning editUserBtn" data-bs-toggle="modal"
                                                data-bs-target="#editUserModal" data-id="<?= $row['id'] ?>"
                                                data-name="<?= htmlspecialchars($row['name']) ?>"
                                                data-email="<?= htmlspecialchars($row['email']) ?>"
                                                data-phone="<?= $row['phone'] ?? '' ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <button class="btn btn-sm btn-outline-danger deleteUserBtn"
                                                data-id="<?= $row['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>

                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-info-circle"></i> No Users Found
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white text-muted small p-3">
            Showing <?= $user_count ?> users.
        </div>

    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">User Profile <span id="viewId"></span></h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewUserContent"></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit User</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">

                    <label class="fw-semibold">Name</label>
                    <input type="text" name="name" id="edit-name" class="form-control mb-3" required>

                    <label class="fw-semibold">Email</label>
                    <input type="email" name="email" id="edit-email" class="form-control mb-3" required>

                    <label class="fw-semibold">Phone</label>
                    <input type="text" name="phone" id="edit-phone" class="form-control mb-3">

                    <label class="fw-semibold">New Password (Optional)</label>
                    <input type="password" name="password" class="form-control mb-3">

                    <button class="btn btn-warning w-100 fw-bold">Update User</button>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New User</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">

                    <label class="fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control mb-3" required>

                    <label class="fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control mb-3" required>

                    <label class="fw-semibold">Phone</label>
                    <input type="text" name="phone" class="form-control mb-3">

                    <label class="fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control mb-3" required>

                    <button class="btn btn-primary w-100 fw-bold">Save User</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

    // Search Filter
    document.getElementById('searchUser').addEventListener('input', function () {
        let filter = this.value.toLowerCase();
        document.querySelectorAll('#usersTable tbody tr').forEach(tr => {
            tr.style.display = tr.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

    // View User Modal
    document.querySelectorAll('.viewUserBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            const resumeLink = d.resume ? `<a href="${d.resume}" target="_blank">View Resume</a>` : "N/A";

            document.getElementById('viewId').innerText = "#" + d.id;

            document.getElementById('viewUserContent').innerHTML = `
            <div class="list-group">
                <div class="list-group-item"><strong>Name:</strong> ${d.name}</div>
                <div class="list-group-item"><strong>Email:</strong> ${d.email}</div>
                <div class="list-group-item"><strong>Phone:</strong> ${d.phone}</div>
                <div class="list-group-item"><strong>Joined:</strong> ${d.joined}</div>
                <div class="list-group-item"><strong>Status:</strong> ${d.status}</div>
                <div class="list-group-item"><strong>Resume:</strong> ${resumeLink}</div>
            </div>
        `;
        });
    });

    // Edit Modal Populate
    document.querySelectorAll('.editUserBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            document.getElementById('edit-id').value = d.id;
            document.getElementById('edit-name').value = d.name;
            document.getElementById('edit-email').value = d.email;
            document.getElementById('edit-phone').value = d.phone;
        });
    });

    // Delete User
    document.querySelectorAll('.deleteUserBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            let id = this.dataset.id;

            if (confirm("Delete user #" + id + "?")) {

                fetch("?action=delete&id=" + id)
                    .then(res => res.json())
                    .then(data => {
                        alert(data.msg);
                        if (data.status === "success") {
                            document.getElementById("user-" + id)?.remove();
                        }
                    });
            }
        });
    });
</script>

</body>

</html>
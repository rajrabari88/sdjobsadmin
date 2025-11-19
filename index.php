<?php
session_start();

// Agar admin login nahi hai to redirect to login.php
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'dashboard';

// Include layout files
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- MAIN CONTENT -->
<div class="content-wrapper">
    <header class="admin-navbar d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
        <button class="btn btn-primary d-lg-none" id="sidebarToggle">
            <i class="bi bi-list fs-5"></i> Menu
        </button>

        <div class="user-info ms-auto">
            <i class="bi bi-person-circle me-2"></i> Welcome, <strong>Admin</strong>
            <a href="logout.php" class="btn btn-outline-danger btn-sm ms-3">Logout</a>
        </div>
    </header>

    <main class="container-fluid mt-4">
        <h1 class="page-title mb-4 text-capitalize"><?= ucfirst(str_replace('_', ' ', $page)) ?></h1>
        <?php
        $pageFile = "pages/{$page}.php";
        if (file_exists($pageFile)) {
            include $pageFile;
        } else {
            echo "<div class='alert alert-warning'>Page not found!</div>";
        }
        ?>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButton = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.classList.toggle('offcanvas-open', sidebar.classList.contains('show'));
        }

        if (toggleButton && sidebar && overlay) {
            toggleButton.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
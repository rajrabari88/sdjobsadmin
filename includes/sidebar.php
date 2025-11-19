<div class="sidebar d-flex flex-column" id="adminSidebar">
    <div class="logo">
        <i class="bi bi-gear-wide-connected me-2"></i>SDJobs Admin
    </div>

    <nav class="flex-grow-1">
        <a href="index.php?page=dashboard" class="<?= isActive('dashboard', $currentPage) ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="index.php?page=job_post" class="<?= isActive('job_post', $currentPage) ?>">
            <i class="bi bi-briefcase"></i> Job Posts
        </a>
        <a href="index.php?page=applications" class="<?= isActive('applications', $currentPage) ?>">
            <i class="bi bi-file-earmark-text"></i> Applications
        </a>
        <!-- <a href="index.php?page=employers" class="<?= isActive('employers', $currentPage) ?>">
            <i class="bi bi-building"></i> Employers
        </a> -->
        <a href="index.php?page=messages" class="<?= isActive('messages', $currentPage) ?>">
            <i class="bi bi-chat-dots"></i> Messages
        </a>
        <a href="index.php?page=users" class="<?= isActive('users', $currentPage) ?>">
            <i class="bi bi-people"></i> Users
        </a>
    </nav>

    <a href="logout.php" class="logout-link">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

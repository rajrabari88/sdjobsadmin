<?php
// includes/header.php
$currentPage = $_GET['page'] ?? 'dashboard';

// Function declaration safe check
if (!function_exists('isActive')) {
    function isActive($page, $currentPage) {
        return $page === $currentPage ? 'active' : '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDJobs Admin Panel - <?= ucfirst(str_replace('_', ' ', $currentPage)) ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        body.offcanvas-open { overflow: hidden; }

        /* Sidebar */
        .sidebar {
            width: 280px;
            min-height: 100vh;
            background: #1e3a8a;
            color: #fff;
            position: fixed;
            left: 0; top: 0;
            box-shadow: 6px 0 15px rgba(0,0,0,0.3);
            transition: all 0.4s ease-in-out;
            z-index: 1020;
            display: flex;
            flex-direction: column;
        }
        .sidebar .logo {
            padding: 20px 25px;
            font-weight: 700;
            font-size: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #d1d5db;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            border-left: 5px solid transparent;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left: 5px solid #60a5fa;
            transform: translateX(5px);
        }
        .sidebar a.active {
            background: rgba(255,255,255,0.15);
            color: #ffeb3b;
            border-left: 5px solid #ffeb3b;
            font-weight: 600;
            box-shadow: inset 0 0 10px rgba(255,235,59,0.2);
        }
        .sidebar .logout-link {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #fca5a5 !important;
        }
        .sidebar .logout-link:hover {
            background: rgba(220,53,69,0.2);
            border-left: 5px solid #dc3545;
            color: #fff !important;
        }

        /* Content Wrapper */
        .content-wrapper {
            margin-left: 280px;
            transition: margin-left 0.4s ease-in-out;
            padding-bottom: 30px;
        }

        .admin-navbar {
            background: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .page-title {
            color: #1e3a8a;
            font-weight: 700;
            margin: 25px 0;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar { left: -280px; }
            .sidebar.show { left: 0; }
            .content-wrapper { margin-left: 0; }
            .overlay {
                position: fixed; top: 0; left: 0;
                width: 100%; height: 100%;
                background: rgba(0,0,0,0.6);
                z-index: 1010;
                display: none;
            }
            .overlay.show { display: block; }
        }
        
    </style>
</head>
<body>
<div class="overlay" id="sidebarOverlay"></div>

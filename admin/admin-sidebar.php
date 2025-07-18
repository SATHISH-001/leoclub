<?php
require_once 'db.php';
require_once 'functions.php';

$pdo = getPDO();

// Get counts for each section
$eventsCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$newsCount = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$bearersCount = $pdo->query("SELECT COUNT(*) FROM office_bearers")->fetchColumn();
$participantsCount = $pdo->query("SELECT COUNT(*) FROM participants")->fetchColumn();
$messagesCount = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$membersCount = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
?>

<!-- Sidebar Wrapper -->
    <main class="col-md-1 ms-sm-auto col-lg-10 px-md-4">
<div class="sidebar-wrapper">
    <!-- Sidebar Implementation -->
    <div class="sidebar bg-dark" id="sidebarMenu">
        <div class="position-sticky pt-3">
            <!-- Mobile close button -->
            <button class="btn btn-link text-white d-md-none position-absolute end-0 me-2" id="sidebarClose">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Sidebar links with scrollable container -->
            <div class="sidebar-content">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == '#' ? 'active' : '' ?>" href="#">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['manage-contact.ph', 'view-contact.php']) ? 'active' : '' ?>" href="manage-contact.php">
                            <i class="fas fa-calendar-alt me-2"></i> Contact Info
                            <span class="badge bg-primary float-end"><?= $eventsCount ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['manage-index.php', '#']) ? 'active' : '' ?>" href="manage-index.php">
                            <i class="fas fa-calendar-alt me-2"></i> Manage Home
                            <span class="badge bg-primary float-end"><?= $eventsCount ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['manage-news.php', 'add-news.php']) ? 'active' : '' ?>" href="manage-news.php">
                            <i class="fas fa-newspaper me-2"></i> News
                            <span class="badge bg-primary float-end"><?= $newsCount ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['manage-office-bearers.php', 'add-bearer.php']) ? 'active' : '' ?>" href="manage-office-bearers.php">
                            <i class="fas fa-users me-2"></i> Office Bearers
                            <span class="badge bg-primary float-end"><?= $bearersCount ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['manage-registered-participants.php', 'add-members.php']) ? 'active' : '' ?>" href="manage-registered-participants.php">
                            <i class="fas fa-images me-2"></i> Event Members
                            <span class="badge bg-primary float-end"><?= $participantsCount ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['manage-admin.php', '']) ? 'active' : '' ?>" href="manage-admin.php">
                            <i class="fas fa-user me-2"></i> Admin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['manage-members.php']) ? 'active' : '' ?>" href="manage-members.php">
                            <i class="fas fa-user-friends me-2"></i> Members
                            <span class="badge bg-primary float-end"><?= $membersCount ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'manage-events.php' ? 'active' : '' ?>" href="manage-events.php">
                            <i class="fas fa-home me-2"></i> Manage Events
                        </a>
                    </li>
                      <li class="nav-item">
                        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'manage-footer.php' ? 'active' : '' ?>" href="manage-footer.php">
                            <i class="fas fa-home me-2"></i> Footer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'manage-about.php' ? 'active' : '' ?>" href="manage-about.php">
                            <i class="fas fa-cog me-2"></i> About
                        </a>
                    </li>
                    <!-- <li class="nav-item mt-3">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li> -->
                </ul>
            </div>
        </div>
    </div>

    <!-- Mobile Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
</div>

<style>
    /* Sidebar Wrapper */
    .sidebar-wrapper {
        position: relative;
    }

    /* Sidebar Styles */
    .sidebar {
        min-height: calc(100vh - 56px);
        width: 250px;
        background-color: #212529;
        position: fixed;
        top: 56px; /* Below navbar */
        left: 0;
        z-index: 1000;
        overflow-y: auto;
        transition: transform 0.3s ease;
    }

    /* Sidebar content container */
    .sidebar-content {
        width: 250px; /* Fixed width matching sidebar */
        /* overflow-x: auto; Enable horizontal scrolling */
        white-space: nowrap; /* Prevent wrapping */
    }

    /* Desktop styles */
    @media (min-width: 768px) {
        .sidebar {
            transform: translateX(0) !important;
        }
        
        /* Push main content to the right */
        .main-content {
            margin-left: 250px;
        }
    }

    /* Mobile styles */
    @media (max-width: 767.98px) {
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
    }

    /* Backdrop styles */
    .sidebar-backdrop {
        position: fixed;
        top: 56px;
        left: 0;
        width: 100%;
        height: calc(100vh - 56px);
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
    }

    .sidebar-backdrop.show {
        display: block;
    }

    /* Nav link styles */
    .nav-link {
        border-radius: 4px;
        margin: 2px 0;
        padding: 8px 12px !important;
        transition: all 0.2s;
        white-space: nowrap; /* Prevent text wrapping */
        /* overflow: hidden; */
        text-overflow: ellipsis; /* Add ellipsis for overflow text */
        display: inline-block; /* Needed for proper width */
        width: 100%; /* Take full width of parent */
    }

    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .nav-link.active {
        background-color: #495057;
        color: #fff !important;
    }

    .nav-link i {
        width: 20px;
        text-align: center;
    }

    .badge {
        font-size: 0.7rem;
        padding: 3px 6px;
        margin-top: 2px;
        float: right;
    }

    /* body.sidebar-open {
        overflow: hidden;
    } */

    /* Custom scrollbar for sidebar */
    .sidebar-content::-webkit-scrollbar {
        height: 6px; /* Horizontal scrollbar height */
    }

    .sidebar-content::-webkit-scrollbar-track {
        background: #2c3e50;
    }

    .sidebar-content::-webkit-scrollbar-thumb {
        background: #4e73df;
        border-radius: 3px;
    }

    .sidebar-content::-webkit-scrollbar-thumb:hover {
        background: #3a56b4;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebarMenu');
    const sidebarToggle = document.getElementById('sidebarToggle'); // Should be in your navbar
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    
    // Function to initialize sidebar
    function initSidebar() {
        if (window.innerWidth >= 768) {
            // Desktop - always show sidebar
            sidebar.classList.add('show');
            sidebarBackdrop.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        } else {
            // Mobile - hide sidebar by default
            sidebar.classList.remove('show');
        }
    }
    
    // Initialize sidebar
    initSidebar();
    
    // Toggle sidebar on mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarBackdrop.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
    }
    
    // Close sidebar
    function closeSidebar() {
        sidebar.classList.remove('show');
        sidebarBackdrop.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    }
    
    // Close button
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }
    
    // Backdrop click
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }
    
    // Close when clicking a link (mobile only)
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                closeSidebar();
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        initSidebar();
    });

    // Initialize tooltips for truncated items
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('.nav-link'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        // Only add tooltip if content is truncated
        if (tooltipTriggerEl.scrollWidth > tooltipTriggerEl.clientWidth) {
            tooltipTriggerEl.setAttribute('data-bs-toggle', 'tooltip');
            tooltipTriggerEl.setAttribute('title', tooltipTriggerEl.textContent.trim());
            new bootstrap.Tooltip(tooltipTriggerEl);
        }
    });
});
</script>
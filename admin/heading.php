<?php
if (!isset($_SESSION) && !headers_sent()) {
    session_start();
}
require_once 'preloader.php';

// Dummy data fallback (will be overridden by actual login)
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = 'Guest';
}

$pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEO CLUB OF ACGCET - <?= $pageTitle ?></title>
    <link rel="icon" type="image/webp" href="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #f1c40f;
            --secondary-color: #fffbea;
            --accent-color: #f39c12;
            --dark-color: #2c3e50;
            --light-color: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary-color);
            color: var(--dark-color);
            overflow-x: hidden;
            padding-top: 80px;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #e1b000);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            z-index: 1100;
        }

        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            color: var(--light-color);
            margin-right: auto;
        }

        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        /* User Dropdown Styles */
        .user-dropdown {
            position: relative;
            display: flex;
            align-items: center;
        }

        .user-toggle {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 20px;
            transition: all 0.3s;
        }

        .user-toggle:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-name {
            margin-right: 5px;
            font-weight: 500;
        }

        .user-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            min-width: 200px;
            /* box-shadow: 0 8px 16px rgba(0,0,0,0.1); */
            border-radius: 8px;
            padding: 10px 0;
            z-index: 1000;
            display: none;
            animation: fadeIn 0.2s ease-in-out;
        }

        .user-menu.show {
            display: block;
        }

        .user-menu-item {
            padding: 8px 15px;
            display: flex;
            align-items: center;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.2s;
        }

        .user-menu-item:hover {
            background-color: #f8f9fa;
        }

        .user-menu-item i {
            width: 20px;
            margin-right: 10px;
            color: var(--accent-color);
        }

        .user-menu-divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 5px 0;
        }

        .logout-item {
            color: #dc3545;
        }

        .logout-item i {
            color: #dc3545;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .user-name {
                display: none;
            }
            
            .user-toggle {
                padding: 5px;
            }
        }

        /* Overlay for mobile */
        .user-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <div class="navbar-container">
                <button class="sidebar-toggle" id="sidebarToggle" type="button">
                    <i class="fas fa-bars"></i>
                </button>
                
                <a class="navbar-brand" href="index.php">
                    <img src="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" alt="LEO Club Logo">
                    LEO CLUB ADMIN
                </a>
                
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <div class="user-dropdown">
                        <button class="user-toggle" id="userToggle">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div class="user-menu" id="userMenu">
                            <div class="user-menu-item">
                                <i class="fas fa-user-circle"></i>
                                <?= htmlspecialchars($_SESSION['admin_name']) ?>
                            </div>
                            <div class="user-menu-divider"></div>
                            <a href="../index.php" class="user-menu-item">
                                <i class="fas fa-home"></i>
                                Return to Home
                            </a>
                            <div class="user-menu-divider"></div>
                            <a href="../logout.php" class="user-menu-item logout-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Overlay for mobile menu -->
    <div class="user-menu-overlay" id="userMenuOverlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userToggle = document.getElementById('userToggle');
            const userMenu = document.getElementById('userMenu');
            const userMenuOverlay = document.getElementById('userMenuOverlay');

            if (userToggle && userMenu) {
                // Toggle user menu
                userToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('show');
                    userMenuOverlay.style.display = userMenu.classList.contains('show') ? 'block' : 'none';
                });

                // Close menu when clicking overlay
                userMenuOverlay.addEventListener('click', function() {
                    userMenu.classList.remove('show');
                    userMenuOverlay.style.display = 'none';
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userToggle.contains(e.target) && !userMenu.contains(e.target)) {
                        userMenu.classList.remove('show');
                        userMenuOverlay.style.display = 'none';
                    }
                });

                // Close menu on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        userMenu.classList.remove('show');
                        userMenuOverlay.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
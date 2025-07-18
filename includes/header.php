<?php
if (!isset($_SESSION) && !headers_sent()) {
    session_start();
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/preloader.php';

$buffer = ob_get_contents();
ob_end_clean();

$pageTitle = $pageTitle ?? 'LEO CLUB OF ACGCET';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEO CLUB OF ACGCET - <?php echo $pageTitle; ?></title>
    <link rel="icon" href="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #f1c40f;
            --secondary-color: #fffbea;
            --accent-color: #f39c12;
            --dark-color: #2c3e50;
            --light-color: #ffffff;
            --dropdown-bg: rgba(255, 255, 255, 0.95);
            --sidebar-bg: #1a1a1a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary-color);
            color: var(--dark-color);
            overflow-x: hidden;
            padding-top: 60px;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #e1b000);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            min-height: 60px;
        }

        .navbar-container {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .navbar-brand-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex-grow: 1;
            position: relative;
            padding-right: 15px;
        }

        .navbar-brand-container {
            display: flex;
            align-items: center;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--light-color);
            display: flex;
            align-items: center;
            line-height: 1.2;
            margin-right: 15px;
        }

        .navbar-brand img {
            height: 35px;
            margin-right: 8px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .user-greeting {
            color: var(--light-color);
            font-size: 0.8rem;
            white-space: nowrap;
            display: block;
            line-height: 1.2;
            margin-top: 2px;
            margin-left: 43px; /* Align with text */
        }

        /* Mobile Toggle Button */
        .mobile-toggle {
            display: block;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            padding: 5px 10px;
            margin-right: 10px;
            z-index: 1040;
        }

        @media (min-width: 992px) {
            .mobile-toggle {
                display: none;
            }
        }

        /* Desktop Navigation */
        .desktop-nav {
            display: none;
        }

        @media (min-width: 992px) {
            .desktop-nav {
                display: flex;
                align-items: center;
            }
            
            .navbar-brand-wrapper {
                flex-direction: row;
                align-items: center;
                flex-grow: 0;
            }
            
            .user-greeting {
                margin-left: 15px;
                margin-top: 0;
                font-size: 0.9rem;
            }
        }

        .nav-link {
            font-weight: 500;
            padding: 8px 15px !important;
            margin: 0 5px;
            border-radius: 8px;
            color: rgba(255,255,255,0.9) !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover, .nav-link:focus {
            color: var(--light-color) !important;
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }

        main {
            min-height: calc(100vh - 60px);
            padding-top: 20px;
        }

        /* User Dropdown Styles */
        .user-dropdown {
            position: relative;
        }

        .user-toggle {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 8px;
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

        .user-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--dropdown-bg);
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px 0;
            z-index: 1000;
            display: none;
        }

        .user-menu.show {
            display: block;
            animation: fadeIn 0.2s ease-in-out;
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

        @media (min-width: 992px) {
            body {
                padding-top: 80px;
            }
            
            .navbar {
                min-height: 80px;
                padding: 0.75rem 0;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
            }
            
            .navbar-brand img {
                height: 40px;
                margin-right: 10px;
            }

            .user-greeting {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <div class="navbar-container">
                    <button class="mobile-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="navbar-brand-wrapper">
                        <div class="navbar-brand-container">
                            <a class="navbar-brand" href="index.php">
                                <img src="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" alt="LEO Club Logo">
                                LEO CLUB OF ACGCET
                            </a>
                        </div>
                        <?php if (isLoggedIn()): ?>
                        <div class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Desktop Navigation - Only visible on large screens -->
                    <div class="desktop-nav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                                <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                            </li>
                            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>">
                                <a class="nav-link" href="events.php"><i class="fas fa-calendar-alt me-1"></i> Events</a>
                            </li>
                            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : ''; ?>">
                                <a class="nav-link" href="news.php"><i class="fas fa-newspaper me-1"></i> News</a>
                            </li>
                            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'register-participants.php' ? 'active' : ''; ?>">
                                <a class="nav-link" href="register-participants.php"><i class="fas fa-images me-1"></i> Event Register</a>
                            </li>
                            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'office-bearers.php' ? 'active' : ''; ?>">
                                <a class="nav-link" href="office-bearers.php"><i class="fas fa-users me-1"></i> Office Bearers</a>
                            </li>
                            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                                <a class="nav-link" href="contact.php"><i class="fas fa-envelope me-1"></i> Contact</a>
                            </li>
                            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                                <a class="nav-link" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a>
                            </li>
                            <?php if (isLoggedIn()): ?>
                            <li class="nav-item user-dropdown">
                                <button class="user-toggle" id="userToggle">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span>Account <i class="fas fa-chevron-down ms-1"></i></span>
                                </button>
                                <div class="user-menu" id="userMenu">
                                    <div class="user-menu-item">
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($_SESSION['name']) ?>
                                    </div>
                                    <div class="user-menu-divider"></div>
                                    <a href="admin/" class="user-menu-item">
                                        <i class="fas fa-tachometer-alt"></i>
                                        Dashboard
                                    </a>
                                    <a href="profile.php" class="user-menu-item">
                                        <i class="fas fa-user"></i>
                                        Profile
                                    </a>
                                    <div class="user-menu-divider"></div>
                                    <a href="logout.php" class="user-menu-item logout-item">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Logout
                                    </a>
                                </div>
                            </li>
                            <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" href="login.php">
                                    <i class="fas fa-sign-in-alt me-1"></i> Login
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="container my-4">
        <!-- Your page content starts here -->
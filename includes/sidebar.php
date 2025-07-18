<div id="sidebar">
    <div class="sidebar-content">
        <?php if (isLoggedIn()): ?>
        <div class="sidebar-user">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($_SESSION['name']) ?></div>
                <div class="user-email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="sidebar-menu">
            <a href="index.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="events.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Events</span>
            </a>
            <a href="news.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i>
                <span>News</span>
            </a>
            <a href="register-participants.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'register-participants.php' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i>
                <span>Event Register</span>
            </a>
            <a href="office-bearers.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'office-bearers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Office Bearers</span>
            </a>
            <a href="contact.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Contact</span>
            </a>
            <a href="about.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                <i class="fas fa-info-circle"></i>
                <span>About</span>
            </a>
            
            <?php if (isLoggedIn()): ?>
            <div class="sidebar-divider"></div>
            <a href="admin/" class="sidebar-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="profile.php" class="sidebar-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="logout.php" class="sidebar-item logout-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
            <?php else: ?>
            <div class="sidebar-divider"></div>
            <a href="login.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Overlay for mobile sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const userToggle = document.getElementById('userToggle');
        const userMenu = document.getElementById('userMenu');
        
        // Toggle sidebar on mobile
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('show');
                sidebarOverlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : 'auto';
            });
        }

        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        }

        // Toggle user menu
        if (userToggle && userMenu) {
            userToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenu.classList.toggle('show');
            });
        }

        // Close menus when clicking outside
        document.addEventListener('click', function(e) {
            if (userToggle && !userToggle.contains(e.target) && userMenu && !userMenu.contains(e.target)) {
                userMenu.classList.remove('show');
            }
        });

        // Close sidebar when a nav link is clicked
        document.querySelectorAll('.sidebar-item').forEach(link => {
            link.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        });
    });
</script>

<style>
    #sidebar {
        position: fixed;
        top: 60px;
        left: -70%;
        width: 70%;
        height: calc(100vh - 60px);
        background-color: var(--sidebar-bg);
        z-index: 1020;
        transition: all 0.3s ease;
        overflow-y: auto;
    }

    #sidebar.show {
        left: 0;
    }

    .sidebar-overlay {
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,0.5);
        z-index: 1010;
        display: none;
    }

    .sidebar-content {
        padding: 20px;
        color: white;
    }

    .sidebar-user {
        display: flex;
        align-items: center;
        padding: 15px 0;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-user .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.5rem;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        margin-bottom: 3px;
    }

    .user-email {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .sidebar-menu {
        display: flex;
        flex-direction: column;
    }

    .sidebar-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        margin-bottom: 5px;
        transition: all 0.3s;
    }

    .sidebar-item i {
        width: 25px;
        font-size: 1.1rem;
        margin-right: 10px;
    }

    .sidebar-item:hover, .sidebar-item.active {
        background-color: rgba(241, 196, 15, 0.2);
        color: white;
    }

    .sidebar-item.active {
        font-weight: 500;
    }

    .sidebar-divider {
        height: 1px;
        background-color: rgba(255,255,255,0.1);
        margin: 15px 0;
    }

    .logout-item {
        color: #ff6b6b;
    }

    .logout-item:hover {
        background-color: rgba(255, 107, 107, 0.1);
    }

    @media (min-width: 992px) {
        #sidebar, .sidebar-overlay {
            display: none;
        }
    }
</style>
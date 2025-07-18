<?php
// Database connection
require_once 'db.php';
require_once 'functions.php';
require_once 'config.php';

// Get PDO connection
$pdo = getPDO();

// Get current footer settings
try {
    $query = "SELECT * FROM footer_settings WHERE id = 1";
    $stmt = $pdo->query($query);
    $footer = $stmt->fetch();
    
    // If no settings exist, use default values
    if (!$footer) {
        $footer = [
            'description' => 'The LEO Club of ACGCET is a premier student organization dedicated to community service and leadership development since 2010.',
            'email' => 'leoclubofacgcet@gmail.com',
            'phone' => '+91 6380172840',
            'instagram' => 'https://instagram.com/leo_club_of_acgcet',
            'copyright_text' => '© %Y% LEO Club of ACGCET. All Rights Reserved. Designed by sathish'
        ];
    }
    
    // Replace %Y% with current year in copyright text
    $footer['copyright_text'] = str_replace('%Y%', date('Y'), $footer['copyright_text']);
    
} catch (PDOException $e) {
    // Fallback values if database fails
    $footer = [
        'description' => 'The LEO Club of ACGCET is a premier student organization dedicated to community service and leadership development since 2010.',
        'email' => 'leoclubofacgcet@gmail.com',
        'phone' => '+91 6380172840',
        'instagram' => 'https://instagram.com/leo_club_of_acgcet',
        'copyright_text' => '© ' . date('Y') . ' LEO Club of ACGCET. All Rights Reserved. Designed by sathish'
    ];
}
?>
</main>

<!-- Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <img src="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" 
                     alt="LEO Club Logo" 
                     class="footer-logo">
                    
                <p><?php echo htmlspecialchars($footer['description']); ?></p>
                <div class="social-links mt-3">
                    <?php if (!empty($footer['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($footer['instagram']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                <div class="footer-links">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="events.php">Events</a></li>
                        <li><a href="news.php">News</a></li>
                        <li><a href="office-bearers.php">Our Team</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4">
                <div class="footer-links">
                    <h5>Contact Us</h5>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt me-2"></i> ACGCET Campus, Karaikudi</li>
                        <li><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($footer['email']); ?></li>
                        <li><i class="fas fa-phone-alt me-2"></i> <?php echo htmlspecialchars($footer['phone']); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12 text-center copyright">
                <p class="mb-0"><?php echo htmlspecialchars($footer['copyright_text']); ?></p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
    
<style>
         :root {
            --primary: #f1c40f;
            --primary-dark: #e1b000;
            --secondary: #2c3e50;
            --light: #ffffff;
            --light-bg: #fffbea;
            --accent: #f39c12;
            --text: #333333;
            --text-light: #6c757d;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text);
            padding-top: 80px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
    .main-footer {
        background: linear-gradient(135deg, var(--secondary), #1a252f);
        color: var(--light);
        padding: 40px 0 20px;
    }
    
    .footer-logo {
        height: 60px;
        margin-bottom: 15px;
    }
    
    .footer-links h5 {
        font-weight: 600;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }
    
    .footer-links h5:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background: var(--primary);
    }
    
    .footer-links ul {
        list-style: none;
        padding-left: 0;
    }
    
    .footer-links li {
        margin-bottom: 10px;
    }
    
    .footer-links a {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .footer-links a:hover {
        color: var(--primary);
        padding-left: 5px;
    }
    
    .social-links a {
        display: inline-block;
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.1);
        color: var(--light);
        border-radius: 50%;
        text-align: center;
        line-height: 40px;
        margin-right: 10px;
        transition: all 0.3s ease;
    }
    
    .social-links a:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-3px);
    }
    
    .copyright {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding-top: 20px;
        margin-top: 30px;
        font-size: 0.9rem;
        color: rgba(255,255,255,0.5);
    }
</style>
    
<script>
    // Add shadow when scrolling
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.main-header');
        if (window.scrollY > 10) {
            header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
        } else {
            header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
        }
    });
    
    // Mobile menu close when clicking outside
    document.addEventListener('click', function(e) {
        const navCollapse = document.querySelector('.navbar-collapse');
        const navToggler = document.querySelector('.navbar-toggler');
        
        if (window.innerWidth <= 991.98 && !e.target.closest('.navbar') && navCollapse.classList.contains('show')) {
            navCollapse.classList.remove('show');
        }
    });
</script>
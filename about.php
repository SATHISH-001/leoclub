<?php 
$pageTitle = "About Us | LEO Club of ACGCET";
require_once 'includes/config.php';
require_once 'includes/header.php'; 

// Fetch content from database
$sections = [];
$achievements = [];
$teamMembers = [];

try {
    // Get main content sections
    $stmt = $pdo->query("SELECT section, title, content FROM about_content ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sections[$row['section']] = $row;
    }
    
    // Get achievements
    $stmt = $pdo->query("SELECT title, description, icon, color FROM about_achievements WHERE is_active = 1 ORDER BY display_order");
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get team members
    $stmt = $pdo->query("SELECT name, position, description, image_path FROM about_team WHERE is_active = 1 ORDER BY display_order");
    $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Default content if database fails
    $sections = [
        'mission' => [
            'title' => 'Our Mission',
            'content' => 'To empower young leaders through meaningful service opportunities that foster personal growth, community development, and global awareness.'
        ],
        'vision' => [
            'title' => 'Our Vision',
            'content' => 'To be recognized as the premier student-led organization that transforms compassionate ideas into impactful actions while developing tomorrow\'s leaders today.'
        ],
        'history' => [
            'title' => 'Our Journey',
            'content' => "Founded in 2010 under the mentorship of the local Lions Club, LEO Club of ACGCET has grown into one of the most active student organizations on campus.\n\nKey Milestones:\n- 2012: Won Best New Club Award\n- 2015: Launched Annual Community Service Week\n- 2018: Recognized as District Outstanding Club\n- 2021: Surpassed 10,000 volunteer hours"
        ]
    ];
}
?>
<link rel="icon" href="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" >
<main class="about-page">
    <!-- Hero Section -->
    <section class="about-hero py-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3 text-white">About LEO Club</h1>
                    <p class="lead mb-4 text-white-50">Leadership Through Service | Excellence Through Action</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#mission" class="btn btn-light btn-lg px-4">Our Purpose</a>
                        <a href="#team" class="btn btn-outline-light btn-lg px-4">Meet The Team</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section id="mission" class="py-5 bg-light">
        <div class="container py-4">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Our Core Identity</h2>
                <div class="section-divider mx-auto"></div>
                <p class="section-subtitle text-muted">What drives us forward</p>
            </div>
            
            <div class="row g-4">
                <!-- Mission -->
                <div class="col-md-6">
                    <div class="core-card card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="icon-wrapper bg-primary bg-opacity-10 p-3 rounded me-3 float-start">
                                <i class="fas fa-bullseye fa-2x text-primary"></i>
                            </div>
                            <h3 class="h4 mb-3"><?= htmlspecialchars($sections['mission']['title'] ?? 'Our Mission') ?></h3>
                            <p class="text-muted"><?= htmlspecialchars($sections['mission']['content'] ?? 'Default mission content') ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Vision -->
                <div class="col-md-6">
                    <div class="core-card card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="icon-wrapper bg-success bg-opacity-10 p-3 rounded me-3 float-start">
                                <i class="fas fa-eye fa-2x text-success"></i>
                            </div>
                            <h3 class="h4 mb-3"><?= htmlspecialchars($sections['vision']['title'] ?? 'Our Vision') ?></h3>
                            <p class="text-muted"><?= htmlspecialchars($sections['vision']['content'] ?? 'Default vision content') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- History Section -->
    <section class="py-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="pe-lg-4">
                        <div class="section-header mb-4">
                            <h2 class="section-title"><?= htmlspecialchars($sections['history']['title'] ?? 'Our Journey') ?></h2>
                            <div class="section-divider"></div>
                        </div>
                        <div class="text-muted">
                            <?= nl2br(htmlspecialchars($sections['history']['content'] ?? 'Default history content')) ?>
                        </div>
                        <div class="mt-4">
                            <a href="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp"  class="btn btn-outline-primary">View Our History</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow">
                         <img src="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" width="60px"
                             alt="LEO Club History" 
                             class="img-fluid object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievements Section -->
    <?php if (!empty($achievements)): ?>
    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Our Achievements</h2>
                <div class="section-divider mx-auto"></div>
                <p class="section-subtitle text-muted">Milestones we're proud of</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($achievements as $achievement): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="achievement-card card border-0 shadow-sm h-100">
                        <div class="card-body p-4 text-center">
                            <div class="icon-wrapper <?= htmlspecialchars($achievement['color']) ?>-soft mx-auto mb-4">
                                <i class="fas <?= htmlspecialchars($achievement['icon']) ?> fa-2x text-<?= htmlspecialchars($achievement['color']) ?>"></i>
                            </div>
                            <h3 class="h5 mb-3"><?= htmlspecialchars($achievement['title']) ?></h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($achievement['description']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Values Section -->
    <section class="py-5">
        <div class="container py-4">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Our Core Values</h2>
                <div class="section-divider mx-auto"></div>
                <p class="section-subtitle text-muted">Principles that guide our actions</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="value-card card border-0 shadow-sm h-100">
                        <div class="card-body p-4 text-center">
                            <div class="icon-wrapper bg-primary-soft mx-auto mb-4">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <h3 class="h5 mb-3">Leadership</h3>
                            <p class="text-muted">Developing skills to serve effectively and inspire others</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-card card border-0 shadow-sm h-100">
                        <div class="card-body p-4 text-center">
                            <div class="icon-wrapper bg-success-soft mx-auto mb-4">
                                <i class="fas fa-hand-holding-heart fa-2x text-success"></i>
                            </div>
                            <h3 class="h5 mb-3">Service</h3>
                            <p class="text-muted">Putting community needs before personal interests</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-card card border-0 shadow-sm h-100">
                        <div class="card-body p-4 text-center">
                            <div class="icon-wrapper bg-info-soft mx-auto mb-4">
                                <i class="fas fa-lightbulb fa-2x text-info"></i>
                            </div>
                            <h3 class="h5 mb-3">Innovation</h3>
                            <p class="text-muted">Finding creative solutions to community challenges</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-card card border-0 shadow-sm h-100">
                        <div class="card-body p-4 text-center">
                            <div class="icon-wrapper bg-warning-soft mx-auto mb-4">
                                <i class="fas fa-star fa-2x text-warning"></i>
                            </div>
                            <h3 class="h5 mb-3">Excellence</h3>
                            <p class="text-muted">Striving for the highest standards in all we do</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <?php if (!empty($teamMembers)): ?>
    <section id="team" class="py-5 bg-light">
        <div class="container py-4">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Meet Our Team</h2>
                <div class="section-divider mx-auto"></div>
                <p class="section-subtitle text-muted">The passionate leaders behind our mission</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <?php foreach ($teamMembers as $member): ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="team-card card border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="avatar-wrapper mx-auto mb-4">
                                <img src="<?= htmlspecialchars($member['image_path'] ?? 'assets/images/team/default.jpg') ?>" 
                                     class="avatar-img rounded-circle" 
                                     alt="<?= htmlspecialchars($member['name']) ?>">
                            </div>
                            <h3 class="h5 mb-1"><?= htmlspecialchars($member['name']) ?></h3>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($member['position']) ?></p>
                            <p class="text-muted small"><?= htmlspecialchars($member['description'] ?? 'Dedicated to service and leadership') ?></p>
                            <div class="social-links mt-3">
                                <a href="#" class="text-muted mx-1"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#" class="text-muted mx-1"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="text-muted mx-1"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="register.php" class="btn btn-primary px-4">Join Our Team</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container py-4 text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Make a Difference?</h2>
            <p class="lead mb-4">Join our community of young leaders and create positive change.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="register.php" class="btn btn-light btn-lg px-4">Become a Member</a>
                <a href="contact.php" class="btn btn-outline-light btn-lg px-4">Contact Us</a>
            </div>
        </div>
    </section>
</main>

<style>
    /* Hero Section */
    .about-hero {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                    url('https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp') center/cover;
        color: white;
        padding: 100px 0;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }

    /* Section Styling */
    .section-header {
        position: relative;
        margin-bottom: 2rem;
    }

    .section-title {
        font-weight: 700;
        position: relative;
        display: inline-block;
        margin-bottom: 1rem;
    }

    .section-title:after {
        content: "";
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(to right, #3498db, #2c3e50);
    }

    .section-divider {
        width: 80px;
        height: 3px;
        background: linear-gradient(to right, #3498db, #2c3e50);
        margin: 1rem 0;
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
    }

    /* Core Cards */
    .core-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
    }

    .core-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }

    /* Achievement Cards */
    .achievement-card {
        transition: all 0.3s ease;
        border-radius: 8px;
    }

    .achievement-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }

    /* Value Cards */
    .value-card {
        transition: all 0.3s ease;
        border-radius: 8px;
    }

    .value-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }

    /* Team Cards */
    .team-card {
        transition: all 0.3s ease;
        border-radius: 10px;
    }

    .team-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }

    /* Icon Wrapper */
    .icon-wrapper {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    /* Avatar */
    .avatar-wrapper {
        width: 120px;
        height: 120px;
    }

    .avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Social Links */
    .social-links a {
        transition: all 0.3s ease;
    }

    .social-links a:hover {
        color: #0d6efd !important;
        transform: translateY(-2px);
    }

    /* Background Soft Colors */
    .bg-primary-soft {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    
    .bg-success-soft {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }
    
    .bg-info-soft {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }
    
    .bg-warning-soft {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .about-hero {
            padding: 80px 0;
        }
        
        .icon-wrapper {
            width: 56px;
            height: 56px;
        }
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 60px 0;
        }
        
        .section-title:after {
            width: 40px;
        }
        
        .section-divider {
            width: 60px;
        }
    }

    @media (max-width: 576px) {
        .about-hero {
            padding: 50px 0;
        }
        
        .icon-wrapper {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem !important;
            float: none !important;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>
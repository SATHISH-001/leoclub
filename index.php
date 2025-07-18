<?php 
$pageTitle = "Home";
require_once './includes/header.php'; 
require_once './includes/functions.php';
require_once './includes/db.php';

// Get counts from database
$pdo = getPDO();
$bearerCount = $pdo->query("SELECT COUNT(*) FROM office_bearers")->fetchColumn();
$coordinatorCount = $pdo->query("SELECT COUNT(*) FROM office_bearers WHERE position LIKE '%Coordinator%'")->fetchColumn();

// Get homepage settings
$settings = $pdo->query("SELECT setting_key, setting_value FROM homepage_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$logoPath = $settings['logo_path'] ?? 'https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp';
$heroTitle = $settings['hero_title'] ?? 'WELCOME TO THE LEO CLUB WEBSITE OF ACGCET';
$welcomeText = $settings['welcome_text'] ?? '';
$counter1Label = $settings['counter1_label'] ?? 'Office Bearers';
$counter2Label = $settings['counter2_label'] ?? 'Coordinators';
$marqueeSpeed = $settings['marquee_speed'] ?? 30;
$showNews = isset($settings['marquee_show_news']) ? (bool)$settings['marquee_show_news'] : true;
$showEvents = isset($settings['marquee_show_events']) ? (bool)$settings['marquee_show_events'] : true;

// Get most recent event with image for hero background
$heroBackgroundImage = $pdo->query("SELECT image_path FROM events WHERE image_path IS NOT NULL AND image_path != '' ORDER BY event_date DESC LIMIT 1")->fetchColumn();
$defaultBackground = 'https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leobg.webp';

// Get latest news and events for ticker
$tickerItems = [];
if ($showNews) {
    $newsItems = $pdo->query("SELECT id, title FROM news ORDER BY publish_date DESC LIMIT 5")->fetchAll();
    foreach ($newsItems as $news) {
        $tickerItems[] = [
            'type' => 'news',
            'id' => $news['id'],
            'title' => $news['title'],
            'date' => null
        ];
    }
}

if ($showEvents) {
    $eventItems = $pdo->query("SELECT id, title, event_date FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5")->fetchAll();
    foreach ($eventItems as $event) {
        $tickerItems[] = [
            'type' => 'events',
            'id' => $event['id'],
            'title' => $event['title'],
            'date' => $event['event_date']
        ];
    }
}

// Shuffle ticker items for variety
shuffle($tickerItems);

// Get latest news for news section
$latestNews = $showNews ? $pdo->query("SELECT id, title, publish_date, summary FROM news ORDER BY publish_date DESC LIMIT 3")->fetchAll() : [];

// Get upcoming events for events section
$upcomingEvents = $showEvents ? $pdo->query("SELECT id, title, event_date, location FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3")->fetchAll() : [];
?>

<!-- Announcement Bar -->
<div class="announcement-bar bg-dark text-white py-2">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-2 announcement-label">
                <span class="badge bg-warning text-dark"><i class="fas fa-bullhorn me-1"></i> UPDATES</span>
            </div>
            <div class="col-md-10">
                <div class="announcement-content">
                    <?php if (!empty($tickerItems)): ?>
                        <div class="marquee" data-speed="<?= (int)$marqueeSpeed ?>">
                            <?php foreach ($tickerItems as $item): ?>
                                <span class="announcement-item">
                                    <a href="<?= $item['type'] ?>.php?id=<?= $item['id'] ?>" class="text-white text-decoration-none">
                                        <span class="announcement-badge me-2">
                                            <?= $item['type'] == 'news' ? 'NEWS' : 'EVENT' ?>
                                        </span>
                                        <?= htmlspecialchars($item['title']) ?>
                                        <?php if ($item['type'] == 'events' && $item['date']): ?>
                                            <small class="ms-2">(<?= date('M j', strtotime($item['date'])) ?>)</small>
                                        <?php endif; ?>
                                    </a>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center">No recent announcements</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hero Section -->
<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-image-overlay"></div>
    <div class="hero-background" style="background-image: url('<?= $heroBackgroundImage ? htmlspecialchars($heroBackgroundImage) : './assets/images/college.jpg' ?>')"></div>

    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <img src="<?= htmlspecialchars($logoPath) ?>" alt="Leo Club Logo" class="hero-logo mb-4">
                <h1 class="hero-title mb-4"><?= htmlspecialchars($heroTitle) ?></h1>
                <?php if (!empty($welcomeText)): ?>
                    <p class="hero-subtitle lead"><?= htmlspecialchars($welcomeText) ?></p>
                <?php endif; ?>
                <div class="hero-cta mt-5">
                    <a href="about.php" class="btn btn-primary btn-lg me-3">Learn More</a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<!-- <section class="stats-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4 mb-4">
                <div class="stat-card" ondblclick="window.location.href='office-bearers.php'">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-count="<?= $bearerCount ?>">0</div>
                        <div class="stat-label"><?= htmlspecialchars($counter1Label) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-lg-4 mb-4">
                <div class="stat-card" ondblclick="window.location.href='office-bearers.php'">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-count="<?= $coordinatorCount ?>">0</div>
                        <div class="stat-label"><?= htmlspecialchars($counter2Label) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<!-- Latest News Section -->
<?php if ($showNews && !empty($latestNews)): ?>
<section class="news-section py-5 bg-light">
    <div class="container">
        <div class="section-header mb-5">
            <h2 class="section-title">Latest News</h2>
            <p class="section-subtitle">Stay updated with our club activities</p>
        </div>
        <div class="row">
            <?php foreach ($latestNews as $news): ?>
            <div class="col-lg-4 mb-4">
                <div class="news-card">
                    <div class="news-date">
                        <span class="news-day"><?= date('d', strtotime($news['publish_date'])) ?></span>
                        <span class="news-month"><?= date('M', strtotime($news['publish_date'])) ?></span>
                    </div>
                    <div class="news-content">
                        <h3 class="news-title"><?= htmlspecialchars($news['title']) ?></h3>
                        <p class="news-excerpt"><?= htmlspecialchars($news['summary']) ?></p>
                        <a href="news-detail.php?id=<?= $news['id'] ?>" class="news-link">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="news.php" class="btn btn-outline-primary">View All News</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Upcoming Events Section -->
<?php if ($showEvents && !empty($upcomingEvents)): ?>
<section class="events-section py-5">
    <div class="container">
        <div class="section-header mb-5">
            <h2 class="section-title">Upcoming Events</h2>
            <p class="section-subtitle">Join us in our upcoming activities</p>
        </div>
        <div class="row">
            <?php foreach ($upcomingEvents as $event): ?>
            <div class="col-lg-4 mb-4">
                <div class="event-card">
                    <div class="event-date">
                        <span class="event-day"><?= date('d', strtotime($event['event_date'])) ?></span>
                        <span class="event-month"><?= date('M', strtotime($event['event_date'])) ?></span>
                    </div>
                    <div class="event-content">
                        <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                        <div class="event-meta">
                            <span class="event-location"><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($event['location']) ?></span>
                        </div>
                        <a href="events-detail.php?id=<?= $event['id'] ?>" class="event-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="events.php" class="btn btn-primary">View All Events</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action Section -->
<section class="cta-section py-5 bg-dark text-white">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Join Our Club?</h2>
        <p class="lead mb-5">Become part of our community and make a difference</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="register.php" class="btn btn-light btn-lg px-4">Register Now</a>
            <a href="about.php" class="btn btn-outline-light btn-lg px-4">Learn More</a>
        </div>
    </div>
</section>

<style>
    /* Announcement Bar Styles */
    .announcement-bar {
        position: relative;
        z-index: 100;
        border-bottom: 1px solid rgba(15, 15, 15, 0.1);
    }
    
    .announcement-label {
        text-align: center;
    }
    
    .announcement-content {
        overflow: hidden;
        white-space: nowrap;
    }
    
    .marquee {
        display: inline-block;
        white-space: nowrap;
        animation: marquee-scroll linear infinite;
        padding-left: 100%;
    }
    
    .marquee:hover {
        animation-play-state: paused;
    }
    
    .announcement-item {
        display: inline-block;
        margin-right: 2rem;
        position: relative;
    }
    
    .announcement-item:after {
        content: "•";
        position: absolute;
        right: -1.5rem;
        color: rgba(255,255,255,0.3);
    }
    
    .announcement-badge {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
        background: rgba(17, 14, 14, 0.2);
    }
    
    .announcement-item a:hover .announcement-badge {
        background: var(--bs-warning);
        color: var(--bs-dark);
    }
    
    @keyframes marquee-scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }
    
    /* Hero Section Styles */
    /* Hero Section Styles */
.hero-section {
    position: relative;
    height: 100vh;
    min-height: 700px;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                url('./assets/images/college.jpg') no-repeat center center;
    background-size: cover;
    color: white;
    display: flex;
    align-items: center;
}
    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        z-index: -2;
        transition: background-image 0.5s ease;
    }
    
    .hero-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 100%);
        z-index: -1;
    }
    
    .hero-logo {
        max-width: 200px;
        height: auto;
        filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));
    }
    
    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 1.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .hero-subtitle {
        font-size: 1.5rem;
        max-width: 700px;
        margin: 0 auto;
        opacity: 0.9;
    }
    
    /* Stats Section Styles */
    .stats-section {
        background: white;
        position: relative;
        margin-top: -50px;
        z-index: 10;
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 2rem;
        display: flex;
        align-items: center;
        height: 100%;
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .stat-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3498db, #2c3e50);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-right: 1.5rem;
        flex-shrink: 0;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 1rem;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 0.5rem;
    }
    
    /* News & Events Card Styles */
    .section-header {
        text-align: center;
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        display: inline-block;
    }
    
    .section-title:after {
        content: "";
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: linear-gradient(to right, #3498db, #2c3e50);
    }
    
    .section-subtitle {
        color:rgb(21, 22, 22);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .news-card, .event-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        height: 100%;
        display: flex;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .news-card:hover, .event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .news-date, .event-date {
        width: 80px;
        background: linear-gradient(135deg, #3498db, #2c3e50);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .news-day, .event-day {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .news-month, .event-month {
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 0.25rem;
    }
    
    .news-content, .event-content {
        padding: 1.5rem;
        flex-grow: 1;
    }
    
    .news-title, .event-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #2c3e50;
    }
    
    .news-excerpt {
        color: #7f8c8d;
        margin-bottom: 1.5rem;
    }
    
    .event-meta {
        margin-bottom: 1.5rem;
    }
    
    .event-location {
        display: block;
        color: #7f8c8d;
        font-size: 0.9rem;
    }
    
    .news-link, .event-link {
        color: #3498db;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .news-link:hover, .event-link:hover {
        color: #2c3e50;
    }
    
    /* CTA Section Styles */
    .cta-section {
        background: linear-gradient(135deg, #2c3e50, #3498db);
        position: relative;
        overflow: hidden;
    }
    
    .cta-section:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leobg.webp') no-repeat center center;
        background-size: cover;
        opacity: 0.1;
        z-index: 0;
    }
    
    .cta-section .container {
        position: relative;
        z-index: 1;
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            font-size: 1.75rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
    }
    
    @media (max-width: 768px) {
        .announcement-label {
            display: flex;
        }
        
        .hero-section {
            min-height: 600px;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
        }
        
        .hero-logo {
            max-width: 150px;
        }
        
        .stat-card {
            flex-direction: column;
            text-align: center;
            padding: 1.5rem;
        }
        
        .stat-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }
        
        .section-title {
            font-size: 2rem;
        }
    }
    
    @media (max-width: 576px) {
        .hero-title {
            font-size: 1.75rem;
        }
        
        .hero-cta .btn {
            display: block;
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .hero-cta .btn:last-child {
            margin-bottom: 0;
        }
        
        .news-card, .event-card {
            flex-direction: column;
        }
        
        .news-date, .event-date {
            width: 100%;
            flex-direction: row;
            padding: 1rem;
        }
        
        .news-day, .event-day {
            margin-right: 1rem;
        }
        
        .news-month, .event-month {
            margin-top: 0;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set marquee speed
    const marquee = document.querySelector('.marquee');
    if (marquee) {
        const speed = marquee.getAttribute('data-speed');
        if (speed) {
            marquee.style.animationDuration = speed + 's';
        }
    }

    // Animate stats counter
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const increment = target / speed;
        
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(updateCounter, 1);
        } else {
            counter.innerText = target;
        }
        
        function updateCounter() {
            const count = +counter.innerText;
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCounter, 1);
            } else {
                counter.innerText = target;
            }
        }
    });
});
</script>

<?php
require_once './includes/footer.php';
?>
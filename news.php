<?php
require_once './includes/header.php';
require_once './includes/functions.php';
require_once './includes/db.php';

$pageTitle = "News";

// Get news items from database
$newsItems = [];
try {
    $pdo = getPDO();
    
    // Get featured news (most recent)
    $featuredStmt = $pdo->query("SELECT * FROM news ORDER BY publish_date DESC LIMIT 1");
    $featuredNews = $featuredStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get other news items
    $stmt = $pdo->query("SELECT * FROM news ORDER BY publish_date DESC LIMIT 1, 100");
    $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error in news.php: " . $e->getMessage());
}

// Filter valid news items
$validNews = array_filter($newsItems, function($item) {
    return !empty($item['id']) && !empty($item['title']);
});
?>

<section class="news-hero py-5 bg-light">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Club News & Updates</h1>
        <p class="lead text-light mx-auto" style="max-width: 700px;">
            Stay informed about our latest activities, achievements, and announcements
        </p>
    </div>
</section>

<!-- Featured News -->
<?php if (!empty($featuredNews)): ?>
<section class="featured-news py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Featured Story</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        
        <div class="card featured-news-card border-0 shadow-lg overflow-hidden">
            <div class="row g-0">
                <div class="col-lg-6">
                    <?php if (!empty($featuredNews['image_path'])): ?>
                    <div class="featured-news-img">
                        <img src="/assets/uploads/news/<?= htmlspecialchars($featuredNews['image_path']) ?>" 
                             class="img-fluid h-100 object-cover" 
                             alt="<?= htmlspecialchars($featuredNews['title']) ?>"
                             loading="eager">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <div class="card-body p-4 p-md-5">
                        <div class="featured-badge mb-3">
                            <span class="badge bg-primary">Featured</span>
                            <span class="text-muted ms-2">
                                <i class="fas fa-calendar-alt me-1"></i> 
                                <?= date('F j, Y', strtotime($featuredNews['publish_date'])) ?>
                            </span>
                        </div>
                        <h2 class="card-title mb-3"><?= htmlspecialchars($featuredNews['title']) ?></h2>
                        <p class="card-text mb-4"><?= substr(htmlspecialchars($featuredNews['content']), 0, 200) ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="news-detail.php?id=<?= $featuredNews['id'] ?>" class="btn btn-primary">
                                Read Full Story
                            </a>
                            <small class="text-muted">
                                <?= !empty($featuredNews['author']) ? 'By ' . htmlspecialchars($featuredNews['author']) : '' ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- News Grid -->
<section class="news-grid py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Latest Updates</h2>
            <div class="section-divider mx-auto"></div>
            <p class="section-subtitle text-muted">Recent news and announcements from our club</p>
        </div>
        
        <?php if (empty($validNews)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-newspaper me-2"></i> No news articles available at this time.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($validNews as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <?php if (!empty($item['image_path'])): ?>
                        <div class="news-card-img">
                            <img src="/assets/uploads/news/<?= htmlspecialchars($item['image_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($item['title']) ?>"
                                 loading="lazy">
                            <div class="news-date-overlay">
                                <span class="news-day"><?= date('d', strtotime($item['publish_date'])) ?></span>
                                <span class="news-month"><?= date('M', strtotime($item['publish_date'])) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-primary small">
                                    <i class="fas fa-tag me-1"></i> 
                                    <?= !empty($item['category']) ? htmlspecialchars($item['category']) : 'General' ?>
                                </span>
                                <span class="text-muted small">
                                    <i class="fas fa-clock me-1"></i> 
                                    <?= date('h:i A', strtotime($item['publish_date'])) ?>
                                </span>
                            </div>
                            <h3 class="h5 card-title"><?= htmlspecialchars($item['title']) ?></h3>
                            <p class="card-text text-muted"><?= substr(htmlspecialchars($item['content']), 0, 120) ?>...</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="news-detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary stretched-link">
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination would go here -->
            <div class="d-flex justify-content-center mt-5">
                <nav aria-label="News pagination">
                    <ul class="pagination">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</section>


<style>
/* Hero Section */
.news-hero {
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                url('https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp') center/cover;

    color: white;
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
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
}

.section-divider {
    width: 80px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
    margin: 1rem auto;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #6c757d;
}

/* Featured News */
.featured-news-card {
    border-radius: 12px;
    overflow: hidden;
}

.featured-news-img {
    height: 100%;
    min-height: 400px;
    background-color: #f8f9fa;
}

.featured-news-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.featured-news-card:hover .featured-news-img img {
    transform: scale(1.03);
}

.featured-badge {
    display: flex;
    align-items: center;
}

/* News Cards */
.news-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.news-card-img {
    position: relative;
    height: 200px;
    overflow: hidden;
    background-color: #f8f9fa;
}

.news-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.news-card:hover .news-card-img img {
    transform: scale(1.05);
}

.news-date-overlay {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(0,0,0,0.7);
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 2;
}

.news-day {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.news-month {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Newsletter */
.newsletter {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    position: relative;
    overflow: hidden;
}

.newsletter:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/newsletter-bg.webp') center/cover;
    opacity: 0.1;
    z-index: 0;
}

.newsletter .container {
    position: relative;
    z-index: 1;
}

.newsletter-form .form-control {
    height: 50px;
    border: none;
    border-radius: 8px 0 0 8px !important;
}

.newsletter-form .btn {
    height: 50px;
    border-radius: 0 8px 8px 0;
    font-weight: 600;
}

/* Pagination */
.pagination .page-item .page-link {
    border-radius: 8px;
    margin: 0 5px;
    border: none;
    color: #2c3e50;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #3498db, #2c3e50);
    border: none;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .featured-news-img {
        min-height: 300px;
    }
    
    .featured-news-card .card-body {
        padding: 2rem;
    }
}

@media (max-width: 768px) {
    .news-hero {
        padding: 80px 0;
    }
    
    .featured-news-img {
        min-height: 250px;
    }
    
    .news-card-img {
        height: 180px;
    }
}

@media (max-width: 576px) {
    .news-hero {
        padding: 60px 0;
    }
    
    .featured-news-card .card-body {
        padding: 1.5rem;
    }
    
    .newsletter-form .input-group {
        flex-direction: column;
    }
    
    .newsletter-form .form-control {
        border-radius: 8px !important;
        margin-bottom: 10px;
    }
    
    .newsletter-form .btn {
        border-radius: 8px !important;
        width: 100%;
    }
}
</style>

<?php require_once './includes/footer.php'; ?>
<?php
require_once './includes/header.php';
require_once './includes/functions.php';
require_once './includes/db.php';

// Check if news ID is provided
if (!isset($_GET['id'])) {
    header("Location: news.php");
    exit();
}

$newsId = (int)$_GET['id'];
$newsItem = null;
$relatedNews = [];

try {
    $pdo = getPDO();
    
    // Get the specific news item
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$newsId]);
    $newsItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$newsItem) {
        header("Location: news.php");
        exit();
    }
    
    // Get related news (same category, excluding current)
    $stmt = $pdo->prepare("SELECT id, title, image_path, publish_date 
                          FROM news 
                          WHERE category = ? AND id != ? 
                          ORDER BY publish_date DESC 
                          LIMIT 3");
    $stmt->execute([$newsItem['category'], $newsId]);
    $relatedNews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error in news-detail.php: " . $e->getMessage());
}

$pageTitle = htmlspecialchars($newsItem['title'] ?? 'News Detail');
?>

<section class="news-detail-hero py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="news.php">News</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($newsItem['title'] ?? '') ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<article class="news-detail py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <header class="news-header mb-5">
                    <div class="news-meta mb-3">
                        <span class="badge bg-primary"><?= htmlspecialchars($newsItem['category'] ?? 'General') ?></span>
                        <span class="text-muted ms-3">
                            <i class="fas fa-calendar-alt me-1"></i> 
                            <?= date('F j, Y', strtotime($newsItem['publish_date'])) ?>
                        </span>
                        <?php if (!empty($newsItem['author'])): ?>
                        <span class="text-muted ms-3">
                            <i class="fas fa-user me-1"></i> 
                            <?= htmlspecialchars($newsItem['author']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="news-title mb-4"><?= htmlspecialchars($newsItem['title']) ?></h1>
                    
                    <?php if (!empty($newsItem['image_path'])): ?>
                    <div class="news-featured-img mb-4">
                        <img src="/assets/uploads/news/<?= htmlspecialchars($newsItem['image_path']) ?>" 
                             class="img-fluid rounded" 
                             alt="<?= htmlspecialchars($newsItem['title']) ?>">
                        <?php if (!empty($newsItem['image_caption'])): ?>
                        <figcaption class="figure-caption text-center mt-2">
                            <?= htmlspecialchars($newsItem['image_caption']) ?>
                        </figcaption>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </header>
                
                <div class="news-content mb-5 bg-white">
                    <?= nl2br(htmlspecialchars($newsItem['content'])) ?>
                </div>
                
                <footer class="news-footer border-top pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="news-tags">
                            <?php if (!empty($newsItem['tags'])): ?>
                                <i class="fas fa-tags me-2"></i>
                                <?php 
                                $tags = explode(',', $newsItem['tags']);
                                foreach ($tags as $tag): 
                                ?>
                                    <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="news-share">
                            <span class="me-2">Share:</span>
                            <!-- <a href="#" class="text-muted me-2"><i class="fab fa-facebook-f"></i></a> -->
                            <a href="https://instagram.com/" target="_blank" class="text-muted me-2"><i class="fab fa-instagram"></i></a>
                            <!-- <a href="#" class="text-muted me-2"><i class="fab fa-linkedin-in"></i></a> -->
                            <a href="#" class="text-muted"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</article>

<?php if (!empty($relatedNews)): ?>
<section class="related-news py-5 bg-light">
    <div class="container">
        <div class="section-header mb-5">
            <h2 class="section-title">Related News</h2>
            <div class="section-divider"></div>
            <p class="section-subtitle text-muted">More stories you might be interested in</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($relatedNews as $item): ?>
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
                                <?= htmlspecialchars($newsItem['category'] ?? 'General') ?>
                            </span>
                            <span class="text-muted small">
                                <i class="fas fa-clock me-1"></i> 
                                <?= date('h:i A', strtotime($item['publish_date'])) ?>
                            </span>
                        </div>
                        <h3 class="h5 card-title"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="card-text text-muted"><?= date('F j, Y', strtotime($item['publish_date'])) ?></p>
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
    </div>
</section>
<?php endif; ?>

<!-- Newsletter Subscription -->
<!-- <section class="newsletter py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4">Stay Updated</h2>
                <p class="lead mb-4">Subscribe to our newsletter to receive the latest news directly in your inbox</p>
                
                <form class="newsletter-form mx-auto" style="max-width: 500px;">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email address" required>
                        <button class="btn btn-light" type="submit">
                            Subscribe <i class="fas fa-paper-plane ms-2"></i>
                        </button>
                    </div>
                </form> -->
            <!-- </div>
        </div>
    </div>
</section>  -->

<style>
/* Hero Section */
.news-detail-hero {
    background-color: #f8f9fa;
    padding-top: 2rem;
    padding-bottom: 1rem;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.3s;
}

.breadcrumb-item a:hover {
    color: #3498db;
}

.breadcrumb-item.active {
    color: #2c3e50;
    font-weight: 500;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
}

/* News Detail */
.news-header {
    margin-bottom: 3rem;
}

.news-meta {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.news-title {
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.3;
}

.news-featured-img {
    margin-bottom: 2rem;
    text-align: center;
}

.news-featured-img img {
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    max-height: 500px;
    width: 100%;
    object-fit: cover;
}

.news-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #495057;
}

.news-content p {
    margin-bottom: 1.5rem;
}

.news-footer {
    margin-top: 3rem;
}

.news-share a {
    transition: color 0.3s;
    font-size: 1.1rem;
}

.news-share a:hover {
    color: #3498db !important;
}

/* Related News */
.related-news {
    background-color: #f8f9fa;
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

/* Responsive Adjustments */
@media (max-width: 768px) {
    .news-meta {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .news-meta > span {
        margin-bottom: 0.5rem;
        margin-left: 0 !important;
    }
    
    .news-title {
        font-size: 1.8rem;
    }
    
    .news-content {
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
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
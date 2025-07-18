<?php
require_once __DIR__ . '/includes/header.php';
require_once './includes/functions.php';
require_once './includes/db.php';
require_once './includes/config.php';

$pageTitle = "Office Bearers | LEO Club of ACGCET";

try {
    $pdo = getPDO();
    
    if ($pdo) {
        // Get office bearers
        $stmt = $pdo->query("SELECT * FROM office_bearers ORDER BY 
            FIELD(year_type, 'current', '2nd_year', '3rd_year', 'final_year', 'past'), 
            position_order ASC");
        if ($stmt && $stmt instanceof PDOStatement) {
            $bearers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Get LEO Fam photo
        $stmt = $pdo->query("SELECT * FROM leo_fam LIMIT 1");
        $leoFam = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error in office-bearers.php: " . $e->getMessage());
    $bearers = [];
    $leoFam = [];
}

// Group bearers by year_type
$groupedBearers = [
    'current' => [],
    '2nd_year' => [],
    '3rd_year' => [],
    'final_year' => [],
    'past' => []
];

foreach ($bearers as $bearer) {
    $groupedBearers[$bearer['year_type']][] = $bearer;
}

// Filter valid bearers for each section
$validCurrentBearers = array_filter($groupedBearers['current'], function($bearer) {
    return !empty($bearer['id']) && !empty($bearer['name']);
});

$valid2ndYearBearers = array_filter($groupedBearers['2nd_year'], function($bearer) {
    return !empty($bearer['id']) && !empty($bearer['name']);
});

$valid3rdYearBearers = array_filter($groupedBearers['3rd_year'], function($bearer) {
    return !empty($bearer['id']) && !empty($bearer['name']);
});

$validFinalYearBearers = array_filter($groupedBearers['final_year'], function($bearer) {
    return !empty($bearer['id']) && !empty($bearer['name']);
});

$validPastBearers = array_filter($groupedBearers['past'], function($bearer) {
    return !empty($bearer['id']) && !empty($bearer['name']);
});
?>

<style>
    /* Hero Section */
    .office-bearers-hero {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                    url('https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp') center/cover;
        color: white;
        padding: 100px 0;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        margin-bottom: 40px;
    }

    /* Section Header */
    .section-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .section-header h1 {
        font-weight: 700;
        position: relative;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }
    
    .section-header h1:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: linear-gradient(to right, #3498db, #2c3e50);
    }
    
    /* Section Titles */
    .section-title {
        text-align: center;
        margin: 40px 0 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
    }
    
    /* Bearer Cards */
    .bearer-card {
        position: relative;
        margin-bottom: 30px;
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        height: 100%;
        width: 100%;
    }
    
    .card-img-container {
        position: relative;
        width: 100%;
        padding-top: 100%; /* 1:1 Aspect Ratio */
        overflow: hidden;
    }
    
    .card-img-container img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .bearer-card:hover .card-img-container img {
        transform: scale(1.05);
    }
    
    .card-body {
        padding: 15px;
        text-align: center;
    }
    
    .card-title {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .position-text {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .no-image-placeholder {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .no-image-placeholder i {
        font-size: 4rem;
        color: #6c757d;
    }
    
    .instagram-link {
        margin-top: 10px;
        display: inline-block;
        color: #E1306C;
        font-weight: 500;
        text-decoration: none;
    }
    
    .instagram-link:hover {
        text-decoration: underline;
    }
    
    /* LEO Fam Section */
    .leo-fam-section {
        text-align: center;
        margin: 40px 0;
    }
    
    .leo-fam-photo {
        max-width: 100%;
        height: auto;
        max-height: 500px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }
    
    .leo-fam-photo:hover {
        transform: scale(1.02);
    }
    
    .leo-fam-instagram {
        font-size: 1.2rem;
        color: #E1306C;
    }
    
    /* Responsive Grid Adjustments */
    @media (min-width: 992px) {
        .bearer-col {
            flex: 0 0 25%;
            max-width: 25%;
        }
    }
    
    @media (min-width: 768px) and (max-width: 991px) {
        .bearer-col {
            flex: 0 0 33.333%;
            max-width: 33.333%;
        }
    }
    
    @media (max-width: 767px) {
        .bearer-col {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 8px;
        }
        
        .bearer-card {
            margin-bottom: 20px;
        }
        
        .card-img-container {
            padding-top: 100%;
        }
        
        .card-body {
            padding: 10px;
        }
        
        .card-title {
            font-size: 0.9rem;
        }
        
        .position-text {
            font-size: 0.8rem;
        }
        
        .no-image-placeholder i {
            font-size: 2.5rem;
        }
    }
    
    @media (max-width: 400px) {
        .bearer-col {
            padding: 0 5px;
        }
        
        .bearer-card {
            margin-bottom: 15px;
        }
        
        .card-body {
            padding: 8px;
        }
        
        .card-title {
            font-size: 0.8rem;
        }
        
        .position-text {
            font-size: 0.7rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="office-bearers-hero">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold">Our Leadership Team</h1>
                <p class="lead">Meet the dedicated students guiding our organization</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="office-bearers-section py-4">
    <div class="container">
        <!-- Current Office Bearers -->
        <div class="section-title">
            <h2>Current Office Bearers</h2>
        </div>
        
        <?php if (empty($validCurrentBearers)): ?>
            <div class="alert alert-info text-center">No current office bearers found.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($validCurrentBearers as $bearer): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 bearer-col">
                    <?= renderBearerCard($bearer) ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- 2nd Year Coordinators -->
        <div class="section-title">
            <h2>3rd Year Coordinators</h2>
        </div>
        
        <?php if (empty($valid2ndYearBearers)): ?>
            <div class="alert alert-info text-center">No 3rd year coordinators found.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($valid2ndYearBearers as $bearer): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 bearer-col">
                    <?= renderBearerCard($bearer) ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- 3rd Year Coordinators -->
        <div class="section-title">
            <h2>2nd Year Coordinators</h2>
        </div>
        
        <?php if (empty($valid3rdYearBearers)): ?>
            <div class="alert alert-info text-center">No 2nd year coordinators found.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($valid3rdYearBearers as $bearer): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 bearer-col">
                    <?= renderBearerCard($bearer) ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- LEO Fam Section -->
        <div class="section-title">
            <h2>LEO Fam</h2>
        </div>
        
        <div class="leo-fam-section">
            <?php if (!empty($leoFam['photo'])): ?>
                <img src="<?= LEO_FAM_UPLOAD_URL. htmlspecialchars($leoFam['photo']) ?>" 
                     alt="LEO Fam Group Photo" 
                     class="leo-fam-photo">
            <?php else: ?>
                <div class="alert alert-info">No group photo uploaded yet</div>
            <?php endif; ?>
            <div class="leo-fam-instagram">
                <i class="fab fa-instagram"></i> <?= !empty($leoFam['instagram']) ? '@'.htmlspecialchars($leoFam['instagram']) : 'leo_club_of_acgcet' ?>
            </div>
        </div>
    </div>
</section>

<?php 
// Helper function to render bearer card
function renderBearerCard($bearer) {
    ob_start();
    ?>
    <div class="bearer-card">
        <div class="card-img-container <?= empty($bearer['photo']) ? 'no-image-placeholder' : '' ?>">
            <?php if (!empty($bearer['photo'])): ?>
                <?php
                $imagePath = BEARERS_UPLOAD_URL . $bearer['photo'];
                $fullPath = BEARERS_UPLOAD_DIR . $bearer['photo'];
                ?>
                
                <?php if (file_exists($fullPath)): ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>" 
                         alt="<?= htmlspecialchars($bearer['name']) ?>"
                         loading="lazy">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            <?php else: ?>
                <i class="fas fa-user"></i>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($bearer['name']) ?></h5>
            <p class="position-text"><?= htmlspecialchars($bearer['position']) ?></p>
            <?php if (!empty($bearer['contact'])): ?>
            <a href="https://instagram.com/<?= htmlspecialchars($bearer['contact']) ?>" 
               target="_blank" 
               class="instagram-link">
                <i class="fab fa-instagram"></i> <?= htmlspecialchars($bearer['contact']) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

require_once __DIR__ . '/includes/footer.php'; 
?>
<?php
require_once 'functions.php';
require_once 'db.php';

// Start session and check admin auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Uncomment when ready
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: index.php");  
//     exit();
// }

$pageTitle = "Manage Homepage";
$pdo = getPDO();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $settingsToUpdate = [];
        
        // Handle logo upload/delete
        if (isset($_POST['logo_action'])) {
            if ($_POST['logo_action'] === 'upload' && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = './assets/uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Validate file size (max 2MB)
                if ($_FILES['logo']['size'] > 2097152) {
                    throw new Exception("Logo size must be less than 2MB");
                }
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $fileType = mime_content_type($_FILES['logo']['tmp_name']);
                
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Invalid file type. Only JPG, PNG, and WEBP are allowed.");
                }
                
                $fileName = 'logo-' . uniqid() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $targetPath = $uploadDir . $fileName;
                
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                    throw new Exception("Failed to upload logo.");
                }
                
                // Delete old logo if exists
                $oldLogo = $pdo->query("SELECT setting_value FROM homepage_settings WHERE setting_key = 'logo_path'")->fetchColumn();
                if ($oldLogo && file_exists($oldLogo) && strpos($oldLogo, 'leologo.webp') === false) {
                    unlink($oldLogo);
                }
                
                $settingsToUpdate['logo_path'] = $targetPath;
            } 
            elseif ($_POST['logo_action'] === 'delete') {
                $logoPath = $pdo->query("SELECT setting_value FROM homepage_settings WHERE setting_key = 'logo_path'")->fetchColumn();
                if ($logoPath && file_exists($logoPath)) {
                    unlink($logoPath);
                }
                $pdo->query("DELETE FROM homepage_settings WHERE setting_key = 'logo_path'");
                $_SESSION['success'] = "Logo deleted successfully!";
            }
        }
        
        // Collect all settings from the form
        if (isset($_POST['save_all_settings'])) {
            $settingsToUpdate = array_merge($settingsToUpdate, [
                'hero_title' => $_POST['hero_title'] ?? '',
                'welcome_text' => $_POST['welcome_text'] ?? '',
                'counter1_label' => $_POST['counter1_label'] ?? '',
                'counter2_label' => $_POST['counter2_label'] ?? '',
                'marquee_speed' => (int)($_POST['marquee_speed'] ?? 30),
                'marquee_show_news' => isset($_POST['show_news']) ? 1 : 0,
                'marquee_show_events' => isset($_POST['show_events']) ? 1 : 0
            ]);
        }
        
        // Update all settings in a single transaction
        if (!empty($settingsToUpdate)) {
            $pdo->beginTransaction();
            
            try {
                $stmt = $pdo->prepare("INSERT INTO homepage_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                
                foreach ($settingsToUpdate as $key => $value) {
                    $stmt->execute([$key, $value, $value]);
                }
                
                $pdo->commit();
                $_SESSION['success'] = "Settings updated successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: manage-index.php");
    exit();
}

// Get current settings
$settings = $pdo->query("SELECT setting_key, setting_value FROM homepage_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$logoPath = $settings['logo_path'] ?? 'https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp';
$heroTitle = $settings['hero_title'] ?? 'WELCOME TO THE LEO CLUB WEBSITE OF ACGCET';
$welcomeText = $settings['welcome_text'] ?? '';
$counter1Label = $settings['counter1_label'] ?? 'Office Bearers';
$counter2Label = $settings['counter2_label'] ?? 'Coordinators';
$marqueeSpeed = $settings['marquee_speed'] ?? 30;
$showNews = isset($settings['marquee_show_news']) ? (bool)$settings['marquee_show_news'] : true;
$showEvents = isset($settings['marquee_show_events']) ? (bool)$settings['marquee_show_events'] : true;

// Get current event background
$currentEventBg = $pdo->query("SELECT image_path FROM events WHERE image_path IS NOT NULL AND image_path != '' ORDER BY event_date DESC LIMIT 1")->fetchColumn();
$defaultBackground = 'https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leobg.webp';

require_once 'heading.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once 'admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Homepage Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="settingsForm">
                <div class="row">
                    <!-- Logo Management -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-image me-2"></i> Club Logo</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <img src="<?= htmlspecialchars($logoPath) ?>" alt="Current Logo" class="img-fluid rounded" style="max-height: 200px;">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="logo" class="form-label">Upload New Logo</label>
                                    <input class="form-control" type="file" id="logo" name="logo" accept="image/jpeg, image/png, image/webp">
                                    <div class="form-text">Max size: 2MB. Recommended: Square aspect ratio, transparent background</div>
                                    <input type="hidden" name="logo_action" id="logoAction" value="">
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary flex-grow-1" onclick="document.getElementById('logoAction').value='upload'; document.getElementById('settingsForm').submit();">
                                        <i class="fas fa-upload me-1"></i> Upload Logo
                                    </button>
                                    <?php if (isset($settings['logo_path'])): ?>
                                    <button type="button" class="btn btn-danger flex-grow-1" onclick="if(confirm('Are you sure you want to delete the current logo?')) { document.getElementById('logoAction').value='delete'; document.getElementById('settingsForm').submit(); }">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($settings['logo_path'])): ?>
                                <a href="<?= htmlspecialchars($logoPath) ?>" class="btn btn-outline-primary w-100 mt-3" download>
                                    <i class="fas fa-download me-1"></i> Download Current Logo
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hero Background Info -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-image me-2"></i> Hero Background</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle me-2"></i> Automatic Background</h5>
                                    <p>The hero section automatically displays the most recent event's image as background.</p>
                                    <p class="mb-0">To change the background, upload an image when creating or editing an event.</p>
                                </div>
                                
                                <div class="current-bg-preview rounded mb-3" style="
                                    height: 150px;
                                    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                                        url('<?= $currentEventBg ? htmlspecialchars($currentEventBg) : $defaultBackground ?>') center/cover;
                                "></div>
                                
                                <p class="small text-muted mb-1">Current background source:</p>
                                <p class="mb-0">
                                    <?php if ($currentEventBg): ?>
                                        <i class="fas fa-check-circle text-success me-1"></i> Latest event image
                                    <?php else: ?>
                                        <i class="fas fa-image me-1"></i> Default background
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hero Section Settings -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-heading me-2"></i> Hero Section</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="hero_title" class="form-label">Main Title</label>
                                    <input type="text" class="form-control" id="hero_title" name="hero_title" 
                                           value="<?= htmlspecialchars($heroTitle) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="welcome_text" class="form-label">Welcome Message</label>
                                    <textarea class="form-control" id="welcome_text" name="welcome_text" 
                                              rows="3"><?= htmlspecialchars($welcomeText) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Marquee Settings -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-scroll me-2"></i> News Ticker</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="marquee_speed" class="form-label">Ticker Speed</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="marquee_speed" name="marquee_speed" 
                                               value="<?= htmlspecialchars($marqueeSpeed) ?>" min="10" max="60" required>
                                        <span class="input-group-text">seconds</span>
                                    </div>
                                    <div class="form-text">Time for one complete scroll cycle</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="show_news" name="show_news" 
                                               <?= $showNews ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="show_news">Show News Items</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="show_events" name="show_events" 
                                               <?= $showEvents ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="show_events">Show Event Items</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Counter Labels -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Counter Section</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="counter1_label" class="form-label">First Counter Label</label>
                                    <input type="text" class="form-control" id="counter1_label" name="counter1_label" 
                                           value="<?= htmlspecialchars($counter1Label) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="counter2_label" class="form-label">Second Counter Label</label>
                                    <input type="text" class="form-control" id="counter2_label" name="counter2_label" 
                                           value="<?= htmlspecialchars($counter2Label) ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Save All Button -->
                <div class="text-center mb-4">
                    <button type="submit" name="save_all_settings" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-1"></i> Save All Settings
                    </button>
                </div>
                
                <!-- Preview Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-eye me-2"></i> Live Preview</h5>
                    </div>
                    <div class="card-body">
                        <div class="preview-container p-4 border rounded" style="background-color: #f8f9fa;">
                            <!-- Ticker Preview -->
                            <div class="marquee-preview mb-4 p-2 rounded" style="background: linear-gradient(135deg, #2c3e50, #3498db); color: white;">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning text-dark me-2">
                                        <i class="fas fa-bullhorn me-1"></i> UPDATES
                                    </span>
                                    <div class="ticker-content" style="animation-duration: <?= $marqueeSpeed ?>s">
                                        <?php if ($showNews): ?>
                                            <span class="ticker-item me-4">
                                                <i class="fas fa-newspaper text-warning me-1"></i> Latest Club News
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($showEvents): ?>
                                            <span class="ticker-item me-4">
                                                <i class="fas fa-calendar-alt text-warning me-1"></i> Upcoming Event
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!$showNews && !$showEvents): ?>
                                            <span class="ticker-item">No active ticker items</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hero Preview -->
                            <div class="hero-preview mb-4 p-5 rounded text-center" 
                                 style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                                        url('<?= $currentEventBg ? htmlspecialchars($currentEventBg) : $defaultBackground ?>') center/cover; 
                                 color: white;">
                                <h2 class="mb-4"><?= htmlspecialchars($heroTitle) ?></h2>
                                <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo" class="img-fluid mb-4" style="max-height: 150px;">
                                <?php if (!empty($welcomeText)): ?>
                                    <p class="lead"><?= htmlspecialchars($welcomeText) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Counters Preview -->
                            <div class="row justify-content-center">
                                <div class="col-md-4 mb-3">
                                    <div class="counter-preview p-4 rounded text-center" style="background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                        <div class="display-4 mb-2">0</div>
                                        <div class="text-muted"><?= htmlspecialchars($counter1Label) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="counter-preview p-4 rounded text-center" style="background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                        <div class="display-4 mb-2">0</div>
                                        <div class="text-muted"><?= htmlspecialchars($counter2Label) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<style>
.admin-container {
    background-color: #f5f7fa;
    min-height: 100vh;
}

.card {
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.card-header {
    border-bottom: none;
    padding: 1rem 1.25rem;
}

.ticker-content {
    display: inline-block;
    white-space: nowrap;
    padding-left: 100%;
    animation: ticker-scroll linear infinite;
}

@keyframes ticker-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}

.hero-preview {
    min-height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.counter-preview {
    transition: transform 0.3s ease;
}

.counter-preview:hover {
    transform: translateY(-5px);
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.current-bg-preview {
    background-size: cover;
    background-position: center;
}

@media (max-width: 768px) {
    .hero-preview {
        padding: 2rem 1rem !important;
        min-height: 200px;
    }
    
    .hero-preview h2 {
        font-size: 1.5rem;
    }
    
    .hero-preview img {
        max-height: 100px !important;
    }
}
</style>

<script>
// Make the preview ticker animate
document.addEventListener('DOMContentLoaded', function() {
    const tickerContent = document.querySelector('.ticker-content');
    if (tickerContent) {
        // Clone the content for seamless looping
        tickerContent.innerHTML += tickerContent.innerHTML;
    }
});
</script>

<?php require_once 'footer.php'; ?>
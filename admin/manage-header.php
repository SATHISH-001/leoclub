<?php
// manage-header.php
if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// Initialize database connection
$pdo = getPDO();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update all settings from the form
    $settingsToUpdate = [
        'events_enabled',
        'register_enabled',
        'office_bearers_enabled',
        'login_enabled'
    ];
    
    foreach ($settingsToUpdate as $setting) {
        if (isset($_POST[$setting])) {
            $value = $_POST[$setting] === '1' ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE site_settings SET value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $setting]);
        }
    }

    $_SESSION['success_message'] = "Settings updated successfully!";
    header("Location: manage-header.php");
    exit();
}

// Get current settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, value FROM site_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['value'];
    }
} catch (PDOException $e) {
    // Fallback to default settings
    $settings = [
        'events_enabled' => '1',
        'register_enabled' => '1',
        'office_bearers_enabled' => '1',
        'login_enabled' => '1'
    ];
    error_log("Database error: " . $e->getMessage());
}

$pageTitle = "Manage Header";
require_once 'heading.php';
?>

<div class="container mt-5">
    <h1 class="mb-4">Manage Header Settings</h1>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Menu Items Visibility</h5>
            </div>
            <div class="card-body">
                <?php 
                $menuItems = [
                    'events_enabled' => 'Events',
                    'register_enabled' => 'Event Register',
                    'office_bearers_enabled' => 'Office Bearers',
                    'login_enabled' => 'Login'
                ];
                
                foreach ($menuItems as $key => $label): ?>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="<?= $key ?>" value="1" <?= ($settings[$key] ?? '1') == '1' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= $key ?>"><?= $label ?></label>
                </div>
                <?php endforeach; ?>
                
                <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
            </div>
        </div>
    </form>
</div>

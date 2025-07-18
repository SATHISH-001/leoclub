<?php
// session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'leoclub_dbs');
define('DB_USER', 'root');
define('DB_PASS', '');

// Admin configuration
define('ADMIN_MIN_PASSWORD_LENGTH', 8);
define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'Admin@123');
// Define common upload paths
define('UPLOAD_BASE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/');
define('UPLOAD_BASE_URL', '/assets/uploads/');

// Specific directories

define('EVENTS_UPLOAD_DIR', UPLOAD_BASE_DIR . 'events/');
define('EVENTS_UPLOAD_URL', UPLOAD_BASE_URL . 'events/');
define('BEARERS_UPLOAD_DIR', UPLOAD_BASE_DIR . 'office-bearers/');
define('BEARERS_UPLOAD_URL', UPLOAD_BASE_URL . 'office-bearers/');
// News upload paths
define('NEWS_UPLOAD_DIR', UPLOAD_BASE_DIR . 'news/');
define('NEWS_UPLOAD_URL', UPLOAD_BASE_URL . 'news/');
// Create PDO connection
require_once __DIR__ . '/functions.php'; // Updated path

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=3307;dbname=" . DB_NAME . ";charset=utf8", 
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

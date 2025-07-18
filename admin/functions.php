<?php
require_once 'db.php';


function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
function requireAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
    
    // Force password change if required
    if (isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] === true) {
        if (basename($_SERVER['PHP_SELF']) !== 'change-password.php') {
            header("Location: change-password.php");
            exit();
        }
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function getMemberById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// In your getAllEvents function

function getAllEvents($limit = null) {
    try {
        $pdo = getPDO();
        $sql = "SELECT * FROM events ORDER BY event_date DESC";
        if ($limit) {
            $sql .= " LIMIT :limit";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->query($sql);
        }
        return $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        error_log("Error in getAllEvents: " . $e->getMessage());
        return [];
    }
}
// ... existing functions ...

/**
 * Handle event form submission
 */
function handleEventFormSubmission($pdo) {
    try {
        if (isset($_POST['save_event'])) {
            // Validate inputs
            $required = ['title', 'description', 'event_date', 'event_time', 'location'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required fields");
                }
            }
            
            // Sanitize inputs
            $id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
            $title = sanitizeInput($_POST['title']);
            $description = sanitizeInput($_POST['description']);
            $event_date = $_POST['event_date'];
            $event_time = $_POST['event_time'];
            $location = sanitizeInput($_POST['location']);
            $image_path = $_POST['existing_image'] ?? '';
            
            // Handle file upload
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../assets/uploads/';
                
                // Validate and process image
                $image_path = handleEventImageUpload($_FILES['event_image'], $upload_dir, $image_path);
            }
            
            // Save to database
            if ($id > 0) {
                // Update existing event
                $stmt = $pdo->prepare("UPDATE events SET 
                    title = ?, description = ?, event_date = ?, 
                    event_time = ?, location = ?, image_path = ? 
                    WHERE id = ?");
                $stmt->execute([$title, $description, $event_date, $event_time, $location, $image_path, $id]);
                $_SESSION['success'] = "Event updated successfully!";
            } else {
                // Create new event
                $stmt = $pdo->prepare("INSERT INTO events 
                    (title, description, event_date, event_time, location, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $event_date, $event_time, $location, $image_path]);
                $_SESSION['success'] = "Event added successfully!";
            }
            
            header("Location: manage-events.php");
            exit();
        }
        elseif (isset($_POST['delete_event'])) {
            $id = (int)$_POST['event_id'];
            
            // Get event data first
            $stmt = $pdo->prepare("SELECT image_path FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $event = $stmt->fetch();
            
            // Delete associated image
            if ($event && !empty($event['image_path'])) {
                $file_path = __DIR__ . '/../../assets/uploads/' . $event['image_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Delete event
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = "Event deleted successfully!";
            header("Location: manage-events.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: manage-events.php");
        exit();
    }
}

/**
 * Handle event image upload
 */
function handleEventImageUpload($file, $upload_dir, $existing_image = '') {
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Validate image file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception("Only JPG, PNG, and GIF images are allowed");
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception("Image size must be less than 2MB");
    }
    
    // Generate safe filename
    $file_name = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\.]/", "", basename($file['name']));
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Delete old image if exists
        if ($existing_image && file_exists($upload_dir . $existing_image)) {
            unlink($upload_dir . $existing_image);
        }
        return $file_name;
    }
    
    throw new Exception("Failed to upload image");
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

/**
 * Display status messages
 */
function displayStatusMessages() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show">'
            . $_SESSION['success']
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
            . '</div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show">'
            . $_SESSION['error']
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
            . '</div>';
        unset($_SESSION['error']);
    }
}


function getAllNews() {
    try {
        $pdo = getPDO();
        $stmt = $pdo->query("
            SELECT news.*, members.name as author_name 
            FROM news 
            JOIN members ON news.author_id = members.id 
            ORDER BY publish_date DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getAllNews: " . $e->getMessage());
        return [];
    }
}

function getNewsById($id) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT news.*, members.name as author_name 
            FROM news 
            JOIN members ON news.author_id = members.id 
            WHERE news.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getNewsById: " . $e->getMessage());
        return null;
    }
}

function saveNewsArticle($data, $imageFile = null) {
    try {
        $pdo = getPDO();
        
        // Handle image upload
        $imagePath = $data['existing_image'] ?? '';
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
                $imagePath = 'assets/uploads/' . $filename;
                
                // Delete old image if it exists
                if (!empty($data['existing_image'])) {
                    $oldImage = __DIR__ . '/../../' . $data['existing_image'];
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }
            }
        }
        
        if (empty($data['id'])) {
            // Insert new article
            $stmt = $pdo->prepare("
                INSERT INTO news (title, content, author_id, publish_date, image_path)
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $stmt->execute([
                $data['title'],
                $data['content'],
                $data['author_id'],
                $imagePath
            ]);
            return $pdo->lastInsertId();
        } else {
            // Update existing article
            $stmt = $pdo->prepare("
                UPDATE news SET
                    title = ?,
                    content = ?,
                    author_id = ?,
                    image_path = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['title'],
                $data['content'],
                $data['author_id'],
                $imagePath,
                $data['id']
            ]);
            return $data['id'];
        }
    } catch (Exception $e) {
        error_log("Error in saveNewsArticle: " . $e->getMessage());
        return false;
    }
}

function deleteNewsArticle($id) {
    try {
        $pdo = getPDO();
        
        // First get the article to delete associated image
        $article = getNewsById($id);
        if ($article && $article['image_path']) {
            $imagePath = __DIR__ . '/../../' . $article['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Then delete the article
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        error_log("Error in deleteNewsArticle: " . $e->getMessage());
        return false;
    }
}
// send message
function saveContactMessage($name, $email, $subject, $message) {
    try {
        // Get database connection
        $db = getPDO();
        
        $query = "INSERT INTO contact_messages (name, email, subject, message, created_at) 
                  VALUES (:name, :email, :subject, :message, NOW())";
                  
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        
        return $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Database error in saveContactMessage: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all contact messages from database
 * @return array Array of contact messages
 */
function getAllContactMessages() {
    try {
        $db = getPDO();
        $query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getAllContactMessages: " . $e->getMessage());
        return [];
    }
}
// Add this to functions.php
/**
 * Delete a contact message
 * @param int $id Message ID
 * @return bool True if successful
 */
function deleteContactMessage($id) {
    try {
        $db = getPDO();
        $query = "DELETE FROM contact_messages WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in deleteContactMessage: " . $e->getMessage());
        return false;
    }
}



// function isAdmin() {
//     return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
// }

// function sanitizeInput($data) {
//     return htmlspecialchars(trim($data));
// }

// includes/functions.php



// function sanitizeInput($input) {
//     return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
// }

// function isLoggedIn() {
//     return isset($_SESSION['user_id']);
// }

// Flash message helpers (optional)
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
function getLoggedInUser($pdo) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}
//office bearers
/**
 * Get all office bearers
 */
function getAllOfficeBearers($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM office_bearers ORDER BY position_order, position");
        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        error_log("Error getting office bearers: " . $e->getMessage());
        return [];
    }
}

/**
 * Handle adding a new office bearer
 */
function handleAddBearer($pdo) {
    $required = ['name', 'position', 'position_order'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Please fill in all required fields");
        }
    }
    
    $name = sanitizeInput($_POST['name']);
    $position = sanitizeInput($_POST['position']);
    $position_order = (int)$_POST['position_order'];
    $department = sanitizeInput($_POST['department'] ?? '');
    $contact = sanitizeInput($_POST['contact'] ?? '');
    $photo = '';
    
    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo = uploadBearerPhoto($_FILES['photo']);
    }
    
    $stmt = $pdo->prepare("INSERT INTO office_bearers 
        (name, position, position_order, department, contact, photo) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $position, $position_order, $department, $contact, $photo]);
    
    $_SESSION['success'] = "Office bearer added successfully!";
}

/**
 * Handle updating an office bearer
 */
function handleUpdateBearer($pdo) {
    $id = (int)$_POST['bearer_id'];
    if (!$id) throw new Exception("Invalid office bearer ID");
    
    $required = ['name', 'position', 'position_order'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Please fill in all required fields");
        }
    }
    
    $name = sanitizeInput($_POST['name']);
    $position = sanitizeInput($_POST['position']);
    $position_order = (int)$_POST['position_order'];
    $department = sanitizeInput($_POST['department'] ?? '');
    $contact = sanitizeInput($_POST['contact'] ?? '');
    $photo = $_POST['existing_photo'] ?? '';
    
    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Delete old photo if exists
        if ($photo && file_exists(__DIR__ . "/../../assets/uploads/office-bearers/$photo")) {
            unlink(__DIR__ . "/../../assets/uploads/office-bearers/$photo");
        }
        $photo = uploadBearerPhoto($_FILES['photo']);
    }
    
    $stmt = $pdo->prepare("UPDATE office_bearers SET 
        name = ?, position = ?, position_order = ?, 
        department = ?, contact = ?, photo = ?
        WHERE id = ?");
    $stmt->execute([$name, $position, $position_order, $department, $contact, $photo, $id]);
    
    $_SESSION['success'] = "Office bearer updated successfully!";
}

/**
 * Handle deleting an office bearer
 */
function handleDeleteBearer($pdo) {
    $id = (int)$_POST['bearer_id'];
    if (!$id) throw new Exception("Invalid office bearer ID");
    
    // Get bearer data first to delete photo
    $stmt = $pdo->prepare("SELECT photo FROM office_bearers WHERE id = ?");
    $stmt->execute([$id]);
    $bearer = $stmt->fetch();
    
    if ($bearer && $bearer['photo']) {
        $photoPath = __DIR__ . "/../../assets/uploads/office-bearers/" . $bearer['photo'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }
    
    // Delete the record
    $stmt = $pdo->prepare("DELETE FROM office_bearers WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = "Office bearer deleted successfully!";
}

/**
 * Upload office bearer photo
 */
function uploadBearerPhoto($file) {
    $uploadDir = __DIR__ . "/../../assets/uploads/office-bearers/";
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate image file
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("Only JPG and PNG images are allowed");
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception("Image size must be less than 2MB");
    }
    
    // Generate safe filename
    $extension = $mimeType === 'image/jpeg' ? '.jpg' : '.png';
    $filename = uniqid() . $extension;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $filename;
    }
    
    throw new Exception("Failed to upload photo");
}
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'];
}
function getContactInfo() {
    $pdo = getPDO(); // Get the database connection
    
    try {
        // Check if the table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'contact_info'")->rowCount() > 0;
        
        if (!$tableExists) {
            // Create the table if it doesn't exist
            $pdo->exec("CREATE TABLE IF NOT EXISTS `contact_info` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `address` text NOT NULL,
                `email` varchar(255) NOT NULL,
                `phone` text NOT NULL,
                `instagram_url` varchar(255) DEFAULT NULL,
                `facebook_url` varchar(255) DEFAULT NULL,
                `twitter_url` varchar(255) DEFAULT NULL,
                `linkedin_url` varchar(255) DEFAULT NULL,
                `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Insert default data
            $pdo->exec("INSERT INTO `contact_info` (`address`, `email`, `phone`, `instagram_url`) VALUES (
                'LEO Club<br>Alagappa Chettiar Government College of Engineering and Technology<br>Karaikudi, Tamilnadu - 630006',
                'leoclubacgcet@gmail.com',
                'President Aakash +91 9677574657 <br>Secretary Dheva dharshini +91 6369991886 <br>Joint-Secretary Lohidha +91 9944344175',
                'https://instagram.com/leoclubacgcet'
            )");
        }

        $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: [
            'address' => 'LEO Club<br>Alagappa Chettiar Government College of Engineering and Technology<br>Karaikudi, Tamilnadu - 630006',
            'email' => 'leoclubacgcet@gmail.com',
            'phone' => 'President Aakash +91 9677574657 <br>Secretary Dheva dharshini +91 6369991886 <br>Joint-Secretary Lohidha +91 9944344175',
            'instagram_url' => 'https://instagram.com/leoclubacgcet',
            'facebook_url' => '',
            'twitter_url' => '',
            'linkedin_url' => ''
        ];
    } catch (PDOException $e) {
        error_log("Error getting contact info: " . $e->getMessage());
        return [
            'address' => 'LEO Club<br>Alagappa Chettiar Government College of Engineering and Technology<br>Karaikudi, Tamilnadu - 630006',
            'email' => 'leoclubacgcet@gmail.com',
            'phone' => 'President Aakash +91 9677574657 <br>Secretary Dheva dharshini +91 6369991886 <br>Joint-Secretary Lohidha +91 9944344175',
            'instagram_url' => 'https://instagram.com/leoclubacgcet',
            'facebook_url' => '',
            'twitter_url' => '',
            'linkedin_url' => ''
        ];
    }
}
?>
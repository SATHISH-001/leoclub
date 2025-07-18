<?php
// Start session and check authentication
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

// Database connection
require_once 'db.php';
require_once 'functions.php';
require_once 'config.php';

// Get PDO connection
$pdo = getPDO();

// Initialize messages
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Prepare update query
        $query = "UPDATE footer_settings SET 
                  description = :description,
                  email = :email,
                  phone = :phone,
                  instagram = :instagram,
                  copyright_text = :copyright_text,
                  updated_at = NOW()
                  WHERE id = 1";
        
        $stmt = $pdo->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':phone', $_POST['phone']);
        $stmt->bindParam(':instagram', $_POST['instagram']);
        $stmt->bindParam(':copyright_text', $_POST['copyright_text']);
        
        // Execute query
        if ($stmt->execute()) {
            $success_message = "Footer settings updated successfully!";
        } else {
            $error_message = "Error updating footer settings.";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Get current footer settings
try {
    $query = "SELECT * FROM footer_settings WHERE id = 1";
    $stmt = $pdo->query($query);
    $footer = $stmt->fetch();
    
    // If no settings exist, create default values
    if (!$footer) {
        $footer = [
            'description' => '',
            'email' => '',
            'phone' => '',
            'instagram' => '',
            'copyright_text' => '© %Y% Your Organization. All Rights Reserved.'
        ];
    }
} catch (PDOException $e) {
    die("Error fetching footer settings: " . $e->getMessage());
}
 include 'heading.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Footer - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .form-section h2 {
            color: #2c3e50;
            border-bottom: 2px solid #f1c40f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #f1c40f;
            border-color: #e1b000;
        }
        .btn-primary:hover {
            background-color: #e1b000;
            border-color: #d4a600;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'admin-sidebar.php'; ?>
      <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
    <div class="admin-container">
        <h1 class="mb-4"><i class="fas fa-shoe-prints me-2"></i> Manage Footer Settings</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="manage-footer.php">
            <div class="form-section">
                <h2><i class="fas fa-info-circle me-2"></i> Club Information</h2>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Club Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($footer['description']); ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h2><i class="fas fa-link me-2"></i> Social Links</h2>
                
                <div class="mb-3">
                    <label for="instagram" class="form-label">Instagram URL</label>
                    <input type="url" class="form-control" id="instagram" name="instagram" value="<?php echo htmlspecialchars($footer['instagram']); ?>">
                </div>
            </div>
            
            <div class="form-section">
                <h2><i class="fas fa-address-book me-2"></i> Contact Information</h2>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($footer['email']); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($footer['phone']); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2><i class="fas fa-copyright me-2"></i> Copyright Text</h2>
                
                <div class="mb-3">
                    <label for="copyright_text" class="form-label">Copyright Text (use %Y% for current year)</label>
                    <input type="text" class="form-control" id="copyright_text" name="copyright_text" value="<?php echo htmlspecialchars($footer['copyright_text']); ?>" required>
                    <small class="text-muted">Example: &copy; %Y% LEO Club of ACGCET. All Rights Reserved. Designed by sathish</small>
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Save Changes</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
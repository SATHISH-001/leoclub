<?php
session_start();
require_once __DIR__ . '/../admin/heading.php'; 
require_once __DIR__ . '../../admin/profile.php';
// requireAdminAuth();

$error = '';
$success = '';
// $admin = getCurrentAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        // Verify current password if changing password
        if (!empty($new_password)) {
            $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$admin['id']]);
            $db_admin = $stmt->fetch();
            
            if (!password_verify($current_password, $db_admin['password_hash'])) {
                $error = "Current password is incorrect";
            } elseif ($new_password !== $confirm_password) {
                $error = "New passwords do not match";
            } elseif (strlen($new_password) < 8) {
                $error = "Password must be at least 8 characters";
            }
        }

        if (empty($error)) {
            // Prepare update query
            $update_data = ['name' => $name, 'email' => $email];
            $sql = "UPDATE admin_users SET name = :name, email = :email";
            
            // Add password update if changing
            if (!empty($new_password)) {
                $update_data['password_hash'] = password_hash($new_password, PASSWORD_DEFAULT);
                $sql .= ", password_hash = :password_hash";
            }
            
            $sql .= " WHERE id = :id";
            $update_data['id'] = $admin['id'];
            
            // Execute update
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_data);
            
            // Update session data
            $_SESSION['admin_name'] = $name;
            
            $success = "Profile updated successfully!";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get current admin data
// $admin = getCurrentAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | LEO CLUB OF ACGCET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       .profile-section {
    background: linear-gradient(135deg, #f0f4f8, #e8f0fe);
    min-height: calc(100vh - 150px);
    padding: 60px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    background: #ffffff;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    border-radius: 20px 20px 0 0 !important;
    background: linear-gradient(to right, #0062E6, #33AEFF);
    color: #fff;
    padding: 20px 25px;
    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.2);
}

.card-header h3 {
    font-weight: 600;
    font-size: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #333;
}

.form-control {
    border-radius: 12px;
    padding: 12px 16px;
    border: 1px solid #ced4da;
    transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.form-control:focus {
    border-color: #33AEFF;
    box-shadow: 0 0 0 0.2rem rgba(0, 98, 230, 0.25);
}

.btn {
    border-radius: 12px;
    padding: 12px;
    font-weight: 600;
    background: linear-gradient(to right, #007bff, #00c6ff);
    border: none;
    transition: background 0.3s ease;
}

.btn:hover {
    background: linear-gradient(to right, #0056b3, #009ec3);
}

.alert {
    border-radius: 10px;
    padding: 15px;
    font-weight: 500;
}

.password-toggle i {
    font-size: 1rem;
    transition: color 0.3s ease;
}

.password-toggle:hover i {
    color: #007bff;
}

hr {
    border-top: 2px dashed #ccc;
}

    </style>
</head>
<body>
    <?php require_once __DIR__ . ''; ?>
    
    <div class="container py-5">
        <div class="profile-card">
            <div class="profile-header">
                <h2><i class="fas fa-user-circle me-2"></i>Admin Profile</h2>
                <p class="mb-0">Manage your account settings</p>
            </div>
            
            <div class="profile-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-section">
                        <h4 class="mb-4"><i class="fas fa-user me-2"></i>Basic Information</h4>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($admin['username']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="mb-4"><i class="fas fa-lock me-2"></i>Password Change</h4>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="password-strength">
                                <div class="password-strength-bar" id="password-strength-bar"></div>
                            </div>
                            <small class="text-muted">Leave blank to keep current password (minimum 8 characters)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-save btn-lg">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('password-strength-bar');
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]/)) strength += 1;
            if (password.match(/[A-Z]/)) strength += 1;
            if (password.match(/[0-9]/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            const width = (strength / 5) * 100;
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = 
                width < 40 ? '#e74a3b' : 
                width < 70 ? '#f6c23e' : '#1cc88a';
        });
    </script>
</body>
</html>


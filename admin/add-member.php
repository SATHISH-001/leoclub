<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$pdo = getPDO();
$pageTitle = "Add New Member";
$departments = ['CSE', 'ECE', 'Mechanical', 'Civil', 'EEE', 'IT'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required = ['name', 'email', 'password', 'role', 'department'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        // Sanitize inputs
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $department = sanitizeInput($_POST['department']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = sanitizeInput($_POST['role']);
        $profile_pic = '';

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate phone if provided
        if (!empty($phone) && !preg_match("/^[0-9]{10}$/", $phone)) {
            throw new Exception("Phone number must be 10 digits");
        }

        // Validate department
        if (!in_array($department, $departments)) {
            throw new Exception("Invalid department selected");
        }

        // Handle file upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/uploads/profile/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Validate image
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $_FILES['profile_pic']['tmp_name']);

            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception("Only JPG, PNG and GIF images are allowed");
            }

            if ($_FILES['profile_pic']['size'] > $maxSize) {
                throw new Exception("Image size must be less than 2MB");
            }

            // Generate safe filename
            $extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . strtolower($extension);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
                $profile_pic = $filename;
            } else {
                throw new Exception("Failed to upload profile picture");
            }
        }

        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO members 
                              (name, email, phone, department, password, role, profile_pic, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->execute([$name, $email, $phone, $department, $password, $role, $profile_pic]);

        $_SESSION['success'] = "Member added successfully!";
        header("Location: manage-members.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: add-member.php");
        exit();
    }
}

require_once 'heading.php';
?>

<style>
    :root {
        --primary: #FFC107;
        --primary-dark: #FFA000;
        --secondary: #FFF8E1;
        --light: #ffffff;
        --dark: #343a40;
        --danger: #e74a3b;
        --border-radius: 0.375rem;
        --box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
    }
    
    .admin-container {
        background-color: var(--secondary);
        min-height: 100vh;
    }
    
    .btn-primary {
        background-color: var(--primary);
        border: none;
        color: var(--dark);
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
        color: var(--dark);
    }
    
    .btn-outline-secondary {
        border-color: var(--primary);
        color: var(--primary-dark);
    }
    
    .btn-outline-secondary:hover {
        background-color: var(--primary);
        color: var(--dark);
    }
    
    .card {
        border: none;
        box-shadow: var(--box-shadow);
        border-radius: var(--border-radius);
    }
    
    .border-bottom {
        border-bottom: 1px solid rgba(255, 193, 7, 0.3) !important;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
    }
    
    .img-thumbnail {
        border: 2px solid var(--primary);
    }
</style>

<div class="admin-container">
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'admin-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add New Member</h1>
                    <a href="manage-members.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Members
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback">Please provide a name</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">Please provide a valid email</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               pattern="[0-9]{10}" maxlength="10">
                                        <div class="invalid-feedback">Please provide a 10-digit phone number</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department" class="form-label">Department *</label>
                                        <select class="form-select" id="department" name="department" required>
                                            <option value="">Select Department</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select a department</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role *</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="Member">Member</option>
                                            <option value="President">President</option>
                                            <option value="Vice President">Vice President</option>
                                            <option value="Secretary">Secretary</option>
                                            <option value="join-Secretary">Join Secretary</option>
                                            <option value="Treasurer">Treasurer</option>
                                             <option value="Coordinator">Co-ordinator</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a role</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password *</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="invalid-feedback">Please provide a password</div>
                                        <div class="form-text">Minimum 8 characters with uppercase, lowercase, and number</div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="profile_pic" class="form-label">Profile Picture</label>
                                        <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*">
                                        <div class="form-text">Max size: 2MB. Allowed types: JPG, PNG, GIF</div>
                                        <div id="imagePreview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                                <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-1"></i> Add Member
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input preview
    document.getElementById('profile_pic').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                preview.innerHTML = `
                    <img src="${event.target.result}" class="img-thumbnail" style="max-height: 150px;">
                    <div class="mt-1 text-muted">${file.name} (${(file.size/1024).toFixed(1)} KB)</div>
                `;
            };
            
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    });
    
    // Form validation
    const forms = document.querySelector('.needs-validation');
    if (forms) {
        forms.addEventListener('submit', function(event) {
            if (!forms.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            forms.classList.add('was-validated');
        }, false);
    }
    
    // Phone number validation
    document.getElementById('phone').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>

<?php require_once 'footer.php'; ?>
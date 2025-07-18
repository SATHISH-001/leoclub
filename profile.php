<?php
// Start session at the VERY beginning with no whitespace before
session_start();

$pageTitle = "My Profile";
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

// Check login status before any output
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$pdo = getPDO();
$user_id = $_SESSION['user_id'];

// Initialize messages
$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);

$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

if (!$user_data) {
    $_SESSION['error_message'] = "User not found";
    header("Location: login.php");
    exit();
}

// Set default profile picture (using LEO logo) if none exists
if (empty($user_data['profile_pic'])) {
    $user_data['profile_pic'] = 'https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);

    try {
        // Check if email already exists (excluding current user)
        $emailCheck = $pdo->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
        $emailCheck->execute([$email, $user_id]);
        
        if ($emailCheck->rowCount() > 0) {
            $error = "This email is already registered by another user.";
        } else {
            $update_data = [
                'name' => $name,
                'email' => $email,
                'id' => $user_id
            ];

            $sql = "UPDATE members SET name = :name, email = :email WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_data);

            // Update session data with new values
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['success_message'] = "Profile updated successfully!";
            
            // Force refresh by redirecting to same page
            header("Location: profile.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header("Location: profile.php");
        exit();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="profile-section py-5" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="profile-box bg-white rounded-4 shadow-sm overflow-hidden">
                    <div class="profile-box-header bg-primary text-white py-4 px-5" style="background: linear-gradient(135deg, #f1c40f 0%, #e1b000 100%);">
                        <h3 class="mb-0 fw-bold"><i class="fas fa-user-circle me-2"></i>My Profile</h3>
                    </div>
                    
                    <div class="profile-box-body p-5">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show mb-4 rounded-3">
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mb-5">
                            <div class="profile-pic-container position-relative d-inline-block">
                                <img src="<?= htmlspecialchars($user_data['profile_pic']) ?>" 
                                     class="profile-picture" 
                                     width="180" 
                                     height="180" 
                                     alt="Profile Image"
                                     onerror="this.src='https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp'">
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <button type="button" class="btn btn-sm btn-warning position-absolute bottom-0 end-0 rounded-circle" data-bs-toggle="modal" data-bs-target="#changePictureModal" style="width: 40px; height: 40px;">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="mt-4">
                                <h2 class="mb-1 fw-bold"><?= htmlspecialchars($user_data['name']) ?></h2>
                                <small class="text-muted">Leo Member since <?= date('M Y', strtotime($user_data['created_at'])) ?></small>
                            </div>
                        </div>

                        <form method="POST" id="profileForm">
                            <div class="mb-4">
                                <label for="name" class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control py-3 px-3 rounded-2" name="name" 
                                       value="<?= htmlspecialchars($user_data['name']) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control py-3 px-3 rounded-2" name="email" 
                                       value="<?= htmlspecialchars($user_data['email']) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Role</label>
                                <input type="text" class="form-control py-3 px-3 rounded-2" 
                                       value="<?= htmlspecialchars(ucfirst($user_data['role'])) ?>" readonly>
                            </div>

                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary py-3 rounded-2 fw-bold" style="background: linear-gradient(135deg, #f1c40f 0%, #e1b000 100%); border: none;">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Change Picture Modal (only visible to admin) -->
<?php if ($_SESSION['role'] === 'admin'): ?>
<div class="modal fade" id="changePictureModal" tabindex="-1" aria-labelledby="changePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePictureModalLabel">Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="update_profile_pic.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    <div class="mb-3">
                        <label for="profile_pic" class="form-label">Select new profile picture</label>
                        <input class="form-control" type="file" id="profile_pic" name="profile_pic" accept="image/*" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="reset_pic" name="reset_pic">
                        <label class="form-check-label" for="reset_pic">
                            Reset to default LEO logo
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    .profile-box {
        border: 1px solid rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .profile-box:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }
    
    .profile-picture {
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        width: 180px;
        height: 180px;
    }
    
    .form-control {
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
    }
    
    .btn-primary {
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        opacity: 0.9;
    }
    
    .profile-box-header {
        background: linear-gradient(135deg, #f1c40f 0%, #e1b000 100%);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile picture error handling
    const profilePicture = document.querySelector('.profile-picture');
    profilePicture.onerror = function() {
        this.src = 'https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp';
    };
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
require_once 'functions.php';
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$pdo = getPDO();

// Define upload directories and URLs - matching profile.php
define('UPLOAD_BASE_DIR', 'D:/xampp/htdocs/leoclubacgcet/uploads/');
define('UPLOAD_BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/leoclubacgcet/uploads/');
define('PROFILE_PIC_DIR', UPLOAD_BASE_DIR . 'profile-pics/');
define('PROFILE_PIC_URL', UPLOAD_BASE_URL . 'profile-pics/');

// Create directory if it doesn't exist
if (!file_exists(PROFILE_PIC_DIR)) {
    mkdir(PROFILE_PIC_DIR, 0777, true);
}

$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$member_id) {
    header("Location: manage-members.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$member_id]);
$member = $stmt->fetch();

if (!$member) {
    die("Member not found");
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $new_password = $_POST['new_password'] ?? '';
    $profile_pic = $member['profile_pic'];

    // Image Upload
    if (!empty($_FILES['profile_image']['name'])) {
        $img_name = basename($_FILES['profile_image']['name']);
        $unique_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $img_name);
        $target_file = PROFILE_PIC_DIR . $unique_name;

        $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                // Delete old profile picture if it exists and isn't the default
                if ($member['profile_pic'] && !str_contains($member['profile_pic'], 'default-profile.jpg')) {
                    $oldImagePath = str_replace(PROFILE_PIC_URL, PROFILE_PIC_DIR, $member['profile_pic']);
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                $profile_pic = PROFILE_PIC_URL . $unique_name;
            } else {
                $error = "Image upload failed.";
            }
        } else {
            $error = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    }

    if (empty($error)) {
        $sql = "UPDATE members SET name = :name, email = :email, profile_pic = :profile_pic";
        $params = [
            'name' => $name,
            'email' => $email,
            'profile_pic' => $profile_pic,
            'id' => $member_id
        ];

        if (!empty($new_password)) {
            if (strlen($new_password) < 8) {
                $error = "Password must be at least 8 characters";
            } else {
                $sql .= ", password = :password";
                $params['password'] = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }

        $sql .= " WHERE id = :id";

        if (empty($error)) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success = "Member updated successfully!";
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch();
        }
    }
}

require_once __DIR__ . '/../admin/heading.php'; 
?>

<div class="container py-5">
    <h2>Edit Member</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($member['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Current Profile Image:</label><br>
            <?php if (!empty($member['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($member['profile_pic']) ?>" width="100" class="rounded">
            <?php else: ?>
                <em>No image</em>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="profile_image">Change Profile Image (optional)</label>
            <input type="file" name="profile_image" class="form-control" accept="image/*">
        </div>

        <hr>

        <h5>Change Password (optional)</h5>
        <div class="mb-3">
            <label for="new_password">New Password (leave blank to keep existing)</label>
            <input type="password" name="new_password" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update Member</button>
        <a href="manage-members.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php require_once __DIR__ . '/../admin/footer.php'; ?>
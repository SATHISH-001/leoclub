<?php
require_once 'config.php';
require_once 'functions.php';
session_start();

// Check if user is logged in and is a superadmin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'superadmin') {
    header("Location: index.php");
    exit();
}

$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_admin'])) {
        // Add new admin
        $name = sanitizeInput($_POST['name']);
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = sanitizeInput($_POST['role']);

        // Validation
        if (empty($name)) $errors['name'] = 'Name is required';
        if (empty($username)) $errors['username'] = 'Username is required';
        if (empty($password)) $errors['password'] = 'Password is required';
        if ($password !== $confirm_password) $errors['confirm_password'] = 'Passwords do not match';
        if (empty($role)) $errors['role'] = 'Role is required';

        if (empty($errors)) {
            try {
                // Check if username exists
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $errors['username'] = 'Username already exists';
                } else {
                    // Insert new admin
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admin_users (name, username, password_hash, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $username, $password_hash, $role]);
                    $success = 'Admin account created successfully!';
                }
            } catch (PDOException $e) {
                $errors['database'] = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $admin_id = (int)$_POST['admin_id'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if (empty($new_password)) $errors['new_password'] = 'New password is required';
        if ($new_password !== $confirm_new_password) $errors['confirm_new_password'] = 'Passwords do not match';

        if (empty($errors)) {
            try {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$password_hash, $admin_id]);
                $success = 'Password changed successfully!';
            } catch (PDOException $e) {
                $errors['database'] = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['edit_admin'])) {
        // Edit admin details
        $admin_id = (int)$_POST['admin_id'];
        $name = sanitizeInput($_POST['edit_name']);
        $username = sanitizeInput($_POST['edit_username']);
        $role = sanitizeInput($_POST['edit_role']);

        if (empty($name)) $errors['edit_name'] = 'Name is required';
        if (empty($username)) $errors['edit_username'] = 'Username is required';
        if (empty($role)) $errors['edit_role'] = 'Role is required';

        if (empty($errors)) {
            try {
                // Check if username exists (excluding current admin)
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
                $stmt->execute([$username, $admin_id]);
                if ($stmt->fetch()) {
                    $errors['edit_username'] = 'Username already exists';
                } else {
                    // Update admin details
                    $stmt = $pdo->prepare("UPDATE admin_users SET name = ?, username = ?, role = ? WHERE id = ?");
                    $stmt->execute([$name, $username, $role, $admin_id]);
                    $success = 'Admin details updated successfully!';
                }
            } catch (PDOException $e) {
                $errors['database'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get all admins
$admins = [];
try {
    $stmt = $pdo->query("SELECT id, name, username, role, last_login FROM admin_users ORDER BY name");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors['database'] = 'Error fetching admins: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admins | LEO CLUB</title>
    <?php include 'heading.php'; ?>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 0 3px;
        }
        .modal-header {
            background-color: #4e73df;
            color: white;
        }
    </style>
</head>
<body>
   
    <div class="container py-5">
        <div class="row">
                <?php include 'admin-sidebar.php'; ?>
            <div class="col-lg-12">
                <h1 class="mb-4"><i class="fas fa-users-cog me-2"></i>Manage Admin Accounts</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['database'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $errors['database'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Add New Admin Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Admin</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Username *</label>
                                    <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                                    <?php if (isset($errors['username'])): ?>
                                        <div class="invalid-feedback"><?= $errors['username'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Password *</label>
                                    <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" required>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Role *</label>
                                    <select name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" required>
                                        <option value="">Select Role</option>
                                        <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="superadmin" <?= ($_POST['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                                    </select>
                                    <?php if (isset($errors['role'])): ?>
                                        <div class="invalid-feedback"><?= $errors['role'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-12 mt-3">
                                    <button type="submit" name="add_admin" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Create Admin
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Admin List Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Admin Accounts</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td><?= $admin['id'] ?></td>
                                            <td><?= htmlspecialchars($admin['name']) ?></td>
                                            <td><?= htmlspecialchars($admin['username']) ?></td>
                                            <td>
                                                <span class="badge <?= $admin['role'] === 'superadmin' ? 'bg-danger' : 'bg-primary' ?>">
                                                    <?= ucfirst($admin['role']) ?>
                                                </span>
                                            </td>
                                            <td><?= $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never' ?></td>
                                            <td>
                                                <!-- Edit Button -->
                                                <button class="btn btn-sm btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editModal<?= $admin['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <!-- Change Password Button -->
                                                <button class="btn btn-sm btn-info btn-action" data-bs-toggle="modal" data-bs-target="#passwordModal<?= $admin['id'] ?>">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?= $admin['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $admin['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?= $admin['id'] ?>">Edit Admin</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Name *</label>
                                                                <input type="text" name="edit_name" class="form-control <?= isset($errors['edit_name']) ? 'is-invalid' : '' ?>" 
                                                                       value="<?= htmlspecialchars($admin['name']) ?>" required>
                                                                <?php if (isset($errors['edit_name'])): ?>
                                                                    <div class="invalid-feedback"><?= $errors['edit_name'] ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Username *</label>
                                                                <input type="text" name="edit_username" class="form-control <?= isset($errors['edit_username']) ? 'is-invalid' : '' ?>" 
                                                                       value="<?= htmlspecialchars($admin['username']) ?>" required>
                                                                <?php if (isset($errors['edit_username'])): ?>
                                                                    <div class="invalid-feedback"><?= $errors['edit_username'] ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Role *</label>
                                                                <select name="edit_role" class="form-select <?= isset($errors['edit_role']) ? 'is-invalid' : '' ?>" required>
                                                                    <option value="admin" <?= $admin['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                                    <option value="superadmin" <?= $admin['role'] === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                                                                </select>
                                                                <?php if (isset($errors['edit_role'])): ?>
                                                                    <div class="invalid-feedback"><?= $errors['edit_role'] ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="edit_admin" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Change Password Modal -->
                                        <div class="modal fade" id="passwordModal<?= $admin['id'] ?>" tabindex="-1" aria-labelledby="passwordModalLabel<?= $admin['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="passwordModalLabel<?= $admin['id'] ?>">Change Password</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">New Password *</label>
                                                                <input type="password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" required>
                                                                <?php if (isset($errors['new_password'])): ?>
                                                                    <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Confirm New Password *</label>
                                                                <input type="password" name="confirm_new_password" class="form-control <?= isset($errors['confirm_new_password']) ? 'is-invalid' : '' ?>" required>
                                                                <?php if (isset($errors['confirm_new_password'])): ?>
                                                                    <div class="invalid-feedback"><?= $errors['confirm_new_password'] ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
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
$pageTitle = "Manage Members";

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Get member data first to delete profile pic
        $stmt = $pdo->prepare("SELECT profile_pic FROM members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();

        if ($member && $member['profile_pic']) {
            $photoPath = __DIR__ . "/../../assets/uploads/profile/" . $member['profile_pic'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        // Delete the record
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = "Member deleted successfully!";
        header("Location: manage-members.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting member: " . $e->getMessage();
        header("Location: manage-members.php");
        exit();
    }
}

// Get all members
$stmt = $pdo->query("SELECT * FROM members ORDER BY name ASC");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'heading.php';
?>

<style>
    :root {
        --primary: #FFC107;
        --primary-dark: #FFA000;
        --secondary: #FFF8E1;
        --light: #ffffff;
        --dark: #343a40;
        --danger: #e74c3c;
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
    
    .btn-danger {
        background-color: var(--danger);
        border: none;
    }
    
    .card {
        border: none;
        box-shadow: var(--box-shadow);
        border-radius: var(--border-radius);
    }
    
    .table th {
        background-color: var(--primary);
        color: var(--dark);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(255, 193, 7, 0.1);
    }
    
    .border-bottom {
        border-bottom: 1px solid rgba(255, 193, 7, 0.3) !important;
    }
    
    .sidebar {
        background-color: var(--light);
    }
    
    .sidebar .nav-link {
        color: var(--dark);
    }
    
    .sidebar .nav-link:hover {
        color: var(--primary-dark);
    }
    
    .sidebar .nav-link.active {
        background-color: var(--primary);
        color: var(--dark);
    }
</style>

<div class="admin-container">  
    <div class="row">
        <?php include_once 'admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Members</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add-member.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Member
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Profile</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($members)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No members found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($member['profile_pic'])): ?>
                                            <img src="/assets/uploads/profile/<?= htmlspecialchars($member['profile_pic']) ?>" 
                                                 class="rounded-circle" 
                                                 style="width: 40px; height: 40px; object-fit: cover;" 
                                                 alt="<?= htmlspecialchars($member['name']) ?>">
                                            <?php else: ?>
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($member['name']) ?></td>
                                        <td><?= htmlspecialchars($member['email']) ?></td>
                                        <td><?= htmlspecialchars($member['phone'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($member['department'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($member['role']) ?></td>
                                        <td>
                                            <a href="edit-member.php?id=<?= $member['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="manage-members.php?delete=<?= $member['id'] ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this member?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
require_once 'footer.php';
?>
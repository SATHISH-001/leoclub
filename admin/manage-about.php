<?php
require_once 'heading.php';
require_once 'functions.php';
require_once 'db.php';
require_once 'config.php';

// Check admin permissions
// if (!hasPermission('admin')) {
//     header('Location: login.php');
//     exit;
// }

// Initialize database connection
$pdo = getPDO();

// Handle actions
$action = $_GET['action'] ?? 'main';
$section = $_GET['section'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($action === 'save-content') {
            // Handle content section save
            $title = sanitizeInput($_POST['title'] ?? '');
            $content = sanitizeInput($_POST['content'] ?? '');
            $section = sanitizeInput($_POST['section'] ?? '');
            
            // Validate input
            if (empty($title)) {
                throw new Exception("Title cannot be empty");
            }
            
            // Check if section exists
            $stmt = $pdo->prepare("SELECT id FROM about_content WHERE section = ?");
            $stmt->execute([$section]);
            
            if ($stmt->rowCount() > 0) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE about_content SET title = ?, content = ? WHERE section = ?");
                $stmt->execute([$title, $content, $section]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO about_content (section, title, content) VALUES (?, ?, ?)");
                $stmt->execute([$section, $title, $content]);
            }
            
            $_SESSION['success_message'] = "Content updated successfully!";
            header("Location: manage-about.php?action=content&section=$section");
            exit;
        }
        elseif ($action === 'save-achievement') {
            // Handle achievement save
            $id = (int)($_POST['id'] ?? 0);
            $title = sanitizeInput($_POST['title'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $icon = sanitizeInput($_POST['icon'] ?? '');
            $color = sanitizeInput($_POST['color'] ?? '');
            $display_order = (int)($_POST['display_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate input
            if (empty($title) || empty($description) || empty($icon) || empty($color)) {
                throw new Exception("All fields are required");
            }
            
            if ($id > 0) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE about_achievements SET title = ?, description = ?, icon = ?, color = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $description, $icon, $color, $display_order, $is_active, $id]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO about_achievements (title, description, icon, color, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $icon, $color, $display_order, $is_active]);
            }
            
            $_SESSION['success_message'] = "Achievement saved successfully!";
            header("Location: manage-about.php?action=achievements");
            exit;
        }
        elseif ($action === 'save-team') {
            // Handle team member save
            $id = (int)($_POST['id'] ?? 0);
            $name = sanitizeInput($_POST['name'] ?? '');
            $position = sanitizeInput($_POST['position'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $display_order = (int)($_POST['display_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate input
            if (empty($name) || empty($position)) {
                throw new Exception("Name and position are required");
            }
            
            // Handle file upload
            $image_path = $_POST['existing_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../assets/images/team/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                
                // Validate image
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['image']['tmp_name']);
                
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Only JPG, PNG, and GIF files are allowed");
                }
                
                // Set maximum file size (2MB)
                if ($_FILES['image']['size'] > 2097152) {
                    throw new Exception("File size must be less than 2MB");
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image_path = 'assets/images/team/' . $fileName;
                    
                    // Delete old image if exists
                    if (!empty($_POST['existing_image']) && file_exists('../../' . $_POST['existing_image'])) {
                        unlink('../../' . $_POST['existing_image']);
                    }
                } else {
                    throw new Exception("Error uploading image");
                }
            }
            
            if ($id > 0) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE about_team SET name = ?, position = ?, description = ?, image_path = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $position, $description, $image_path, $display_order, $is_active, $id]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO about_team (name, position, description, image_path, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $position, $description, $image_path, $display_order, $is_active]);
            }
            
            $_SESSION['success_message'] = "Team member saved successfully!";
            header("Location: manage-about.php?action=team");
            exit;
        }
        elseif ($action === 'delete-achievement') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception("Invalid achievement ID");
            }
            
            $stmt = $pdo->prepare("DELETE FROM about_achievements WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success_message'] = "Achievement deleted successfully!";
            header("Location: manage-about.php?action=achievements");
            exit;
        }
        elseif ($action === 'delete-team') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception("Invalid team member ID");
            }
            
            // Get image path first
            $stmt = $pdo->prepare("SELECT image_path FROM about_team WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            
            if ($row && !empty($row['image_path'])) {
                $imagePath = '../../' . $row['image_path'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Delete record
            $stmt = $pdo->prepare("DELETE FROM about_team WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success_message'] = "Team member deleted successfully!";
            header("Location: manage-about.php?action=team");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: manage-about.php?action=" . urlencode($action));
        exit;
    }
}

// Get data for display
$contentData = [];
$achievements = [];
$teamMembers = [];
$editAchievement = null;
$editTeamMember = null;

try {
    // Get content sections
    $stmt = $pdo->query("SELECT section, title, content FROM about_content");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $contentData[$row['section']] = $row;
    }
    
    // Get achievements
    $stmt = $pdo->query("SELECT * FROM about_achievements ORDER BY display_order");
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get team members
    $stmt = $pdo->query("SELECT * FROM about_team ORDER BY display_order");
    $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get specific achievement or team member for editing
    if (isset($_GET['edit_achievement'])) {
        $id = (int)$_GET['edit_achievement'];
        $stmt = $pdo->prepare("SELECT * FROM about_achievements WHERE id = ?");
        $stmt->execute([$id]);
        $editAchievement = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (isset($_GET['edit_team'])) {
        $id = (int)$_GET['edit_team'];
        $stmt = $pdo->prepare("SELECT * FROM about_team WHERE id = ?");
        $stmt->execute([$id]);
        $editTeamMember = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error loading data: " . $e->getMessage();
}

// Common icons and colors for achievements
$icons = [
    'fa-tint', 'fa-leaf', 'fa-hand-holding-heart', 'fa-broom', 
    'fa-users', 'fa-graduation-cap', 'fa-heart', 'fa-lightbulb',
    'fa-trophy', 'fa-medal', 'fa-star', 'fa-award'
];

$colors = [
    'text-primary', 'text-success', 'text-danger', 'text-warning',
    'text-info', 'text-secondary', 'text-dark'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Page | LEO Club Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .nav-link.active {
            font-weight: bold;
            color: #0d6efd !important;
            background-color: #e7f1ff;
            border-radius: 5px;
        }
        .preview-icon {
            font-size: 2rem;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .img-thumbnail {
            max-width: 100px;
            max-height: 100px;
        }
        .section-divider {
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #3498db, #2c3e50);
            margin: 1rem 0;
        }
        .badge-active {
            background-color: #198754;
        }
        .badge-inactive {
            background-color: #6c757d;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.5em;
        }
    </style>
</head>
<body>
    
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'admin-sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['success_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <?php if ($action === 'main'): ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">About Page Management</h1>
                    </div>
                    
                    <p>Welcome to the About Page management system. Use the sidebar to navigate to different sections.</p>
                    
                    <div class="row mt-4 g-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-wrapper bg-primary bg-opacity-10 p-3 rounded me-3">
                                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                                        </div>
                                        <h5 class="card-title mb-0">Content Sections</h5>
                                    </div>
                                    <p class="card-text">Manage mission, vision, and history sections of the about page.</p>
                                    <a href="manage-about.php?action=content&section=mission" class="btn btn-primary">Manage Content</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-wrapper bg-success bg-opacity-10 p-3 rounded me-3">
                                            <i class="fas fa-trophy fa-2x text-success"></i>
                                        </div>
                                        <h5 class="card-title mb-0">Achievements</h5>
                                    </div>
                                    <p class="card-text">Manage the achievements and milestones displayed on the about page.</p>
                                    <a href="manage-about.php?action=achievements" class="btn btn-primary">Manage Achievements</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-wrapper bg-info bg-opacity-10 p-3 rounded me-3">
                                            <i class="fas fa-users fa-2x text-info"></i>
                                        </div>
                                        <h5 class="card-title mb-0">Team Members</h5>
                                    </div>
                                    <p class="card-text">Manage the team members section with photos and positions.</p>
                                    <a href="manage-about.php?action=team" class="btn btn-primary">Manage Team</a>
                                </div>
                            </div>
                        </div>
                    </div>
                
                <?php elseif ($action === 'content'): ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Manage Content Sections</h1>
                    </div>
                    
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item">
                            <a class="nav-link <?= ($section === 'mission') ? 'active' : '' ?>" href="?action=content&section=mission">Mission</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($section === 'vision') ? 'active' : '' ?>" href="?action=content&section=vision">Vision</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($section === 'history') ? 'active' : '' ?>" href="?action=content&section=history">History</a>
                        </li>
                    </ul>
                    
                    <form method="post" action="manage-about.php?action=save-content">
                        <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($contentData[$section]['title'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="6" required><?= htmlspecialchars($contentData[$section]['content'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </form>
                
                <?php elseif ($action === 'achievements'): ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Manage Achievements</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAchievementModal">
                                <i class="fas fa-plus me-2"></i>Add New Achievement
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Title</th>
                                    <th width="100">Icon</th>
                                    <th width="120">Status</th>
                                    <th width="100">Order</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($achievements as $index => $achievement): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($achievement['title']) ?></td>
                                    <td><i class="fas <?= htmlspecialchars($achievement['icon']) ?> <?= htmlspecialchars($achievement['color']) ?> fa-lg"></i></td>
                                    <td>
                                        <span class="badge rounded-pill <?= $achievement['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                            <?= $achievement['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= $achievement['display_order'] ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="?action=achievements&edit_achievement=<?= $achievement['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="post" action="manage-about.php?action=delete-achievement" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $achievement['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this achievement?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($achievements)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No achievements found. Add your first achievement using the button above.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Add/Edit Achievement Modal -->
                    <div class="modal fade" id="addAchievementModal" tabindex="-1" aria-labelledby="addAchievementModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addAchievementModalLabel">
                                        <?= $editAchievement ? 'Edit Achievement' : 'Add New Achievement' ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post" action="manage-about.php?action=save-achievement">
                                    <input type="hidden" name="id" value="<?= $editAchievement['id'] ?? 0 ?>">
                                    
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="achievementTitle" class="form-label">Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="achievementTitle" name="title" 
                                                       value="<?= htmlspecialchars($editAchievement['title'] ?? '') ?>" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="achievementOrder" class="form-label">Display Order</label>
                                                <input type="number" class="form-control" id="achievementOrder" name="display_order" 
                                                       value="<?= $editAchievement['display_order'] ?? (count($achievements) + 1) ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="achievementDescription" class="form-label">Description <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="achievementDescription" name="description" rows="3" required><?= htmlspecialchars($editAchievement['description'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="achievementIcon" class="form-label">Icon <span class="text-danger">*</span></label>
                                                <select class="form-select" id="achievementIcon" name="icon" required>
                                                    <option value="">Select Icon</option>
                                                    <?php foreach ($icons as $icon): ?>
                                                    <option value="<?= $icon ?>" <?= ($editAchievement && $editAchievement['icon'] === $icon) ? 'selected' : '' ?>>
                                                        <?= str_replace('fa-', '', $icon) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="achievementColor" class="form-label">Color <span class="text-danger">*</span></label>
                                                <select class="form-select" id="achievementColor" name="color" required>
                                                    <option value="">Select Color</option>
                                                    <?php foreach ($colors as $color): ?>
                                                    <option value="<?= $color ?>" <?= ($editAchievement && $editAchievement['color'] === $color) ? 'selected' : '' ?>>
                                                        <?= ucfirst(str_replace('text-', '', $color)) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="achievementActive" name="is_active" 
                                                       <?= ($editAchievement && $editAchievement['is_active']) || !$editAchievement ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="achievementActive">Active</label>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center border-top pt-3">
                                            <div id="iconPreview" class="preview-icon mb-2">
                                                <?php if ($editAchievement): ?>
                                                <i class="fas <?= htmlspecialchars($editAchievement['icon']) ?> <?= htmlspecialchars($editAchievement['color']) ?>"></i>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">Icon Preview</small>
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
                
                <?php elseif ($action === 'team'): ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Manage Team Members</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                                <i class="fas fa-plus me-2"></i>Add New Member
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th width="100">Image</th>
                                    <th width="120">Status</th>
                                    <th width="100">Order</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teamMembers as $index => $member): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($member['name']) ?></td>
                                    <td><?= htmlspecialchars($member['position']) ?></td>
                                    <td>
                                        <?php if ($member['image_path']): ?>
                                        <img src="../../<?= htmlspecialchars($member['image_path']) ?>" alt="Member" style="width: 50px; height: 50px; object-fit: cover;" class="rounded-circle">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?= $member['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                            <?= $member['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= $member['display_order'] ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="?action=team&edit_team=<?= $member['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="post" action="manage-about.php?action=delete-team" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this team member?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($teamMembers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No team members found. Add your first team member using the button above.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Add/Edit Team Member Modal -->
                    <div class="modal fade" id="addTeamModal" tabindex="-1" aria-labelledby="addTeamModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addTeamModalLabel">
                                        <?= $editTeamMember ? 'Edit Team Member' : 'Add New Team Member' ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post" action="manage-about.php?action=save-team" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?= $editTeamMember['id'] ?? 0 ?>">
                                    <?php if ($editTeamMember && !empty($editTeamMember['image_path'])): ?>
                                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editTeamMember['image_path']) ?>">
                                    <?php endif; ?>
                                    
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="teamName" class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="teamName" name="name" 
                                                       value="<?= htmlspecialchars($editTeamMember['name'] ?? '') ?>" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="teamPosition" class="form-label">Position <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="teamPosition" name="position" 
                                                       value="<?= htmlspecialchars($editTeamMember['position'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="teamDescription" class="form-label">Description</label>
                                            <textarea class="form-control" id="teamDescription" name="description" rows="3"><?= htmlspecialchars($editTeamMember['description'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="teamImage" class="form-label">Profile Image</label>
                                                <input type="file" class="form-control" id="teamImage" name="image" accept="image/*">
                                                <div class="form-text">Max size: 2MB (JPG, PNG, GIF)</div>
                                                
                                                <?php if ($editTeamMember && !empty($editTeamMember['image_path'])): ?>
                                                <div class="mt-2">
                                                    <small>Current Image:</small>
                                                    <img src="../../<?= htmlspecialchars($editTeamMember['image_path']) ?>" class="img-thumbnail mt-1">
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="teamOrder" class="form-label">Display Order</label>
                                                <input type="number" class="form-control" id="teamOrder" name="display_order" 
                                                       value="<?= $editTeamMember['display_order'] ?? (count($teamMembers) + 1) ?>">
                                                
                                                <div class="form-check form-switch mt-3">
                                                    <input class="form-check-input" type="checkbox" id="teamActive" name="is_active" 
                                                           <?= ($editTeamMember && $editTeamMember['is_active']) || !$editTeamMember ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="teamActive">Active</label>
                                                </div>
                                            </div>
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
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-open modal if editing
        <?php if ($editAchievement): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('addAchievementModal'));
            modal.show();
        });
        <?php endif; ?>
        
        <?php if ($editTeamMember): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('addTeamModal'));
            modal.show();
        });
        <?php endif; ?>
        
        // Update icon preview
        document.addEventListener('DOMContentLoaded', function() {
            const iconSelect = document.getElementById('achievementIcon');
            const colorSelect = document.getElementById('achievementColor');
            const preview = document.getElementById('iconPreview');
            
            function updatePreview() {
                if (iconSelect.value && colorSelect.value) {
                    preview.innerHTML = `<i class="fas ${iconSelect.value} ${colorSelect.value}"></i>`;
                } else {
                    preview.innerHTML = '';
                }
            }
            
            if (iconSelect && colorSelect && preview) {
                iconSelect.addEventListener('change', updatePreview);
                colorSelect.addEventListener('change', updatePreview);
                
                // Initialize preview if editing
                if (iconSelect.value && colorSelect.value) {
                    updatePreview();
                }
            }
        });
    </script>
</body>
</html>
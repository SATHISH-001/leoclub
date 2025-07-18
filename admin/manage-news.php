<?php
require_once 'config.php';
require_once 'functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin permissions (uncomment when ready)
// if (!hasPermission('admin')) {
//     header('Location: /login.php');
//     exit;
// }

$pdo = getPDO();
$pageTitle = "Manage News";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['save_news'])) {
            $id = (int)($_POST['news_id'] ?? 0);
            $title = sanitizeInput($_POST['title'] ?? '');
            $content = sanitizeInput($_POST['content'] ?? '');
            $publish_date = sanitizeInput($_POST['publish_date'] ?? date('Y-m-d'));
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            // Validate input
            if (empty($title) || empty($content)) {
                throw new Exception("Title and content are required");
            }
            
            // Handle file upload
            $image_path = $_POST['existing_image'] ?? '';
            if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../assets/uploads/news/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Validate image size (max 2MB)
                if ($_FILES['news_image']['size'] > 2097152) {
                    throw new Exception("Image size must be less than 2MB");
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['news_image']['name']);
                $targetPath = $uploadDir . $fileName;
                
                // Validate image type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['news_image']['tmp_name']);
                
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Only JPG, PNG, and GIF files are allowed");
                }
                
                if (move_uploaded_file($_FILES['news_image']['tmp_name'], $targetPath)) {
                    $image_path = $fileName;
                    
                    // Delete old image if exists
                    if (!empty($_POST['existing_image']) && file_exists($uploadDir . $_POST['existing_image'])) {
                        unlink($uploadDir . $_POST['existing_image']);
                    }
                } else {
                    throw new Exception("Error uploading image");
                }
            }
            
            if ($id > 0) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ?, image_path = ?, publish_date = ?, is_featured = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $content, $image_path, $publish_date, $is_featured, $id]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO news (title, content, image_path, publish_date, is_featured, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $content, $image_path, $publish_date, $is_featured]);
            }
            
            $_SESSION['success_message'] = "News article saved successfully!";
            header("Location: manage-news.php");
            exit();
        }
        elseif (isset($_POST['delete_news'])) {
            $id = (int)($_POST['news_id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception("Invalid news ID");
            }
            
            // Get image path first
            $stmt = $pdo->prepare("SELECT image_path FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            
            if ($row && !empty($row['image_path'])) {
                $imagePath = '../../assets/uploads/news/' . $row['image_path'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Delete record
            $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success_message'] = "News article deleted successfully!";
            header("Location: manage-news.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: manage-news.php");
        exit();
    }
}

// Get all news articles with is_featured included
$news = [];
try {
    $stmt = $pdo->query("SELECT id, title, content, image_path, publish_date, is_featured FROM news ORDER BY publish_date DESC");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error loading news: " . $e->getMessage();
}

require_once 'heading.php';
?>
   
<div class="admin-container">
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'admin-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage News</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newsModal">
                            <i class="fas fa-plus me-1"></i> Add News
                        </button>
                    </div>
                </div>
                
                <!-- Status Messages -->
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
                
                <!-- News Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Publish Date</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($news)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No news articles found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($news as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['title']) ?></td>
                                            <td><?= date('M j, Y', strtotime($item['publish_date'])) ?></td>
                                            <td>
                                                <?php if ($item['is_featured']): ?>
                                                    <span class="badge bg-success">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-news" 
                                                        data-id="<?= $item['id'] ?>"
                                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                                        data-content="<?= htmlspecialchars($item['content']) ?>"
                                                        data-image="<?= htmlspecialchars($item['image_path'] ?? '') ?>"
                                                        data-date="<?= $item['publish_date'] ?>"
                                                        data-featured="<?= $item['is_featured'] ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#newsModal">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" name="delete_news" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this news article?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
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
</div>

<!-- News Modal (for Add/Edit) -->

<div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="newsForm">
                <input type="hidden" name="news_id" id="newsId">
                <input type="hidden" name="existing_image" id="existingImage">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="newsModalLabel">Add News Article</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="8" required></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="publish_date" class="form-label">Publish Date *</label>
                                <input type="date" class="form-control" id="publish_date" name="publish_date" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1">
                                <label class="form-check-label" for="is_featured">Featured Article</label>
                            </div>
                            
                            <div class="mb-3">
                                <label for="news_image" class="form-label">News Image</label>
                                <input type="file" class="form-control" id="news_image" name="news_image" accept="image/jpeg, image/png, image/gif">
                                <div class="form-text">Max size: 2MB. Allowed types: JPG, PNG, GIF</div>
                                <div id="imagePreview" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="save_news">Save News</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const newsModal = document.getElementById('newsModal');
    const form = document.getElementById('newsForm');
    const modalTitle = document.getElementById('newsModalLabel');
    const newsIdInput = document.getElementById('newsId');
    const existingImageInput = document.getElementById('existingImage');
    const imagePreview = document.getElementById('imagePreview');
    const featuredCheckbox = document.getElementById('is_featured');
    
    // Edit buttons
    document.querySelectorAll('.edit-news').forEach(button => {
        button.addEventListener('click', function() {
            modalTitle.textContent = 'Edit News Article';
            newsIdInput.value = this.dataset.id;
            
            // Fill form with existing data
            document.getElementById('title').value = this.dataset.title;
            document.getElementById('content').value = this.dataset.content;
            document.getElementById('publish_date').value = this.dataset.date;
            featuredCheckbox.checked = this.dataset.featured === '1';
            
            const imagePath = this.dataset.image;
            if (imagePath) {
                existingImageInput.value = imagePath;
                imagePreview.innerHTML = `
                    <img src="/assets/uploads/news/${imagePath}" class="img-thumbnail" style="max-height: 150px;">
                    <div class="mt-1">Current image</div>
                `;
            } else {
                imagePreview.innerHTML = '';
                existingImageInput.value = '';
            }
        });
    });
    
    // File input preview
    document.getElementById('news_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 2097152) {
                alert('File size must be less than 2MB');
                e.target.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Only JPG, PNG, and GIF files are allowed');
                e.target.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.innerHTML = `
                    <img src="${event.target.result}" class="img-thumbnail" style="max-height: 150px;">
                    <div class="mt-1">${file.name}</div>
                `;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Reset form when modal is closed
    newsModal.addEventListener('hidden.bs.modal', function() {
        form.reset();
        imagePreview.innerHTML = '';
        modalTitle.textContent = 'Add News Article';
        newsIdInput.value = '';
        existingImageInput.value = '';
        featuredCheckbox.checked = false;
    });
});
</script>


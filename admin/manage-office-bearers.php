<?php
require_once 'config.php';
require_once 'functions.php';

$pdo = getPDO();
$pageTitle = "Manage Office Bearers";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['save_bearer'])) {
            // Validate required fields
            $required = ['name', 'position', 'position_order', 'year_type'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required fields");
                }
            }

            $id = isset($_POST['bearer_id']) ? (int)$_POST['bearer_id'] : 0;
            $name = sanitizeInput($_POST['name']);
            $position = sanitizeInput($_POST['position']);
            $position_order = (int)$_POST['position_order'];
            $year_type = sanitizeInput($_POST['year_type']);
            $department = sanitizeInput($_POST['department'] ?? '');
            $contact = sanitizeInput($_POST['contact'] ?? '');
            $photo = $_POST['existing_photo'] ?? '';

            // Handle file upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Create directory if it doesn't exist
                if (!file_exists(BEARERS_UPLOAD_DIR)) {
                    mkdir(BEARERS_UPLOAD_DIR, 0755, true);
                }

                // Validate image
                $allowedTypes = ['image/jpeg', 'image/png'];
                $maxSize = 2 * 1024 * 1024; // 2MB
                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($fileInfo, $_FILES['photo']['tmp_name']);

                if (!in_array($mimeType, $allowedTypes)) {
                    throw new Exception("Only JPG and PNG images are allowed");
                }

                if ($_FILES['photo']['size'] > $maxSize) {
                    throw new Exception("Image size must be less than 2MB");
                }

                // Generate safe filename
                $extension = $mimeType === 'image/jpeg' ? '.jpg' : '.png';
                $filename = uniqid() . $extension;
                $targetPath = BEARERS_UPLOAD_DIR . $filename;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    // Delete old photo if exists
                    if ($photo && file_exists(BEARERS_UPLOAD_DIR . $photo)) {
                        unlink(BEARERS_UPLOAD_DIR . $photo);
                    }
                    $photo = $filename;
                } else {
                    throw new Exception("Failed to upload photo");
                }
            }

            // Save to database
            if ($id > 0) {
                // Update existing bearer
                $stmt = $pdo->prepare("UPDATE office_bearers SET 
                    name = ?, position = ?, position_order = ?, 
                    department = ?, contact = ?, photo = ?, year_type = ?
                    WHERE id = ?");
                $stmt->execute([$name, $position, $position_order, $department, $contact, $photo, $year_type, $id]);
                $message = "Office bearer updated successfully!";
            } else {
                // Add new bearer
                $stmt = $pdo->prepare("INSERT INTO office_bearers 
                    (name, position, position_order, department, contact, photo, year_type) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $position, $position_order, $department, $contact, $photo, $year_type]);
                $message = "Office bearer added successfully!";
            }

            $_SESSION['success'] = $message;
            header("Location: manage-office-bearers.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: manage-office-bearers.php");
        exit();
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];
        
        // Get bearer data first to delete photo
        $stmt = $pdo->prepare("SELECT photo FROM office_bearers WHERE id = ?");
        $stmt->execute([$id]);
        $bearer = $stmt->fetch();

        if ($bearer && $bearer['photo']) {
            $photoPath = BEARERS_UPLOAD_DIR . $bearer['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        // Delete the record
        $stmt = $pdo->prepare("DELETE FROM office_bearers WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = "Office bearer deleted successfully!";
        header("Location: manage-office-bearers.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: manage-office-bearers.php");
        exit();
    }
}

// Get all office bearers grouped by year_type
$stmt = $pdo->query("SELECT * FROM office_bearers ORDER BY 
    FIELD(year_type, 'current', '2nd_year', '3rd_year', 'final_year', 'past'), 
    position_order ASC");
$bearers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group bearers by year_type
$groupedBearers = [
    'current' => [],
    '2nd_year' => [],
    '3rd_year' => [],
    'final_year' => [],
    'past' => []
];

foreach ($bearers as $bearer) {
    $groupedBearers[$bearer['year_type']][] = $bearer;
}

require_once 'heading.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" width="400px" height="400px" alt="leo">
</head>
<body>

<div class="admin-container">
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'admin-sidebar.php'; ?>

            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Office Bearers</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bearerModal" onclick="resetForm()">
                        <i class="fas fa-plus me-1"></i> Add New
                    </button>
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
                
                <!-- Current Office Bearers -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Current Office Bearers</h5>
                    </div>
                    <div class="card-body">
                        <?= renderBearersTable($groupedBearers['current']) ?>
                    </div>
                </div>
                
                <!-- 2nd Year Coordinators -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">3rd Year Coordinators</h5>
                    </div>
                    <div class="card-body">
                        <?= renderBearersTable($groupedBearers['2nd_year']) ?>
                    </div>
                </div>
                
                <!-- 3rd Year Coordinators -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">2nd Year Coordinators</h5>
                    </div>
                    <div class="card-body">
                        <?= renderBearersTable($groupedBearers['3rd_year']) ?>
                    </div>
                </div>
                
                <!-- Past Office Bearers -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">LEO Fam</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            Note: LEO Fam section displays a group photo with the club's Instagram ID. 
                            To update this, replace the image file "leo_fam_photo.webp" in the media storage.
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Bearer Modal -->
<div class="modal fade" id="bearerModal" tabindex="-1" aria-labelledby="bearerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="bearerForm">
                <input type="hidden" name="bearer_id" id="bearerId">
                <input type="hidden" name="existing_photo" id="existingPhoto">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="bearerModalLabel">Add Office Bearer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="year_type" class="form-label">Type *</label>
                        <select class="form-select" id="year_type" name="year_type" required>
                            <option value="current">Current Office Bearer</option>
                            <option value="2nd_year">3rd Year Coordinator</option>
                            <option value="3rd_year">2nd Year Coordinator</option>
                            <option value="past">Past Office Bearer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="position" class="form-label">Position *</label>
                        <input type="text" class="form-control" id="position" name="position" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="position_order" class="form-label">Display Order *</label>
                        <input type="number" class="form-control" id="position_order" name="position_order" required min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="department" name="department">
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact" class="form-label">Instagram ID (without @)</label>
                        <input type="text" class="form-control" id="contact" name="contact" placeholder="username">
                    </div>
                    
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <div class="form-text">Max size: 2MB. Allowed types: JPG, PNG</div>
                        <div id="photoPreview" class="mt-2"></div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="save_bearer">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bearerModal = document.getElementById('bearerModal');
    const form = document.getElementById('bearerForm');
    const modalTitle = document.getElementById('bearerModalLabel');
    const bearerId = document.getElementById('bearerId');
    const existingPhoto = document.getElementById('existingPhoto');
    const photoPreview = document.getElementById('photoPreview');
    
    // Edit buttons
    document.querySelectorAll('.edit-bearer').forEach(button => {
        button.addEventListener('click', function() {
            modalTitle.textContent = 'Edit Office Bearer';
            bearerId.value = this.getAttribute('data-id');
            
            // Fill form with existing data
            document.getElementById('name').value = this.getAttribute('data-name');
            document.getElementById('position').value = this.getAttribute('data-position');
            document.getElementById('position_order').value = this.getAttribute('data-order');
            document.getElementById('department').value = this.getAttribute('data-department');
            document.getElementById('contact').value = this.getAttribute('data-contact');
            document.getElementById('year_type').value = this.getAttribute('data-year-type');
            
            const photo = this.getAttribute('data-photo');
            existingPhoto.value = photo;
            
            if (photo) {
                photoPreview.innerHTML = `
                    <img src="<?= BEARERS_UPLOAD_URL ?>${photo}" class="img-thumbnail" style="max-height:100px;">
                    <div class="mt-1">Current photo</div>
                `;
            } else {
                photoPreview.innerHTML = '';
            }
        });
    });
    
    // File input preview
    document.getElementById('photo').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                photoPreview.innerHTML = `
                    <img src="${event.target.result}" class="img-thumbnail" style="max-height:100px;">
                    <div class="mt-1">${file.name}</div>
                `;
            };
            
            reader.readAsDataURL(file);
        }
    });
});

function resetForm() {
    document.getElementById('bearerForm').reset();
    document.getElementById('bearerId').value = '';
    document.getElementById('existingPhoto').value = '';
    document.getElementById('photoPreview').innerHTML = '';
    document.getElementById('bearerModalLabel').textContent = 'Add Office Bearer';
    document.getElementById('year_type').value = 'current';
}
</script>

<?php
// Helper function to render bearers table
function renderBearersTable($bearers) {
    if (empty($bearers)) {
        return '<div class="alert alert-info">No records found</div>';
    }
    
    ob_start();
    ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Instagram</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bearers as $index => $bearer): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <?php if (!empty($bearer['photo'])): ?>
                        <img src="<?= BEARERS_UPLOAD_URL . htmlspecialchars($bearer['photo']) ?>" 
                             class="img-thumbnail" 
                             style="width: 60px; height: 60px; object-fit: cover;" 
                             alt="<?= htmlspecialchars($bearer['name']) ?>">
                        <?php else: ?>
                        <div class="text-muted">No photo</div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($bearer['name']) ?></td>
                    <td><?= htmlspecialchars($bearer['position']) ?></td>
                    <td><?= htmlspecialchars($bearer['department']) ?></td>
                    <td><?= !empty($bearer['contact']) ? '@'.htmlspecialchars($bearer['contact']) : '-' ?></td>
                    <td><?= $bearer['position_order'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-bearer" 
                                data-id="<?= $bearer['id'] ?>"
                                data-name="<?= htmlspecialchars($bearer['name']) ?>"
                                data-position="<?= htmlspecialchars($bearer['position']) ?>"
                                data-order="<?= $bearer['position_order'] ?>"
                                data-department="<?= htmlspecialchars($bearer['department']) ?>"
                                data-contact="<?= htmlspecialchars($bearer['contact']) ?>"
                                data-photo="<?= htmlspecialchars($bearer['photo']) ?>"
                                data-year-type="<?= htmlspecialchars($bearer['year_type']) ?>"
                                data-bs-toggle="modal" 
                                data-bs-target="#bearerModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="manage-office-bearers.php?delete=<?= $bearer['id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure you want to delete this office bearer?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
?>
</body>
</html>
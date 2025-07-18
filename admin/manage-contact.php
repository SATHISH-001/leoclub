<?php
$pageTitle = "Manage Contact";
require_once 'config.php';
require_once 'functions.php';
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize PDO connection
$pdo = getPDO();

// Get current contact info
$contactInfo = getContactInfo();

// Handle contact info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact_info'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token";
        header("Location: manage-contact.php");
        exit();
    }

    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($address)) $errors['address'] = 'Address is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    if (empty($phone)) $errors['phone'] = 'Phone information is required';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE contact_info SET 
                address = :address,
                email = :email,
                phone = :phone,
                instagram_url = :instagram,
                facebook_url = :facebook,
                twitter_url = :twitter,
                linkedin_url = :linkedin
                WHERE id = 1");
            
            $stmt->execute([
                ':address' => $address,
                ':email' => $email,
                ':phone' => $phone,
                ':instagram' => $instagram ?: null,
                ':facebook' => $facebook ?: null,
                ':twitter' => $twitter ?: null,
                ':linkedin' => $linkedin ?: null
            ]);
            
            $_SESSION['success'] = "Contact information updated successfully!";
            header("Location: manage-contact.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating contact information: " . $e->getMessage();
        }
    }
}

// Generate CSRF token
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Pagination variables for messages
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCondition = '';
$searchParams = [];
if (!empty($search)) {
    $searchCondition = "WHERE name LIKE :search OR email LIKE :search OR subject LIKE :search";
    $searchParams[':search'] = "%$search%";
}

// Get total messages count for pagination
$totalQuery = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $searchCondition");
$totalQuery->execute($searchParams);
$totalMessages = $totalQuery->fetchColumn();
$totalPages = ceil($totalMessages / $perPage);

// Get messages with pagination
$query = $pdo->prepare("SELECT * FROM contact_messages $searchCondition ORDER BY created_at DESC LIMIT :offset, :perPage");
foreach ($searchParams as $key => $value) {
    $query->bindValue($key, $value);
}
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$query->execute();
$messages = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle message deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Verify the message exists first
    $checkQuery = $pdo->prepare("SELECT id FROM contact_messages WHERE id = :id");
    $checkQuery->bindValue(':id', $id, PDO::PARAM_INT);
    $checkQuery->execute();
    
    if ($checkQuery->fetch()) {
        $deleteQuery = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
        $deleteQuery->bindValue(':id', $id, PDO::PARAM_INT);
        if ($deleteQuery->execute()) {
            $_SESSION['success'] = "Message deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting message.";
        }
    } else {
        $_SESSION['error'] = "Message not found.";
    }
    
    header("Location: manage-contact.php");
    exit();
}

// Handle message status update
if (isset($_GET['mark'])) {
    $id = (int)$_GET['mark'];
    $status = $_GET['status'] === 'read' ? 1 : 0;
    
    $updateQuery = $pdo->prepare("UPDATE contact_messages SET is_read = :status WHERE id = :id");
    $updateQuery->bindValue(':status', $status, PDO::PARAM_INT);
    $updateQuery->bindValue(':id', $id, PDO::PARAM_INT);
    if ($updateQuery->execute()) {
        $_SESSION['success'] = "Message status updated!";
    } else {
        $_SESSION['error'] = "Error updating message status.";
    }
    
    header("Location: manage-contact.php");
    exit();
}

require_once 'heading.php';
?>

<div class="admin-container">
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'admin-sidebar.php'; ?>
            
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Contact Information</h1>
                </div>
                
                <!-- Status Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Update Contact Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                                                  id="address" name="address" rows="3" required><?php echo htmlspecialchars($contactInfo['address'] ?? ''); ?></textarea>
                                        <?php if (isset($errors['address'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['address']; ?></div>
                                        <?php endif; ?>
                                        <small class="text-muted">HTML tags allowed for formatting (e.g., &lt;br&gt; for line breaks)</small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                                   id="email" name="email" value="<?php echo htmlspecialchars($contactInfo['email'] ?? ''); ?>" required>
                                            <?php if (isset($errors['email'])): ?>
                                                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone Information</label>
                                            <textarea class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                                      id="phone" name="phone" rows="2" required><?php echo htmlspecialchars($contactInfo['phone'] ?? ''); ?></textarea>
                                            <?php if (isset($errors['phone'])): ?>
                                                <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                            <?php endif; ?>
                                            <small class="text-muted">HTML tags allowed for formatting</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="facebook" class="form-label">Facebook URL</label>
                                            <input type="url" class="form-control" id="facebook" name="facebook" 
                                                   value="<?php echo htmlspecialchars($contactInfo['facebook_url'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="twitter" class="form-label">Twitter URL</label>
                                            <input type="url" class="form-control" id="twitter" name="twitter" 
                                                   value="<?php echo htmlspecialchars($contactInfo['twitter_url'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="instagram" class="form-label">Instagram URL</label>
                                            <input type="url" class="form-control" id="instagram" name="instagram" 
                                                   value="<?php echo htmlspecialchars($contactInfo['instagram_url'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="linkedin" class="form-label">LinkedIn URL</label>
                                            <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                                   value="<?php echo htmlspecialchars($contactInfo['linkedin_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" name="update_contact_info" class="btn btn-primary">Update Contact Information</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Messages Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">Contact Messages</h5>
                            </div>
                            <div class="card-body">
                                <!-- Search Form -->
                                <form method="GET" class="mb-4">
                                    <div class="input-group">
                                        <!-- <input type="text" class="form-control" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>"> -->
                                        <!-- <button class="btn btn-primary" type="submit">Search</button> -->
                                        <?php if (!empty($search)): ?>
                                            <a href="manage-contact.php" class="btn btn-outline-secondary">Clear</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                                
                                <!-- Messages Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Subject</th>
                                                <th>Message Preview</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($messages)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No messages found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($messages as $message): ?>
                                                    <tr class="<?php echo $message['is_read'] ? '' : 'fw-bold'; ?>">
                                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                                        <td><a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"><?php echo htmlspecialchars($message['email']); ?></a></td>
                                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                                        <td><?php echo nl2br(htmlspecialchars(substr($message['message'], 0, 50) . (strlen($message['message']) )> 50 ? '...' : '')); ?></td>
                                                        <td><?php echo date('M j, Y g:i a', strtotime($message['created_at'])); ?></td>
                                                        <td>
                                                            <?php if ($message['is_read']): ?>
                                                                <span class="badge bg-success">Read</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning text-dark">Unread</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="view-message.php?id=<?php echo $message['id']; ?>" class="btn btn-primary" title="View">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <?php if ($message['is_read']): ?>
                                                                    <a href="manage-contact.php?mark=<?php echo $message['id']; ?>&status=unread" class="btn btn-secondary" title="Mark as Unread">
                                                                        <i class="fas fa-envelope"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <a href="manage-contact.php?mark=<?php echo $message['id']; ?>&status=read" class="btn btn-success" title="Mark as Read">
                                                                        <i class="fas fa-envelope-open"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <a href="manage-contact.php?delete=<?php echo $message['id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this message?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($currentPage > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="manage-contact.php?page=<?php echo $currentPage - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                                    <a class="page-link" href="manage-contact.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($currentPage < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="manage-contact.php?page=<?php echo $currentPage + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
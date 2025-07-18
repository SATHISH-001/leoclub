<?php
$pageTitle = "View Message";
require_once 'config.php';
require_once 'functions.php';
require_once 'db.php';

// Start session
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// Initialize PDO
$pdo = getPDO();

// Check if message ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid message ID";
    header("Location: manage-contact.php");
    exit();
}

$messageId = (int)$_GET['id'];

// Fetch the message
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = :id");
$stmt->bindValue(':id', $messageId, PDO::PARAM_INT);
$stmt->execute();
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    $_SESSION['error'] = "Message not found";
    header("Location: manage-contact.php");
    exit();
}

// Mark as read if not already
if (!$message['is_read']) {
    $updateStmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id");
    $updateStmt->bindValue(':id', $messageId, PDO::PARAM_INT);
    $updateStmt->execute();
}

require_once 'heading.php';
?>
<!-- <main class="col-md-1 ms-sm-auto col-lg-10 px-md-4"> -->
<div class="admin-container">
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'admin-sidebar.php'; ?>
            
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">View Message</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="manage-contact.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Messages
                        </a>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Message Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Name:</strong>
                                <p><?php echo htmlspecialchars($message['name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong>
                                <p><a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"><?php echo htmlspecialchars($message['email']); ?></a></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Subject:</strong>
                                <p><?php echo htmlspecialchars($message['subject']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Date:</strong>
                                <p><?php echo date('M j, Y g:i a', strtotime($message['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Message:</strong>
                            <div class="border p-3 bg-light">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="manage-contact.php?delete=<?php echo $message['id']; ?>" class="btn btn-danger me-2" onclick="return confirm('Are you sure you want to delete this message?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            <?php if ($message['is_read']): ?>
                                <a href="manage-contact.php?mark=<?php echo $message['id']; ?>&status=unread" class="btn btn-warning">
                                    <i class="fas fa-envelope"></i> Mark as Unread
                                </a>
                            <?php else: ?>
                                <a href="manage-contact.php?mark=<?php echo $message['id']; ?>&status=read" class="btn btn-success">
                                    <i class="fas fa-envelope-open"></i> Mark as Read
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
<?php
require_once 'db.php';
require_once 'functions.php';

// Check if admin is logged in
// session_start();
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: ../login.php');
//     exit;
// }



// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    if (deleteContactMessage($deleteId)) {
        $_SESSION['success'] = "Message deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete message.";
    }
    header("Location: messages.php");
    exit();
}


$messages = getAllContactMessages();
$pageTitle = "Contact Messages";
require_once __DIR__ . '/../admin/heading.php'; 
?>
 
<!-- <div class="container-fluid py-4">
     -->
 <div class="admin-container">
     <div class="row">
        <?php include_once  'admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Contact Messages</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="text-muted small">Total: <?= count($messages) ?></div>
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
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($messages)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No messages found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($messages as $message): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($message['name']) ?></td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($message['email']) ?>" class="text-primary">
                                                <?= htmlspecialchars($message['email']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($message['subject']) ?></td>
                                        <td class="message-cell">
                                            <div class="message-content" style="max-height: 100px; overflow: hidden;">
                                                <?= nl2br(htmlspecialchars($message['message'])) ?>
                                            </div>
                                            <button class="btn btn-sm btn-link text-primary view-more">View More</button>
                                        </td>
                                        <td class="text-center">
                                            <?= date('M d, Y h:i A', strtotime($message['created_at'])) ?>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="delete_id" value="<?= $message['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                                                    <i class="fas fa-trash-alt"></i> Delete
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

<style>
    .message-cell {
        max-width: 300px;
    }
    
    .message-content {
        white-space: pre-wrap;
        word-break: break-word;
    }
    
    .view-more {
        padding: 0;
        font-size: 0.75rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle full message view
    document.querySelectorAll('.view-more').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const content = this.previousElementSibling;
            
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                this.textContent = 'Show Less';
            } else {
                content.style.maxHeight = '100px';
                this.textContent = 'View More';
            }
        });
    });
});
</script>


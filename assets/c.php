<?php
$pageTitle = "Contact Us";
require_once 'includes/header.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $errors = [];

    // Validate inputs
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    }
    if (empty($subject)) {
        $errors['subject'] = 'Subject is required';
    }
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }

    // If no errors, process the form
    if (empty($errors)) {
        if (saveContactMessage($name, $email, $subject, $message)) {
            $success = "Thank you for your message! We'll get back to you soon.";
            // Clear form fields
            $name = $email = $subject = $message = '';
        } else {
            $errors['database'] = 'There was an error submitting your message. Please try again.';
        }
    }
}
?>

<section class="contact-section py-5">
    <div class="container">
        <h1 class="text-center mb-5">Contact Us</h1>
        
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="contact-info">
                    <h3 class="mb-4">Get in Touch</h3>
                    
                    <div class="contact-item d-flex mb-4">
                        <div class="icon-box me-4">
                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5>Address</h5>
                            <p>LEO Club <br>Alagappa Chettiar Government College of Engineering and Technology<br>Karaikudi, Tamilnadu - 630006</p>
                        </div>
                    </div>
                    
                    <div class="contact-item d-flex mb-4">
                        <div class="icon-box me-4">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5>Email</h5>
                            <p>leoclubacgcet@gmail.com<br></p>
                        </div>
                    </div>
                    
                    <div class="contact-item d-flex mb-4">
                        <div class="icon-box me-4">
                            <i class="fas fa-phone-alt fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5>Phone</h5>
                            <p>President Aakash +91 9677574657 <br>Secretary Dheva dharshini +91 6369991886 <br>Joint-Secretary Lohidha +91 9944344175</p>
                        </div>
                    </div>
                    
                    <div class="social-links mt-4">
                        <h5 class="mb-3">Follow Us On Instagram</h5>
                        <!-- <a href="#" class="me-3"><i class="fab fa-facebook-f fa-2x"></i></a> -->
                        <!-- <a href="#" class="me-3"><i class="fab fa-twitter fa-2x"></i></a> -->
                        <a href="" class="me-3"><i class="fab fa-instagram fa-2x"></i></a>
                        <!-- <a href="#" class="me-3"><i class="fab fa-linkedin-in fa-2x"></i></a> -->
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="contact-form card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Send us a Message</h3>
                        
                        <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($errors['database'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="contact.php">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                       id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                                <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>" 
                                       id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>">
                                <?php if (isset($errors['subject'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['subject']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message</label>
                                <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                                          id="message" name="message" rows="5"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                                <?php if (isset($errors['message'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['message']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Google Maps Embed -->
<section class="map-section py-0">
    <div class="container-fluid px-0">
        <div class="ratio ratio-21x9">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3888.80123456789!2d77.12345678901234!3d12.345678901234567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTLCsDIwJzQ0LjQiTiA3N8KwMDcnNDEuOSJF!5e0!3m2!1sen!2sin!4v1234567890123!5m2!1sen!2sin" 
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>


     
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage Contact Messages</h5>
                    <div>
                        <a href="export-contact.php" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-export"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="manage-contact.php" class="btn btn-outline-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <!-- Messages Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($messages)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No messages found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($messages as $message): ?>
                                        <tr class="<?php echo $message['is_read'] ? '' : 'table-primary'; ?>">
                                            <td><?php echo $message['id']; ?></td>
                                            <td><?php echo htmlspecialchars($message['name']); ?></td>
                                            <td><a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"><?php echo htmlspecialchars($message['email']); ?></a></td>
                                            <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                            <td><?php echo formatDate($message['created_at']); ?></td>
                                            <td>
                                                <?php if ($message['is_read']): ?>
                                                    <span class="badge bg-success">Read</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Unread</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view-contact.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($message['is_read']): ?>
                                                    <a href="manage-contact.php?mark=<?php echo $message['id']; ?>&status=unread" class="btn btn-sm btn-secondary" title="Mark as Unread">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="manage-contact.php?mark=<?php echo $message['id']; ?>&status=read" class="btn btn-sm btn-success" title="Mark as Read">
                                                        <i class="fas fa-envelope-open"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="manage-contact.php?delete=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this message?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
                                        <a class="page-link" href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
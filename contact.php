<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "Contact Us";

// Load required files in correct order
require_once 'includes/db.php'; // Must be first to initialize $pdo
$pdo = getPDO(); // Initialize the PDO connection
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Get contact information from database
try {
    $contactInfo = getContactInfo();
} catch (Exception $e) {
    // Handle error gracefully
    error_log("Error getting contact info: " . $e->getMessage());
    $contactInfo = [
        'address' => 'LEO Club<br>Alagappa Chettiar Government College of Engineering and Technology<br>Karaikudi, Tamilnadu - 630006',
        'email' => 'leoclubacgcet@gmail.com',
        'phone' => 'President Aakash +91 9677574657 <br>Secretary Dheva dharshini +91 6369991886 <br>Joint-Secretary Lohidha +91 9944344175',
        'instagram_url' => 'https://instagram.com/leoclubacgcet',
        'facebook_url' => '',
        'twitter_url' => '',
        'linkedin_url' => ''
    ];
}

// Handle form submission
$errors = [];
$success = '';
$name = $email = $subject = $message = '';

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
                            <p><?php echo $contactInfo['address']; ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item d-flex mb-4">
                        <div class="icon-box me-4">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5>Email</h5>
                            <p>
                                <a href="mailto:<?php echo $contactInfo['email']; ?>">
                                    <?php echo $contactInfo['email']; ?>
                                </a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item d-flex mb-4">
                        <div class="icon-box me-4">
                            <i class="fas fa-phone-alt fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5>Phone</h5>
                            <p><?php echo $contactInfo['phone']; ?></p>
                        </div>
                    </div>
                    
                    <div class="social-links mt-4">
                        <h5 class="mb-3">Follow Us</h5>
                        <?php if (!empty($contactInfo['facebook_url'])): ?>
                            <a href="<?php echo $contactInfo['facebook_url']; ?>" class="me-3" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-facebook-f fa-2x"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($contactInfo['twitter_url'])): ?>
                            <a href="<?php echo $contactInfo['twitter_url']; ?>" class="me-3" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-twitter fa-2x"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($contactInfo['instagram_url'])): ?>
                            <a href="<?php echo $contactInfo['instagram_url']; ?>" class="me-3" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-instagram fa-2x"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($contactInfo['linkedin_url'])): ?>
                            <a href="<?php echo $contactInfo['linkedin_url']; ?>" class="me-3" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-linkedin-in fa-2x"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="contact-form card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Send us a Feedback</h3>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors['database'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="contact.php">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                       id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>" 
                                       id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                                <?php if (isset($errors['subject'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['subject']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Feedback</label>
                                <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                                          id="message" name="message" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                                <?php if (isset($errors['message'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['message']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Send Feedback</button>
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
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3927.527622315678!2d78.7732143147946!3d10.10701839279642!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b005dee4a8a1a0d%3A0x9b9c1a7b3c1b1b1b!2sAlagappa%20Chettiar%20Government%20College%20of%20Engineering%20and%20Technology!5e0!3m2!1sen!2sin!4v1620000000000!5m2!1sen!2sin" 
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<style>
/* Contact Section Styling */
.contact-section {
    background-color: #f8f9fa;
    padding: 80px 0;
}

.contact-section h1 {
    font-weight: 700;
    position: relative;
    margin-bottom: 50px;
    color: #2c3e50;
}

.contact-section h1:after {
    content: "";
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
}

/* Contact Info Card */
.contact-info {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    height: 100%;
}

.contact-info h3 {
    color: #2c3e50;
    font-weight: 600;
    position: relative;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.contact-info h3:after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
}

.contact-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-item h5 {
    color: #3498db;
    font-weight: 600;
    margin-bottom: 5px;
}

.contact-item p {
    color: #555;
    margin-bottom: 0;
}

.contact-item a {
    color: #3498db;
    text-decoration: none;
}

.contact-item a:hover {
    text-decoration: underline;
}

.icon-box {
    width: 50px;
    height: 50px;
    background: rgba(52, 152, 219, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Contact Form Styling */
.contact-form {
    border: none;
    border-radius: 10px;
}

.contact-form .card-body {
    padding: 30px;
}

.contact-form .card-title {
    color: #2c3e50;
    font-weight: 600;
    position: relative;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.contact-form .card-title:after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
}

.contact-form .form-control {
    padding: 12px 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
    transition: all 0.3s;
}

.contact-form .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
}

.contact-form textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.contact-form .btn-primary {
    background: linear-gradient(to right, #3498db, #2c3e50);
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s;
}

.contact-form .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Social Links */
.social-links a svg {
    fill: #3498db;
    transform: translateY(-3px);
}

.social-links a:hover svg {
    fill: #3498db;
    transform: translateY(-3px);
}

/* Map Section */
.map-section {
    padding: 0;
}

.map-section iframe {
    border: none;
    filter: grayscale(20%) contrast(90%);
}

/* Responsive Design */
@media (max-width: 992px) {
    .contact-section {
        padding: 60px 0;
    }
    
    .contact-info, .contact-form .card-body {
        padding: 25px;
    }
}

@media (max-width: 768px) {
    .contact-section {
        padding: 50px 0;
    }
    
    .contact-section h1 {
        font-size: 2rem;
        margin-bottom: 40px;
    }
    
    .contact-info, .contact-form .card-body {
        padding: 20px;
    }
    
    .contact-item {
        padding: 12px 0;
    }
}

@media (max-width: 576px) {
    .contact-section {
        padding: 40px 0;
    }
    
    .contact-section h1 {
        font-size: 1.8rem;
    }
    
    .contact-info h3, .contact-form .card-title {
        font-size: 1.3rem;
    }
    
    .icon-box {
        width: 40px;
        height: 40px;
    }
    
    .icon-box i {
        font-size: 1.5rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
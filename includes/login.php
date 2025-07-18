<?php
// MUST BE ABSOLUTE FIRST LINE - NO WHITESPACE BEFORE!
session_start();
ob_start();

// Include necessary files BEFORE using their functions
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';  // This contains sanitizeInput()

// Store current URL for redirect
$pdo = getPDO(); // Call the function to get the connection

// Store current URL for redirect
if (!isset($_SESSION['redirect_url']) && !in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'register.php'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
}

$pageTitle = "Login";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Clear output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            $redirect = $_SESSION['redirect_url'] ?? 'index.php';
            unset($_SESSION['redirect_url']);
            
            header("Location: " . $redirect);
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        $error = "Database error. Please try again later.";
        error_log("Login error: " . $e->getMessage());
    }
}

// require_once __DIR__ . '/header.php';
?>

<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #f8f9fc;
        --accent-color: #ff6b6b;
        --dark-color: #2c3e50;
        --light-color: #ffffff;
    }
    
    .login-section {
        background: linear-gradient(135deg, rgba(78, 115, 223, 0.1), rgba(248, 249, 252, 0.8));
        min-height: 80vh;
        display: flex;
        align-items: center;
    }
    
    .login-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .login-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .login-header {
        background: linear-gradient(135deg, var(--primary-color), #224abe);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }
    
    .login-header h2 {
        font-weight: 700;
        margin-bottom: 0;
    }
    
    .login-body {
        padding: 2rem;
    }
    
    .form-control {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
    
    .btn-login {
        background: linear-gradient(135deg, var(--primary-color), #224abe);
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
    }
    
    .login-footer {
        text-align: center;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
        margin-top: 1.5rem;
    }
    
    .login-footer a {
        color: var(--primary-color);
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .login-footer a:hover {
        color: #224abe;
        text-decoration: underline;
    }
    
    .social-login {
        margin: 1.5rem 0;
    }
    
    .social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .social-btn i {
        margin-right: 10px;
        font-size: 1.2rem;
    }
    
    .btn-google {
        background-color: #fff;
        color: #757575;
        border: 1px solid #ddd;
    }
    
    .btn-google:hover {
        background-color: #f8f9fa;
        border-color: #ccc;
    }
    
    .btn-facebook {
        background-color: #3b5998;
        color: white;
    }
    
    .btn-facebook:hover {
        background-color: #344e86;
    }
    
    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #aaa;
    }
    
    .password-toggle:hover {
        color: var(--primary-color);
    }
    
    .password-wrapper {
        position: relative;
    }
</style>

<section class="login-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="login-card">
                    <div class="login-header">
                        <h2 class="animate__animated animate__fadeInDown">Welcome Back!</h2>
                    </div>
                    <div class="login-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger animate__animated animate__shakeX">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="login.php" method="POST" class="animate__animated animate__fadeIn">
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <div class="password-wrapper">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                    </div>
                                    <span class="password-toggle" id="togglePassword">
                                        <i class="far fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>
                                <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-login btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                          
                        </form>
                        
                        <div class="login-footer animate__animated animate__fadeInUp">
                            <p>Don't have an account? <a href="register.php" class="fw-bold">Create an account</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Password toggle functionality
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
    });
    
    // Add animation to form elements
    document.addEventListener('DOMContentLoaded', function() {
        const formGroups = document.querySelectorAll('.mb-4');
        formGroups.forEach((group, index) => {
            group.style.animationDelay = `${index * 100}ms`;
        });
    });
</script>


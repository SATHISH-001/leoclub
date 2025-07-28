<?php
ob_start();
session_start();

// Database configuration
$db_host = 'localhost';
$db_name = 'leoclub_dbs';
$db_user = 'root';
$db_pass = '';

// Initialize variables
$errors = [];
$formData = ['name' => '', 'email' => '', 'phone' => '', 'department' => ''];
$departments = ['CSE', 'ECE', 'Mechanical', 'Civil', 'EEE', 'IT'];
$success = false;

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate inputs
        $formData['name'] = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
        $formData['email'] = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $formData['phone'] = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
        $formData['department'] = trim(filter_input(INPUT_POST, 'department', FILTER_SANITIZE_STRING));
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate name
        if (empty($formData['name'])) {
            $errors['name'] = 'Full name is required';
        } elseif (strlen($formData['name']) > 100) {
            $errors['name'] = 'Name must be less than 100 characters';
        } elseif (!preg_match("/^[a-zA-Z ]*$/", $formData['name'])) {
            $errors['name'] = 'Only letters and spaces allowed';
        }

        // Validate email
        if (empty($formData['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
            $stmt->execute([$formData['email']]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Email already registered';
            }
        }

        // Validate phone
        if (empty($formData['phone'])) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match("/^[0-9]{10}$/", $formData['phone'])) {
            $errors['phone'] = 'Invalid phone number (must be 10 digits)';
        }

        // Validate department
        if (empty($formData['department'])) {
            $errors['department'] = 'Department is required';
        } elseif (!in_array($formData['department'], $departments)) {
            $errors['department'] = 'Invalid department selected';
        }

        // Validate password
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif (!preg_match("/[A-Z]/", $password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter';
        } elseif (!preg_match("/[a-z]/", $password)) {
            $errors['password'] = 'Password must contain at least one lowercase letter';
        } elseif (!preg_match("/[0-9]/", $password)) {
            $errors['password'] = 'Password must contain at least one number';
        } elseif ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        // If no errors, register user
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO members (name, email, phone, department, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$formData['name'], $formData['email'], $formData['phone'], $formData['department'], $hashed_password])) {
                $_SESSION['success'] = 'Registration successful! Please login.';
                header('Location: login.php');
                exit();
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $errors[] = 'Database connection failed. Please try again later.';
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage());
    $errors[] = 'An unexpected error occurred.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Leo Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
    --input-line: #ced4da;
    --input-line-focus: var(--primary);
}

body {
    background-color: var(--secondary);
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    line-height: 1.6;
    color: var(--dark);
    margin: 0;
    padding: 0;
}

.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background-color: var(--secondary);
}

.auth-card {
    max-width: 500px;
    width: 100%;
    margin: 0 auto;
    box-shadow: var(--box-shadow);
    border-radius: var(--border-radius);
    overflow: hidden;
    border: none;
    transition: var(--transition);
    background-color: var(--light);
}

.auth-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--dark);
    padding: 2rem;
    text-align: center;
    position: relative;
}

.auth-header h2 {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.logo-container {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background-color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border: 3px solid white;
}

.logo-container img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 50%;
}

.auth-body {
    padding: 2rem;
}

/* Form Elements - Borderless Design */
.form-group {
    position: relative;
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 500;
    color: var(--dark);
    margin-bottom: 0.5rem;
    display: block;
    transition: var(--transition);
}

.form-control, .form-select {
    display: block;
    width: 100%;
    padding: 0.75rem 0;
    font-size: 1rem;
    line-height: 1.5;
    color: var(--dark);
    background-color: transparent;
    background-clip: padding-box;
    border: none;
    border-bottom: 1px solid var(--input-line);
    border-radius: 0;
    transition: var(--transition);
    box-shadow: none;
}

.form-control:focus, .form-select:focus {
    outline: 0;
    border-bottom: 2px solid var(--input-line-focus);
    box-shadow: none;
}

/* Underline Animation */
.form-group::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--primary);
    transition: var(--transition);
}

.form-group:focus-within::after {
    width: 100%;
}

/* Password Toggle */
.password-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--input-line);
    transition: var(--transition);
    padding: 0.5rem;
}

.password-toggle:hover {
    color: var(--primary-dark);
}

/* Validation States */
.is-invalid {
    border-bottom-color: var(--danger) !important;
}

.invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--danger);
}

.is-invalid ~ .invalid-feedback {
    display: block;
}

/* Button Styles */
.btn-primary {
    background-color: var(--primary);
    border: none;
    padding: 0.75rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    border-radius: var(--border-radius);
    transition: var(--transition);
    color: var(--dark);
    width: 100%;
    margin-top: 1rem;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Link Styles */
.text-center a {
    color: var(--primary-dark);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.text-center a:hover {
    color: var(--primary);
    text-decoration: underline;
}

/* Alert Styles */
.alert {
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background-color: rgba(231, 76, 60, 0.1);
    border-left: 4px solid var(--danger);
    color: var(--danger);
}

/* Responsive Adjustments */
@media (max-width: 576px) {
    .auth-container {
        padding: 1rem;
    }
    
    .auth-header {
        padding: 1.5rem;
    }
    
    .auth-body {
        padding: 1.5rem;
    }
    
    .logo-container {
        width: 70px;
        height: 70px;
    }
}

/* Animation for form elements */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.auth-body > * {
    animation: fadeIn 0.4s ease forwards;
}

.auth-body > *:nth-child(1) { animation-delay: 0.1s; }
.auth-body > *:nth-child(2) { animation-delay: 0.2s; }
.auth-body > *:nth-child(3) { animation-delay: 0.3s; }
.auth-body > *:nth-child(4) { animation-delay: 0.4s; }
.auth-body > *:nth-child(5) { animation-delay: 0.5s; }
.auth-body > *:nth-child(6) { animation-delay: 0.6s; }
.auth-body > *:nth-child(7) { animation-delay: 0.7s; }
.auth-body > *:nth-child(8) { animation-delay: 0.8s; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="auth-card card">
                        <div class="auth-header">
                            <div class="logo-container">
                                <img src="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" alt="Leo Club Logo">
                            </div>
                            <h2>Join Leo Club</h2>
                            <p class="mb-0">Become a member of our community</p>
                        </div>
                        <div class="auth-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong>Please fix these issues:</strong>
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                           id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" required>
                                    <div class="invalid-feedback"><?= $errors['name'] ?? '' ?></div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           id="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>" required>
                                    <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                           id="phone" name="phone" value="<?= htmlspecialchars($formData['phone']) ?>" 
                                           pattern="[0-9]{10}" required>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?? '' ?></div>
                                    <small class="text-muted">10-digit phone number (no spaces or dashes)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <select class="form-select <?= isset($errors['department']) ? 'is-invalid' : '' ?>" 
                                            id="department" name="department" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= htmlspecialchars($dept) ?>" <?= $formData['department'] === $dept ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($dept) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"><?= $errors['department'] ?? '' ?></div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                               id="password" name="password" required>
                                        <span class="password-toggle" id="togglePassword">
                                            <i class="far fa-eye"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
                                    <small class="text-muted">Minimum 8 characters with uppercase, lowercase, and number</small>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                               id="confirm_password" name="confirm_password" required>
                                        <span class="password-toggle" id="toggleConfirmPassword">
                                            <i class="far fa-eye"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback"><?= $errors['confirm_password'] ?? '' ?></div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                    <i class="fas fa-user-plus me-2"></i>Register Now
                                </button>
                                
                                <div class="text-center">
                                    <p class="mb-0">Already have an account? <a href="login.php" class="fw-semibold">Sign in here</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = (toggleElement, inputElement) => {
                const type = inputElement.getAttribute('type') === 'password' ? 'text' : 'password';
                inputElement.setAttribute('type', type);
                toggleElement.innerHTML = type === 'password' 
                    ? '<i class="far fa-eye"></i>' 
                    : '<i class="far fa-eye-slash"></i>';
            };
            
            document.querySelector('#togglePassword').addEventListener('click', function() {
                togglePassword(this, document.querySelector('#password'));
            });
            
            document.querySelector('#toggleConfirmPassword').addEventListener('click', function() {
                togglePassword(this, document.querySelector('#confirm_password'));
            });
            
            // Form validation
            const forms = document.querySelector('.needs-validation');
            if (forms) {
                forms.addEventListener('submit', function(event) {
                    if (!forms.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    forms.classList.add('was-validated');
                }, false);
            }
        });
    </script>
</body>
</html>
<?php 
ob_end_flush();
?>
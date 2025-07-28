<?php
// MUST BE ABSOLUTE FIRST LINE - NO WHITESPACE BEFORE!
session_start();
ob_start();

// Include necessary files
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get PDO connection
$pdo = getPDO();

// Store current URL for redirect
if (!isset($_SESSION['redirect_url'])) {
  $_SESSION['redirect_url'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';
}

// Initialize variables
$loginError = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
  $email = sanitizeInput($_POST['email']);
  $password = $_POST['password'];

  try {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      // Set session variables
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
      $loginError = "Invalid email or password.";
    }
  } catch (PDOException $e) {
    $loginError = "Database error. Please try again later.";
    error_log("Login error: " . $e->getMessage());
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>LEO Club - Member Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="icon" href="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', 'Segoe UI', sans-serif;
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5));
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .login-container {
      /* background: rgba(255, 255, 255, 0.95); */
      background: transparent;
      border-radius: 15px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      position: relative;
      overflow: hidden;
      width: 100%;
      max-width: 450px;
      padding: 40px;
      z-index: 1;
    }

    .login-container::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(to bottom right,
          rgba(255, 193, 7, 0.1),
          rgba(255, 193, 7, 0.3));
      transform: rotate(15deg);
      z-index: -1;
    }

    .logo-container {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid rgba(255, 193, 7, 0.2);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    h1 {
      font-weight: 600;
      margin: 15px 0;
      color: #ffc107;
      text-align: center;
      font-size: 28px;
    }

    .tagline {
      color: #666;
      text-align: center;
      margin-bottom: 30px;
      font-size: 14px;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    .input-group {
      position: relative;
      margin-bottom: 25px;
    }

    .input-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #777;
      transition: all 0.3s;
    }

    input {
      background: transparent;
      border: none;
      border-bottom: 2px solid #ccc;
      padding: 10px 10px 10px 40px;
      width: 100%;
      font-size: 15px;
      color: #fff;
      outline: none;
      transition: border-color 0.3s;
    }

    input:focus {
      border-bottom: 2px solid #ffc107;
      background: transparent;
      box-shadow: none;
    }

    input::placeholder {
      color: #aaa;
    }


    input:focus+i {
      color: #ffc107;
    }

    .password-toggle {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #777;
      cursor: pointer;
      transition: color 0.3s;
    }

    .password-toggle:hover {
      color: #ffc107;
    }

    button[type="submit"] {
      background: linear-gradient(to right, #ffc107, #ffab00);
      color: #333;
      border: none;
      padding: 15px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-top: 10px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    button[type="submit"]:hover {
      background: linear-gradient(to right, #ffab00, #ff8f00);
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
    }

    button[type="submit"]:active {
      transform: translateY(0);
    }

    .forgot-password {
      text-align: right;
      margin: 10px 0 20px;
    }

    .forgot-password a {
      color: #777;
      font-size: 13px;
      text-decoration: none;
      transition: color 0.3s;
    }

    .forgot-password a:hover {
      color: #ffc107;
      text-decoration: underline;
    }

    .alert {
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: flex;
      align-items: center;
    }

    .alert-danger {
      background-color: rgba(220, 53, 69, 0.1);
      border: 1px solid rgba(220, 53, 69, 0.2);
      color: #dc3545;
    }

    .alert i {
      margin-right: 10px;
    }

    .footer {
      text-align: center;
      margin-top: 30px;
      color: #777;
      font-size: 13px;
    }

    .footer a {
      color: #ffc107;
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .login-container {
        padding: 30px 20px;
      }

      h1 {
        font-size: 24px;
      }

      .logo {
        width: 80px;
        height: 80px;
      }
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="logo-container">
      <img src="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" alt="LEO Club Logo" class="logo">
      <h1>Member Login</h1>
      <p class="tagline">Enter your credentials to access your account</p>
    </div>

    <?php if ($loginError): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($loginError) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" id="loginForm">
      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" placeholder="Email Address" required />
      </div>

      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="loginPassword" placeholder="Password" required />
        <button type="button" class="password-toggle" onclick="togglePassword('loginPassword', this)">
          <i class="far fa-eye"></i>
        </button>
      </div>

      <div class="forgot-password">
        <a href="forgot-password.php">Forgot your password?</a>
      </div>

      <button type="submit" name="login">Sign In</button>
    </form>

    <div class="footer">
      Don't have an account? <a href="register.php">Sign up</a>
    </div>
  </div>

  <script>
    function togglePassword(inputId, button) {
      const input = document.getElementById(inputId);
      const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
      input.setAttribute('type', type);

      // Toggle eye icon
      if (type === 'password') {
        button.innerHTML = '<i class="far fa-eye"></i>';
      } else {
        button.innerHTML = '<i class="far fa-eye-slash"></i>';
      }
    }

    // Add focus effects
    document.addEventListener('DOMContentLoaded', function() {
      const inputs = document.querySelectorAll('input');

      inputs.forEach(input => {
        input.addEventListener('focus', function() {
          this.parentNode.querySelector('i').style.color = '#ffc107';
        });

        input.addEventListener('blur', function() {
          this.parentNode.querySelector('i').style.color = '#777';
        });
      });
    });
  </script>
</body>

</html>
<?php
ob_end_flush();
?>
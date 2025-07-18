<?php
session_start();
require_once './includes/db.php';
require_once './includes/functions.php';

$pageTitle = "Forgot Password";
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate token and expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store in database
        $update = $pdo->prepare("UPDATE members SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update->execute([$token, $expiry, $email]);

        // Send email (adjust as needed)
        $resetLink = "http://yourdomain.com/reset-password.php?token=$token";
        $subject = "LEO Club - Password Reset Request";
        $body = "Hi {$user['name']},\n\nClick the following link to reset your password:\n$resetLink\n\nThis link will expire in 1 hour.";
        $headers = "From: noreply@yourdomain.com";

        mail($email, $subject, $body, $headers);

        $message = "A password reset link has been sent to your email.";
    } else {
        $message = "No account found with that email address.";
    }
}

require_once './includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4 text-center">Forgot Password</h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">Enter your registered email</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="example@example.com">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once './includes/footer.php'; ?>

<?php
// login.php - Login Page
require_once 'includes/db.php';
require_once 'includes/functions.php';
startSession();

// If already logged in, send to correct dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin')     header("Location: /eventflow/admin/dashboard.php");
    elseif ($role === 'organizer') header("Location: /eventflow/organizer/dashboard.php");
    else header("Location: /eventflow/attendee/events.php");
    exit();
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        // Find user by email
        $sql  = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        // Check password (MD5 hash comparison)
        if ($user && $user['password'] === md5($password)) {
            // Save user info in session
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin')          header("Location: /eventflow/admin/dashboard.php");
            elseif ($user['role'] === 'organizer')  header("Location: /eventflow/organizer/dashboard.php");
            else                                    header("Location: /eventflow/attendee/events.php");
            exit();
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">

        <!-- Logo -->
        <div class="auth-logo">
            <span class="icon">🎪</span>
            <h1>Event<span>Flow</span></h1>
            <p>Event Lifecycle & Ticketing Platform</p>
        </div>

        <!-- Login Card -->
        <div class="auth-card">
            <h2>Welcome Back 👋</h2>

            <!-- Show error if any -->
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= safe($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">✅ Account created! Please login.</div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
                <div class="alert alert-error">🚫 Access denied for your role.</div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com"
                           value="<?= safe($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign In →</button>
            </form>

            <!-- Demo credentials hint -->
            <div style="margin-top:20px;padding:14px;background:var(--input);border-radius:8px;font-size:13px;">
                <div style="color:var(--orange);font-weight:600;margin-bottom:8px;">🔑 Demo Credentials:</div>
                <div style="color:var(--gray);line-height:1.8;">
                    Admin: admin@eventflow.com / <b>admin123</b><br>
                    Organizer: organizer@eventflow.com / <b>org123</b><br>
                    Attendee: attendee@eventflow.com / <b>att123</b>
                </div>
            </div>

            <p class="auth-link">No account? <a href="/eventflow/register.php">Register here</a></p>
        </div>

    </div>
</div>
</body>
</html>

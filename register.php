<?php
// register.php - Registration Page
require_once 'includes/db.php';
require_once 'includes/functions.php';
startSession();

// Already logged in? Go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: /eventflow/login.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? '');

    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!in_array($role, ['organizer','attendee'])) {
        $error = 'Please select a valid role.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = 'This email is already registered. Please login.';
        } else {
            // Insert new user with MD5 password
            $hashed = md5($password);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hashed, $role);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: /eventflow/login.php?registered=1");
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">

        <div class="auth-logo">
            <span class="icon">🎪</span>
            <h1>Event<span>Flow</span></h1>
            <p>Create your free account</p>
        </div>

        <div class="auth-card">
            <h2>Create Account ✨</h2>

            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= safe($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Your full name"
                           value="<?= safe($_POST['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com"
                           value="<?= safe($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Password (min 6 characters)</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label>Register As</label>
                    <select name="role" required>
                        <option value="">-- Select your role --</option>
                        <option value="organizer" <?= (($_POST['role']??'') === 'organizer') ? 'selected' : '' ?>>
                            🎭 Event Organizer — I want to create events
                        </option>
                        <option value="attendee" <?= (($_POST['role']??'') === 'attendee') ? 'selected' : '' ?>>
                            🎟 Attendee — I want to book tickets
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Create Account →</button>
            </form>

            <p class="auth-link">Already have an account? <a href="/eventflow/login.php">Login here</a></p>
        </div>

    </div>
</div>
</body>
</html>

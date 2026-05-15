<?php
// pages/org_create.php — Create Event
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('organizer');

$active_page = 'org_create.php';
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name']);
    $desc   = trim($_POST['description']);
    $date   = $_POST['date'];
    $time   = $_POST['time'];
    $venue  = trim($_POST['venue']);
    $price  = floatval($_POST['price']);
    $seats  = intval($_POST['total_seats']);
    $org_id = $_SESSION['user_id'];

    if (!$name || !$date || !$time || !$venue || $seats < 1) {
        $error = "Please fill all required fields.";
    } else {
        $sql = "INSERT INTO events (organizer_id,name,description,date,time,venue,price,total_seats,available_seats)
                VALUES ($org_id,'$name','$desc','$date','$time','$venue',$price,$seats,$seats)";
        if (mysqli_query($conn, $sql)) {
            $success = "Event created successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event — EventFlow</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main page-anim">
        <div class="mob-bar">
            <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <strong style="font-family:Syne">Event<span style="color:var(--orange)">Flow</span></strong>
            <div></div>
        </div>

        <div class="page-head">
            <h2>➕ Create New Event</h2>
            <p>Fill in the details to publish your event</p>
        </div>

        <div class="content">
            <?php if($error):   ?><div class="alert alert-error">❌ <?= $error ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success">✅ <?= $success ?> <a href="org_events.php">View My Events →</a></div><?php endif; ?>

            <div class="form-card">
                <form method="POST">
                    <div class="form-group">
                        <label>Event Name *</label>
                        <input type="text" name="name" placeholder="e.g. Tech Summit 2025" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Describe your event..."></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="date" required>
                        </div>
                        <div class="form-group">
                            <label>Time *</label>
                            <input type="time" name="time" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Venue *</label>
                        <input type="text" name="venue" placeholder="e.g. Convention Center, Mumbai" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ticket Price (₹) *</label>
                            <input type="number" name="price" placeholder="0 for FREE" min="0" step="0.01" value="0" required>
                        </div>
                        <div class="form-group">
                            <label>Total Seats *</label>
                            <input type="number" name="total_seats" placeholder="e.g. 200" min="1" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">🚀 Create Event</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>

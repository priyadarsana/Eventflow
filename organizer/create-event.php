<?php
// organizer/create-event.php - Create a new event
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('organizer');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date        = trim($_POST['date'] ?? '');
    $time        = trim($_POST['time'] ?? '');
    $venue       = trim($_POST['venue'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $seats       = intval($_POST['total_seats'] ?? 0);
    $orgId       = $_SESSION['user_id'];

    // Validate
    if (empty($name) || empty($date) || empty($time) || empty($venue)) {
        $error = 'Please fill all required fields.';
    } elseif ($seats < 1) {
        $error = 'Total seats must be at least 1.';
    } elseif ($price < 0) {
        $error = 'Price cannot be negative.';
    } else {
        // Insert into database
        $stmt = mysqli_prepare($conn,
            "INSERT INTO events (organizer_id, name, description, date, time, venue, price, total_seats, available_seats)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'isssssdii',
            $orgId, $name, $description, $date, $time, $venue, $price, $seats, $seats
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = 'Event "' . htmlspecialchars($name) . '" created successfully! 🎉';
        } else {
            $error = 'Failed to create event. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main anim">
        <div class="page-head">
            <h2>➕ Create New Event</h2>
            <p>Fill in all details to publish your event</p>
        </div>
        <div class="content">

            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= safe($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= $success ?></div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST">

                    <div class="form-group">
                        <label>Event Name *</label>
                        <input type="text" name="name" placeholder="e.g. Tech Summit 2025"
                               value="<?= safe($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Describe what this event is about..."><?= safe($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="date"
                                   value="<?= safe($_POST['date'] ?? '') ?>"
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Time *</label>
                            <input type="time" name="time"
                                   value="<?= safe($_POST['time'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Venue *</label>
                        <input type="text" name="venue" placeholder="e.g. Convention Center, Mumbai"
                               value="<?= safe($_POST['venue'] ?? '') ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Ticket Price (₹) *</label>
                            <input type="number" name="price" placeholder="0 for FREE"
                                   min="0" step="1"
                                   value="<?= safe($_POST['price'] ?? '0') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Total Seats *</label>
                            <input type="number" name="total_seats" placeholder="e.g. 200"
                                   min="1"
                                   value="<?= safe($_POST['total_seats'] ?? '') ?>" required>
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

<?php
// attendee/confirmation.php - Show booking success details
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('attendee');

$ref = safe(trim($_GET['ref'] ?? ''));

if (!$ref) {
    header("Location: /eventflow/attendee/events.php");
    exit();
}

// Get full booking details
$stmt = mysqli_prepare($conn,
    "SELECT b.*, e.name AS event_name, e.date, e.time, e.venue, e.price
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     WHERE b.booking_ref = ? AND b.attendee_id = ?"
);
mysqli_stmt_bind_param($stmt, 'si', $ref, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$booking) {
    header("Location: /eventflow/attendee/events.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main anim">
        <div class="page-head">
            <h2>🎉 Booking Confirmed!</h2>
            <p>Your ticket has been booked successfully</p>
        </div>
        <div class="content">
            <div style="max-width:500px">
                <div class="confirm-box">
                    <div class="big-icon">🎉</div>
                    <h3>You're all set!</h3>
                    <p>Save your booking reference for entry</p>

                    <!-- Booking Reference Code -->
                    <div class="ref-code"><?= safe($booking['booking_ref']) ?></div>

                    <!-- Booking details -->
                    <div class="confirm-details">
                        <div class="cd-row">
                            <span class="k">Event</span>
                            <span class="v"><?= safe($booking['event_name']) ?></span>
                        </div>
                        <div class="cd-row">
                            <span class="k">Date & Time</span>
                            <span class="v"><?= niceDate($booking['date']) ?> at <?= niceTime($booking['time']) ?></span>
                        </div>
                        <div class="cd-row">
                            <span class="k">Venue</span>
                            <span class="v"><?= safe($booking['venue']) ?></span>
                        </div>
                        <div class="cd-row">
                            <span class="k">Seats Booked</span>
                            <span class="v"><?= $booking['seats_booked'] ?></span>
                        </div>
                        <div class="cd-row">
                            <span class="k">Amount Paid</span>
                            <span class="v" style="color:var(--orange)"><?= money($booking['total_amount']) ?></span>
                        </div>
                        <div class="cd-row">
                            <span class="k">Status</span>
                            <span class="v"><span class="badge badge-confirmed">✅ Confirmed</span></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display:flex;gap:12px;margin-top:20px">
                    <a href="bookings.php" class="btn btn-primary">📋 View My Bookings</a>
                    <a href="events.php" class="btn btn-outline">← Browse More Events</a>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

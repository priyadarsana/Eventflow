<?php
// pages/att_confirm.php — Booking Confirmation
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('attendee');

if (!isset($_SESSION['last_booking'])) {
    header("Location: att_events.php");
    exit;
}

$b           = $_SESSION['last_booking'];
$active_page = 'att_bookings.php';
unset($_SESSION['last_booking']); // Clear after showing once
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — EventFlow</title>
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
            <h2>🎉 Booking Confirmed!</h2>
            <p>Your ticket is confirmed and ready</p>
        </div>

        <div class="content">
            <div style="max-width:520px">
                <div class="confirm-box">
                    <span class="big-icon">🎊</span>
                    <h3>You're all set!</h3>
                    <p>Show this reference number at the venue entrance</p>

                    <div class="ref-code"><?= htmlspecialchars($b['ref']) ?></div>

                    <div class="detail-list">
                        <div class="detail-row">
                            <span class="k">Event</span>
                            <span class="v"><?= htmlspecialchars($b['event']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="k">Seats Booked</span>
                            <span class="v"><?= $b['seats'] ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="k">Amount Paid</span>
                            <span class="v" style="color:var(--orange)">
                                <?= $b['amount'] > 0 ? '₹'.number_format($b['amount']) : 'FREE' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="margin-top:18px;display:flex;gap:12px">
                    <a href="att_bookings.php" class="btn btn-primary btn-sm" style="flex:1">View My Bookings →</a>
                    <a href="att_events.php"   class="btn btn-outline btn-sm" style="flex:1;text-align:center">Browse More Events</a>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

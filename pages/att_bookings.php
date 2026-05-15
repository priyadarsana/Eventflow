<?php
// pages/att_bookings.php — My Bookings
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('attendee');

$uid         = $_SESSION['user_id'];
$active_page = 'att_bookings.php';

$bookings = mysqli_query($conn,
    "SELECT b.*, e.name as event_name, e.date, e.time, e.venue
     FROM bookings b JOIN events e ON b.event_id=e.id
     WHERE b.attendee_id=$uid ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings — EventFlow</title>
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
            <h2>🎟 My Bookings</h2>
            <p>All your confirmed tickets</p>
        </div>

        <div class="content">
            <?php if(mysqli_num_rows($bookings) === 0): ?>
                <div class="empty">
                    <span class="eicon">🎟</span>
                    <h3>No bookings yet</h3>
                    <p>Browse events and book your first ticket!</p>
                    <br>
                    <a href="att_events.php" class="btn btn-primary btn-sm" style="width:auto">🎪 Browse Events</a>
                </div>
            <?php else: ?>
                <div class="tbox">
                    <div class="tbox-head">
                        <h3>Booking History</h3>
                    </div>
                    <div class="tscroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>Booking Ref</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Venue</th>
                                    <th>Seats</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($b = mysqli_fetch_assoc($bookings)): ?>
                                <tr>
                                    <td class="td-ref"><?= htmlspecialchars($b['booking_ref']) ?></td>
                                    <td class="td-bold"><?= htmlspecialchars($b['event_name']) ?></td>
                                    <td><?= date('d M Y', strtotime($b['date'])) ?></td>
                                    <td><?= htmlspecialchars($b['venue']) ?></td>
                                    <td><?= $b['seats_booked'] ?></td>
                                    <td><?= $b['total_amount'] > 0 ? '₹'.number_format($b['total_amount']) : 'FREE' ?></td>
                                    <td><span class="badge badge-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>

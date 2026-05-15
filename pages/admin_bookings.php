<?php
// pages/admin_bookings.php — Admin: All Bookings
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$active_page = 'admin_bookings.php';

$bookings = mysqli_query($conn,
    "SELECT b.*, e.name as event_name, e.venue,
            u.name as attendee_name, u.email as attendee_email
     FROM bookings b
     JOIN events e ON b.event_id=e.id
     JOIN users  u ON b.attendee_id=u.id
     ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings — EventFlow Admin</title>
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
            <h2>🎟 All Bookings</h2>
            <p>Complete booking registry across all events</p>
        </div>

        <div class="content">
            <div class="tbox">
                <div class="tbox-head">
                    <h3>Bookings Registry</h3>
                    <span style="font-size:13px;color:var(--muted)"><?= mysqli_num_rows($bookings) ?> total bookings</span>
                </div>
                <div class="tscroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Attendee</th>
                                <th>Email</th>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Seats</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($bookings) === 0): ?>
                            <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--muted)">No bookings found.</td></tr>
                        <?php else: while($b = mysqli_fetch_assoc($bookings)): ?>
                            <tr>
                                <td class="td-ref"><?= htmlspecialchars($b['booking_ref']) ?></td>
                                <td class="td-bold"><?= htmlspecialchars($b['attendee_name']) ?></td>
                                <td style="color:var(--muted);font-size:13px"><?= htmlspecialchars($b['attendee_email']) ?></td>
                                <td><?= htmlspecialchars($b['event_name']) ?></td>
                                <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
                                <td><?= $b['seats_booked'] ?></td>
                                <td style="font-weight:600"><?= $b['total_amount']>0 ? '₹'.number_format($b['total_amount']) : 'FREE' ?></td>
                                <td><span class="badge badge-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                            </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

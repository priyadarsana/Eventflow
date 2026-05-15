<?php
// attendee/bookings.php - View all bookings for this attendee
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('attendee');

$attendeeId = $_SESSION['user_id'];

$result = mysqli_query($conn,
    "SELECT b.*, e.name AS event_name, e.date, e.time, e.venue
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     WHERE b.attendee_id = $attendeeId
     ORDER BY b.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main anim">
        <div class="page-head">
            <h2>🎟 My Bookings</h2>
            <p>All your confirmed tickets</p>
        </div>
        <div class="content">
            <div class="table-box">
                <div class="table-top">
                    <h3>Booking History</h3>
                    <span style="color:var(--gray);font-size:14px"><?= mysqli_num_rows($result) ?> bookings</span>
                </div>
                <div class="table-wrap">
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
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr><td colspan="7">
                                <div class="empty">
                                    <span class="eicon">🎟</span>
                                    <h3>No bookings yet</h3>
                                    <p>Browse events and book your first ticket!</p>
                                    <a href="events.php" class="btn btn-primary btn-sm" style="margin-top:14px">🎪 Browse Events</a>
                                </div>
                            </td></tr>
                        <?php else:
                            while ($b = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="td-ref"><?= safe($b['booking_ref']) ?></td>
                                <td class="td-bold"><?= safe($b['event_name']) ?></td>
                                <td><?= niceDate($b['date']) ?></td>
                                <td><?= safe($b['venue']) ?></td>
                                <td><?= $b['seats_booked'] ?></td>
                                <td style="color:var(--green);font-weight:600"><?= money($b['total_amount']) ?></td>
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

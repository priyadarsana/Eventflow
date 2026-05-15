<?php
// admin/bookings.php - Admin view all bookings
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$result = mysqli_query($conn,
    "SELECT b.*, e.name AS event_name, e.venue,
            u.name AS attendee_name, u.email AS attendee_email
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     JOIN users  u ON b.attendee_id = u.id
     ORDER BY b.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Bookings — Admin — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main anim">
        <div class="page-head">
            <h2>🎟 All Bookings</h2>
            <p>Complete booking registry across all events</p>
        </div>
        <div class="content">
            <div class="table-box">
                <div class="table-top">
                    <h3>Bookings Registry</h3>
                    <span style="color:var(--gray);font-size:14px"><?= mysqli_num_rows($result) ?> total bookings</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Attendee</th>
                                <th>Event</th>
                                <th>Booked On</th>
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
                                    <p>Bookings will appear here.</p>
                                </div>
                            </td></tr>
                        <?php else:
                            while ($b = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="td-ref"><?= safe($b['booking_ref']) ?></td>
                                <td>
                                    <div class="td-bold"><?= safe($b['attendee_name']) ?></div>
                                    <div style="font-size:12px;color:var(--muted)"><?= safe($b['attendee_email']) ?></div>
                                </td>
                                <td><?= safe($b['event_name']) ?></td>
                                <td><?= niceDate($b['created_at']) ?></td>
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

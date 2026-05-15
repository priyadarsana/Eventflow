<?php
// pages/admin_events.php — Admin: All Events
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$active_page = 'admin_events.php';

$events = mysqli_query($conn,
    "SELECT e.*, u.name as organizer_name,
            (e.total_seats - e.available_seats) as seats_sold,
            (e.price * (e.total_seats - e.available_seats)) as revenue
     FROM events e JOIN users u ON e.organizer_id=u.id
     ORDER BY e.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events — EventFlow Admin</title>
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
            <h2>📅 All Events</h2>
            <p>Complete list of all platform events</p>
        </div>

        <div class="content">
            <div class="tbox">
                <div class="tbox-head">
                    <h3>Events Registry</h3>
                    <span style="font-size:13px;color:var(--muted)"><?= mysqli_num_rows($events) ?> total events</span>
                </div>
                <div class="tscroll">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Event Name</th>
                                <th>Organizer</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Price</th>
                                <th>Seats Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($events) === 0): ?>
                            <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--muted)">No events found.</td></tr>
                        <?php else: $i=1; while($ev = mysqli_fetch_assoc($events)): ?>
                            <tr>
                                <td style="color:var(--faint)"><?= $i++ ?></td>
                                <td class="td-bold"><?= htmlspecialchars($ev['name']) ?></td>
                                <td><?= htmlspecialchars($ev['organizer_name']) ?></td>
                                <td><?= date('d M Y', strtotime($ev['date'])) ?></td>
                                <td><?= htmlspecialchars($ev['venue']) ?></td>
                                <td><?= $ev['price']==0 ? '<span style="color:var(--success)">FREE</span>' : '₹'.number_format($ev['price']) ?></td>
                                <td>
                                    <span style="color:var(--orange);font-weight:700"><?= $ev['seats_sold'] ?></span>
                                    / <?= $ev['total_seats'] ?>
                                </td>
                                <td style="color:var(--success);font-weight:600">₹<?= number_format($ev['revenue']) ?></td>
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

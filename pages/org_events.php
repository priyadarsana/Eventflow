<?php
// pages/org_events.php — Organizer: My Events
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('organizer');

$uid         = $_SESSION['user_id'];
$active_page = 'org_events.php';

$events = mysqli_query($conn,
    "SELECT *,(total_seats-available_seats) as sold,
            (price*(total_seats-available_seats)) as revenue
     FROM events WHERE organizer_id=$uid ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events — EventFlow</title>
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
            <h2>📅 My Events</h2>
            <p>All events you have created</p>
        </div>

        <div class="content">
            <?php if(mysqli_num_rows($events) === 0): ?>
                <div class="empty">
                    <span class="eicon">📅</span>
                    <h3>No events yet</h3>
                    <p>Create your first event to get started!</p>
                    <br>
                    <a href="org_create.php" class="btn btn-primary btn-sm" style="width:auto">➕ Create Event</a>
                </div>
            <?php else: ?>
                <div class="cards">
                <?php while($ev = mysqli_fetch_assoc($events)):
                    $pct = $ev['total_seats'] > 0 ? round(($ev['sold']/$ev['total_seats'])*100) : 0;
                ?>
                    <div class="card">
                        <div class="card-head">
                            <div class="ev-title"><?= htmlspecialchars($ev['name']) ?></div>
                        </div>
                        <div class="card-body">
                            <div class="ev-meta">
                                <div class="ev-meta-item">📆 <?= date('d M Y', strtotime($ev['date'])) ?> at <?= date('h:i A', strtotime($ev['time'])) ?></div>
                                <div class="ev-meta-item">📍 <?= htmlspecialchars($ev['venue']) ?></div>
                                <div class="ev-meta-item">💺 <?= $ev['sold'] ?> / <?= $ev['total_seats'] ?> seats sold</div>
                                <div class="ev-meta-item">💰 Revenue: ₹<?= number_format($ev['revenue']) ?></div>
                            </div>
                            <div class="seats-bar"><div class="seats-fill" style="width:<?= $pct ?>%"></div></div>
                        </div>
                        <div class="card-foot">
                            <span class="price <?= $ev['price']==0?'free':'' ?>">
                                <?= $ev['price']==0 ? 'FREE' : '₹'.number_format($ev['price']) ?>
                            </span>
                            <span class="badge badge-upcoming">Active</span>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>

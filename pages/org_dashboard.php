<?php
// pages/org_dashboard.php — Organizer Dashboard
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('organizer');

$uid = $_SESSION['user_id'];
$active_page = 'org_dashboard.php';

// Stats
$total_events   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM events WHERE organizer_id=$uid"))['c'];
$tickets_sold   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(b.seats_booked),0) c FROM bookings b JOIN events e ON b.event_id=e.id WHERE e.organizer_id=$uid AND b.status='confirmed'"))['c'];
$revenue        = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(b.total_amount),0) c FROM bookings b JOIN events e ON b.event_id=e.id WHERE e.organizer_id=$uid AND b.status='confirmed'"))['c'];
$upcoming       = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM events WHERE organizer_id=$uid AND date >= CURDATE()"))['c'];

// Recent events
$recent = mysqli_query($conn,"SELECT *,(total_seats-available_seats) as sold FROM events WHERE organizer_id=$uid ORDER BY created_at DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — EventFlow</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main page-anim">
        <!-- Mobile bar -->
        <div class="mob-bar">
            <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <strong style="font-family:Syne">Event<span style="color:var(--orange)">Flow</span></strong>
            <div></div>
        </div>

        <div class="page-head">
            <h2>🏠 Dashboard</h2>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        </div>

        <div class="content">
            <!-- Stats -->
            <div class="stats">
                <div class="stat">
                    <span class="sicon">📅</span>
                    <div class="sval"><?= $total_events ?></div>
                    <div class="slabel">Total Events</div>
                </div>
                <div class="stat">
                    <span class="sicon">🎟</span>
                    <div class="sval"><?= $tickets_sold ?></div>
                    <div class="slabel">Tickets Sold</div>
                </div>
                <div class="stat">
                    <span class="sicon">💰</span>
                    <div class="sval">₹<?= number_format($revenue) ?></div>
                    <div class="slabel">Total Revenue</div>
                </div>
                <div class="stat">
                    <span class="sicon">🔔</span>
                    <div class="sval"><?= $upcoming ?></div>
                    <div class="slabel">Upcoming</div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="sec-title">📋 Recent Events</div>
            <?php if(mysqli_num_rows($recent) === 0): ?>
                <div class="empty">
                    <span class="eicon">📅</span>
                    <h3>No events yet</h3>
                    <p>Create your first event to get started!</p>
                    <br>
                    <a href="org_create.php" class="btn btn-primary btn-sm" style="width:auto">➕ Create Event</a>
                </div>
            <?php else: ?>
                <div class="cards">
                <?php while($ev = mysqli_fetch_assoc($recent)): 
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
                            </div>
                            <div class="seats-bar"><div class="seats-fill" style="width:<?=$pct?>%"></div></div>
                        </div>
                        <div class="card-foot">
                            <span class="price <?= $ev['price']==0?'free':'' ?>">
                                <?= $ev['price']==0 ? 'FREE' : '₹'.number_format($ev['price']) ?>
                            </span>
                            <span class="badge badge-upcoming">Upcoming</span>
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

<?php
// organizer/dashboard.php - Organizer Dashboard
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('organizer');

$orgId = $_SESSION['user_id'];

// Get organizer's events with stats
$result = mysqli_query($conn,
    "SELECT e.*,
            (e.total_seats - e.available_seats) AS seats_sold,
            (e.price * (e.total_seats - e.available_seats)) AS revenue
     FROM events e
     WHERE e.organizer_id = $orgId
     ORDER BY e.created_at DESC"
);
$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = $row;
}

// Calculate totals
$totalEvents  = count($events);
$totalTickets = array_sum(array_column($events, 'seats_sold'));
$totalRevenue = array_sum(array_column($events, 'revenue'));
$upcoming     = count(array_filter($events, fn($e) => $e['date'] >= date('Y-m-d')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Organizer — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main anim">
        <div class="page-head">
            <h2>🏠 Dashboard</h2>
            <p>Overview of your events and performance</p>
        </div>
        <div class="content">
            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <span class="sicon">📅</span>
                    <div class="sval"><?= $totalEvents ?></div>
                    <div class="slabel">My Events</div>
                </div>
                <div class="stat-card">
                    <span class="sicon">🎟</span>
                    <div class="sval"><?= $totalTickets ?></div>
                    <div class="slabel">Tickets Sold</div>
                </div>
                <div class="stat-card">
                    <span class="sicon">💰</span>
                    <div class="sval" style="font-size:20px"><?= money($totalRevenue) ?></div>
                    <div class="slabel">Revenue Earned</div>
                </div>
                <div class="stat-card">
                    <span class="sicon">🔔</span>
                    <div class="sval"><?= $upcoming ?></div>
                    <div class="slabel">Upcoming</div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="sec-title">📋 Recent Events</div>

            <?php if (empty($events)): ?>
                <div class="empty">
                    <span class="eicon">📅</span>
                    <h3>No events yet</h3>
                    <p>Create your first event to get started!</p>
                    <a href="create-event.php" class="btn btn-primary btn-sm" style="margin-top:14px">➕ Create Event</a>
                </div>
            <?php else: ?>
                <div class="cards-grid">
                <?php foreach (array_slice($events, 0, 4) as $e):
                    $pct = $e['total_seats'] > 0 ? round(($e['seats_sold'] / $e['total_seats']) * 100) : 0;
                ?>
                    <div class="ev-card">
                        <div class="ev-card-top">
                            <div class="ev-title"><?= safe($e['name']) ?></div>
                        </div>
                        <div class="ev-card-body">
                            <div class="ev-meta">
                                <div class="ev-meta-row">📆 <?= niceDate($e['date']) ?> at <?= niceTime($e['time']) ?></div>
                                <div class="ev-meta-row">📍 <?= safe($e['venue']) ?></div>
                                <div class="ev-meta-row">💺 <?= $e['available_seats'] ?> / <?= $e['total_seats'] ?> seats left</div>
                                <div class="ev-meta-row">💰 Revenue: <b style="color:var(--green)"><?= money($e['revenue']) ?></b></div>
                            </div>
                            <div class="seats-bar"><div class="seats-fill" style="width:<?= $pct ?>%"></div></div>
                        </div>
                        <div class="ev-card-foot">
                            <?php if ($e['price'] == 0): ?>
                                <span class="price-big free">FREE</span>
                            <?php else: ?>
                                <span class="price-big"><?= money($e['price']) ?></span>
                            <?php endif; ?>
                            <span class="badge badge-upcoming"><?= $e['date'] >= date('Y-m-d') ? 'Upcoming' : 'Past' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>

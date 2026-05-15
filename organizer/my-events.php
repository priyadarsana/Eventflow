<?php
// organizer/my-events.php - View all events created by this organizer
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('organizer');

$orgId = $_SESSION['user_id'];

$result = mysqli_query($conn,
    "SELECT e.*,
            (e.total_seats - e.available_seats) AS seats_sold,
            (e.price * (e.total_seats - e.available_seats)) AS revenue
     FROM events e
     WHERE e.organizer_id = $orgId
     ORDER BY e.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Events — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main anim">
        <div class="page-head">
            <h2>📅 My Events</h2>
            <p>All events you have created</p>
        </div>
        <div class="content">

            <?php if (mysqli_num_rows($result) === 0): ?>
                <div class="empty">
                    <span class="eicon">📅</span>
                    <h3>No events yet</h3>
                    <p>You haven't created any events yet.</p>
                    <a href="create-event.php" class="btn btn-primary btn-sm" style="margin-top:14px">➕ Create Your First Event</a>
                </div>
            <?php else: ?>
                <div class="cards-grid">
                <?php while ($e = mysqli_fetch_assoc($result)):
                    $pct = $e['total_seats'] > 0 ? round(($e['seats_sold'] / $e['total_seats']) * 100) : 0;
                    $isUpcoming = $e['date'] >= date('Y-m-d');
                ?>
                    <div class="ev-card">
                        <div class="ev-card-top">
                            <div class="ev-title"><?= safe($e['name']) ?></div>
                            <?php if ($e['description']): ?>
                                <div style="font-size:13px;color:var(--muted);margin-top:5px;line-height:1.5">
                                    <?= safe(substr($e['description'], 0, 80)) ?>...
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ev-card-body">
                            <div class="ev-meta">
                                <div class="ev-meta-row">📆 <?= niceDate($e['date']) ?> at <?= niceTime($e['time']) ?></div>
                                <div class="ev-meta-row">📍 <?= safe($e['venue']) ?></div>
                                <div class="ev-meta-row">
                                    💺 <b style="color:var(--orange)"><?= $e['seats_sold'] ?></b> sold /
                                       <?= $e['available_seats'] ?> left / <?= $e['total_seats'] ?> total
                                </div>
                                <div class="ev-meta-row">
                                    💰 Revenue: <b style="color:var(--green)"><?= money($e['revenue']) ?></b>
                                </div>
                            </div>
                            <!-- Capacity progress bar -->
                            <div style="display:flex;align-items:center;gap:8px;margin-top:8px">
                                <div class="seats-bar" style="flex:1"><div class="seats-fill" style="width:<?= $pct ?>%"></div></div>
                                <span style="font-size:12px;color:var(--gray)"><?= $pct ?>% full</span>
                            </div>
                        </div>
                        <div class="ev-card-foot">
                            <?php if ($e['price'] == 0): ?>
                                <span class="price-big free">FREE</span>
                            <?php else: ?>
                                <span class="price-big"><?= money($e['price']) ?></span>
                            <?php endif; ?>
                            <span class="badge <?= $isUpcoming ? 'badge-upcoming' : 'badge-confirmed' ?>">
                                <?= $isUpcoming ? 'Upcoming' : 'Completed' ?>
                            </span>
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

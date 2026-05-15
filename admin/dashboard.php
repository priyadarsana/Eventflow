<?php
// admin/dashboard.php - Admin Dashboard
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('admin'); // Only admins can see this

// ── Fetch stats from database ──────────────────────
// Count all events
$totalEvents   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM events"))['c'];

// Count all confirmed bookings
$totalBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE status='confirmed'"))['c'];

// Sum all revenue from confirmed bookings
$totalRevenue  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) as r FROM bookings WHERE status='confirmed'"))['r'];

// Count all users (not admin)
$totalUsers    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role != 'admin'"))['c'];

// Total seats sold
$ticketsSold   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(seats_booked),0) as c FROM bookings WHERE status='confirmed'"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main anim">
        <div class="page-head">
            <h2>📊 Admin Dashboard</h2>
            <p>Platform-wide overview and statistics</p>
        </div>

        <div class="content">
            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <span class="sicon">📅</span>
                    <div class="sval"><?= $totalEvents ?></div>
                    <div class="slabel">Total Events</div>
                </div>
                <div class="stat-card">
                    <span class="sicon">🎟</span>
                    <div class="sval"><?= $totalBookings ?></div>
                    <div class="slabel">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <span class="sicon">🎫</span>
                    <div class="sval"><?= $ticketsSold ?></div>
                    <div class="slabel">Tickets Sold</div>
                </div>
                <div class="stat-card">
                    <span class="sicon">💰</span>
                    <div class="sval" style="font-size:20px"><?= money($totalRevenue) ?></div>
                    <div class="slabel">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <span class="sicon">👥</span>
                    <div class="sval"><?= $totalUsers ?></div>
                    <div class="slabel">Registered Users</div>
                </div>
            </div>

            <!-- Quick info box -->
            <div class="table-box" style="padding:24px">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px">
                    <div>
                        <div style="font-size:13px;color:var(--gray);margin-bottom:6px">Platform Health</div>
                        <div style="font-size:15px;font-weight:600;color:var(--green)">✅ All Systems Running</div>
                    </div>
                    <div>
                        <div style="font-size:13px;color:var(--gray);margin-bottom:6px">Database</div>
                        <div style="font-size:15px;font-weight:600;color:var(--green)">✅ MySQL Connected</div>
                    </div>
                    <div>
                        <div style="font-size:13px;color:var(--gray);margin-bottom:6px">Quick Actions</div>
                        <a href="events.php" class="btn btn-outline btn-sm">View All Events</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

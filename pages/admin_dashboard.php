<?php
// pages/admin_dashboard.php — Admin Dashboard
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$active_page = 'admin_dashboard.php';

// Platform stats
$total_events   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM events"))['c'];
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM bookings WHERE status='confirmed'"))['c'];
$tickets_sold   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(seats_booked),0) c FROM bookings WHERE status='confirmed'"))['c'];
$total_revenue  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(total_amount),0) c FROM bookings WHERE status='confirmed'"))['c'];
$total_users    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users WHERE role != 'admin'"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — EventFlow</title>
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
            <h2>📊 Admin Dashboard</h2>
            <p>Platform-wide overview and statistics</p>
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
                    <div class="sval"><?= $total_bookings ?></div>
                    <div class="slabel">Total Bookings</div>
                </div>
                <div class="stat">
                    <span class="sicon">🎫</span>
                    <div class="sval"><?= $tickets_sold ?></div>
                    <div class="slabel">Tickets Sold</div>
                </div>
                <div class="stat">
                    <span class="sicon">💰</span>
                    <div class="sval">₹<?= number_format($total_revenue) ?></div>
                    <div class="slabel">Total Revenue</div>
                </div>
                <div class="stat">
                    <span class="sicon">👥</span>
                    <div class="sval"><?= $total_users ?></div>
                    <div class="slabel">Registered Users</div>
                </div>
            </div>

            <!-- Quick links -->
            <div class="tbox" style="margin-top:10px">
                <div class="tbox-head"><h3>🔍 Quick Overview</h3></div>
                <div style="padding:22px;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px">
                    <div>
                        <div style="font-size:12px;color:var(--muted);margin-bottom:6px">Platform Status</div>
                        <div style="font-size:15px;font-weight:600;color:var(--success)">✅ All Systems Running</div>
                    </div>
                    <div>
                        <div style="font-size:12px;color:var(--muted);margin-bottom:6px">Database</div>
                        <div style="font-size:15px;font-weight:600;color:var(--success)">✅ MySQL Connected</div>
                    </div>
                    <div>
                        <div style="font-size:12px;color:var(--muted);margin-bottom:6px">Quick Actions</div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">
                            <a href="admin_events.php"   class="btn btn-outline btn-sm">View Events</a>
                            <a href="admin_bookings.php" class="btn btn-outline btn-sm">View Bookings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

<?php
// admin/events.php - Admin view all events
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('admin');

// Get all events with organizer name and seats sold
$search = safe(trim($_GET['search'] ?? ''));
$sql = "SELECT e.*, u.name AS organizer_name,
               (e.total_seats - e.available_seats) AS seats_sold,
               (e.price * (e.total_seats - e.available_seats)) AS revenue
        FROM events e
        JOIN users u ON e.organizer_id = u.id";

if ($search) {
    $sql .= " WHERE e.name LIKE '%$search%' OR e.venue LIKE '%$search%'";
}
$sql .= " ORDER BY e.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Events — Admin — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main anim">
        <div class="page-head">
            <h2>📅 All Events</h2>
            <p>Complete list of all events on the platform</p>
        </div>
        <div class="content">
            <div class="table-box">
                <div class="table-top">
                    <h3>Events Registry</h3>
                    <!-- Search form -->
                    <form method="GET" style="display:flex;gap:10px;align-items:center">
                        <div class="search-wrap">
                            <span>🔍</span>
                            <input type="text" name="search" placeholder="Search events..."
                                   value="<?= $search ?>">
                        </div>
                        <button type="submit" class="btn btn-outline btn-sm">Search</button>
                        <?php if ($search): ?>
                            <a href="events.php" class="btn btn-outline btn-sm">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="table-wrap">
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
                        <?php
                        $i = 1;
                        $count = mysqli_num_rows($result);
                        if ($count === 0): ?>
                            <tr><td colspan="8">
                                <div class="empty">
                                    <span class="eicon">📅</span>
                                    <h3>No events found</h3>
                                    <p>Try a different search term.</p>
                                </div>
                            </td></tr>
                        <?php else:
                            while ($e = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td style="color:var(--muted)"><?= $i++ ?></td>
                                <td class="td-bold"><?= safe($e['name']) ?></td>
                                <td><?= safe($e['organizer_name']) ?></td>
                                <td><?= niceDate($e['date']) ?></td>
                                <td><?= safe($e['venue']) ?></td>
                                <td><?= money($e['price']) ?></td>
                                <td>
                                    <span style="color:var(--orange);font-weight:600"><?= $e['seats_sold'] ?></span>
                                    / <?= $e['total_seats'] ?>
                                </td>
                                <td style="color:var(--green);font-weight:600"><?= money($e['revenue']) ?></td>
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

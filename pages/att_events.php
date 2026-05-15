<?php
// pages/att_events.php — Attendee: Browse Events
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('attendee');

$active_page = 'att_events.php';
$search      = trim($_GET['search'] ?? '');

// Build query with optional search
$where = "WHERE e.date >= CURDATE()";
if ($search) {
    $s     = mysqli_real_escape_string($conn, $search);
    $where .= " AND (e.name LIKE '%$s%' OR e.venue LIKE '%$s%')";
}

$events = mysqli_query($conn,
    "SELECT e.*, u.name as organizer_name
     FROM events e JOIN users u ON e.organizer_id=u.id
     $where ORDER BY e.date ASC");

$success = $_GET['booked'] ?? '';
$error   = $_GET['err']    ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events — EventFlow</title>
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
            <h2>🎪 Browse Events</h2>
            <p>Discover and book amazing events</p>
        </div>

        <div class="content">
            <?php if($success): ?><div class="alert alert-success">🎉 <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if($error):   ?><div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

            <!-- Search -->
            <form method="GET" style="margin-bottom:22px">
                <div class="search-wrap" style="display:inline-flex">
                    <span>🔍</span>
                    <input type="text" name="search" placeholder="Search events by name or venue..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="padding:4px 12px">Search</button>
                    <?php if($search): ?><a href="att_events.php" style="color:var(--muted);font-size:13px;text-decoration:none;margin-left:4px">✕ Clear</a><?php endif; ?>
                </div>
            </form>

            <?php if(mysqli_num_rows($events) === 0): ?>
                <div class="empty">
                    <span class="eicon">🔍</span>
                    <h3>No events found</h3>
                    <p><?= $search ? "Try a different search term." : "No upcoming events right now." ?></p>
                </div>
            <?php else: ?>
                <div class="cards">
                <?php while($ev = mysqli_fetch_assoc($events)):
                    $pct      = $ev['total_seats'] > 0 ? round((($ev['total_seats']-$ev['available_seats'])/$ev['total_seats'])*100) : 0;
                    $can_book = $ev['available_seats'] > 0;
                ?>
                    <div class="card">
                        <div class="card-head">
                            <div class="ev-title"><?= htmlspecialchars($ev['name']) ?></div>
                            <div class="ev-by">by <?= htmlspecialchars($ev['organizer_name']) ?></div>
                        </div>
                        <div class="card-body">
                            <div class="ev-meta">
                                <div class="ev-meta-item">📆 <?= date('d M Y', strtotime($ev['date'])) ?> at <?= date('h:i A', strtotime($ev['time'])) ?></div>
                                <div class="ev-meta-item">📍 <?= htmlspecialchars($ev['venue']) ?></div>
                                <div class="ev-meta-item">💺 <?= $ev['available_seats'] ?> of <?= $ev['total_seats'] ?> seats left</div>
                            </div>
                            <div class="seats-bar"><div class="seats-fill" style="width:<?= $pct ?>%"></div></div>
                        </div>
                        <div class="card-foot">
                            <span class="price <?= $ev['price']==0?'free':'' ?>">
                                <?= $ev['price']==0 ? 'FREE' : '₹'.number_format($ev['price']) ?>
                            </span>
                            <?php if($can_book): ?>
                                <!-- Small inline form to trigger booking -->
                                <button class="btn btn-primary btn-sm"
                                    onclick="openBook(<?= $ev['id'] ?>, '<?= addslashes($ev['name']) ?>', '<?= date('d M Y', strtotime($ev['date'])) ?>', '<?= htmlspecialchars($ev['venue']) ?>', <?= $ev['price'] ?>, <?= $ev['available_seats'] ?>)">
                                    🎟 Book Now
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline btn-sm" disabled style="opacity:.5">Sold Out</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- ── BOOKING MODAL ─────────────────────── -->
<div id="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--card);border:1px solid var(--border);border-radius:16px;width:100%;max-width:480px;animation:fadeUp .3s ease">

        <div style="padding:22px 26px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-family:Syne;font-size:19px;font-weight:700">🎟 Book Ticket</h3>
            <button onclick="closeModal()" style="background:var(--input);border:1px solid var(--border);border-radius:7px;width:30px;height:30px;cursor:pointer;color:var(--muted);font-size:15px">✕</button>
        </div>

        <div style="padding:24px 26px">
            <!-- Event summary -->
            <div style="background:var(--input);border-radius:8px;padding:16px;margin-bottom:18px">
                <div class="detail-row"><span class="k">Event</span>  <span class="v" id="m-name">—</span></div>
                <div class="detail-row"><span class="k">Date</span>   <span class="v" id="m-date">—</span></div>
                <div class="detail-row"><span class="k">Venue</span>  <span class="v" id="m-venue">—</span></div>
                <div class="detail-row"><span class="k">Price</span>  <span class="v" style="color:var(--orange)" id="m-price">—</span></div>
                <div class="detail-row" style="border:none"><span class="k">Available</span><span class="v" id="m-avail">—</span></div>
            </div>

            <form method="POST" action="att_book.php">
                <input type="hidden" name="event_id" id="m-event-id">
                <div class="form-group">
                    <label>Number of Seats</label>
                    <input type="number" name="seats" id="m-seats" min="1" max="10" value="1" onchange="updateTotal()">
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;background:var(--input);padding:14px 16px;border-radius:8px;margin-bottom:18px">
                    <span style="color:var(--muted);font-size:14px">Total Amount</span>
                    <span style="font-family:Syne;font-size:22px;font-weight:700;color:var(--orange)" id="m-total">₹0</span>
                </div>
                <button type="submit" class="btn btn-primary">✅ Confirm Booking</button>
            </form>
        </div>

    </div>
</div>

<script>
let currentPrice = 0;

function openBook(id, name, date, venue, price, avail) {
    currentPrice = price;
    document.getElementById('m-event-id').value = id;
    document.getElementById('m-name').textContent  = name;
    document.getElementById('m-date').textContent  = date;
    document.getElementById('m-venue').textContent = venue;
    document.getElementById('m-price').textContent = price > 0 ? '₹' + price.toLocaleString('en-IN') + ' / seat' : 'FREE';
    document.getElementById('m-avail').textContent = avail + ' seats';
    document.getElementById('m-seats').max         = avail;
    document.getElementById('m-seats').value       = 1;
    updateTotal();
    document.getElementById('modal-overlay').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modal-overlay').style.display = 'none';
}

function updateTotal() {
    const seats = parseInt(document.getElementById('m-seats').value) || 1;
    const total = currentPrice * seats;
    document.getElementById('m-total').textContent = total > 0 ? '₹' + total.toLocaleString('en-IN') : 'FREE';
}

// Close on background click
document.getElementById('modal-overlay').addEventListener('click', function(e){
    if(e.target === this) closeModal();
});
</script>
</body>
</html>

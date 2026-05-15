<?php
// attendee/events.php - Browse and Book Events
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('attendee');

// Search filter
$search = safe(trim($_GET['search'] ?? ''));
$sql = "SELECT e.*, u.name AS organizer_name
        FROM events e
        JOIN users u ON e.organizer_id = u.id
        WHERE e.date >= CURDATE()";
if ($search) {
    $sql .= " AND (e.name LIKE '%$search%' OR e.venue LIKE '%$search%')";
}
$sql .= " ORDER BY e.date ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Events — EventFlow</title>
    <link rel="stylesheet" href="/eventflow/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main anim">
        <div class="page-head">
            <h2>🎪 Browse Events</h2>
            <p>Discover and book amazing events near you</p>
        </div>
        <div class="content">

            <!-- Search Bar -->
            <form method="GET" style="margin-bottom:22px">
                <div style="display:flex;gap:10px;align-items:center">
                    <div class="search-wrap">
                        <span>🔍</span>
                        <input type="text" name="search"
                               placeholder="Search by event name or venue..."
                               value="<?= $search ?>">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Search</button>
                    <?php if ($search): ?>
                        <a href="events.php" class="btn btn-outline btn-sm">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if (isset($_GET['booked'])): ?>
                <div class="alert alert-success">
                    🎉 Booking confirmed! Reference: <b><?= safe($_GET['booked']) ?></b>
                    — <a href="bookings.php" style="color:var(--green)">View My Bookings</a>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">❌ <?= safe($_GET['error']) ?></div>
            <?php endif; ?>

            <!-- Event Cards -->
            <?php if (mysqli_num_rows($result) === 0): ?>
                <div class="empty">
                    <span class="eicon">🎪</span>
                    <h3>No events found</h3>
                    <p><?= $search ? 'Try a different search.' : 'No upcoming events available.' ?></p>
                </div>
            <?php else: ?>
                <div class="cards-grid">
                <?php while ($e = mysqli_fetch_assoc($result)):
                    $pct = $e['total_seats'] > 0 ? round((($e['total_seats'] - $e['available_seats']) / $e['total_seats']) * 100) : 0;
                    $soldOut = $e['available_seats'] <= 0;
                ?>
                    <div class="ev-card">
                        <div class="ev-card-top">
                            <div class="ev-title"><?= safe($e['name']) ?></div>
                            <div class="ev-by">by <?= safe($e['organizer_name']) ?></div>
                        </div>
                        <div class="ev-card-body">
                            <div class="ev-meta">
                                <div class="ev-meta-row">📆 <?= niceDate($e['date']) ?> at <?= niceTime($e['time']) ?></div>
                                <div class="ev-meta-row">📍 <?= safe($e['venue']) ?></div>
                                <div class="ev-meta-row">💺 <?= $e['available_seats'] ?> of <?= $e['total_seats'] ?> seats left</div>
                            </div>
                            <div class="seats-bar"><div class="seats-fill" style="width:<?= $pct ?>%"></div></div>
                        </div>
                        <div class="ev-card-foot">
                            <?php if ($e['price'] == 0): ?>
                                <span class="price-big free">FREE</span>
                            <?php else: ?>
                                <span class="price-big"><?= money($e['price']) ?></span>
                            <?php endif; ?>

                            <?php if ($soldOut): ?>
                                <button class="btn btn-sm" style="background:var(--input);color:var(--muted);cursor:not-allowed" disabled>
                                    Sold Out
                                </button>
                            <?php else: ?>
                                <!-- Button opens the booking form below -->
                                <button class="btn btn-primary btn-sm"
                                        onclick="openBook(<?= $e['id'] ?>, '<?= addslashes(safe($e['name'])) ?>', <?= $e['price'] ?>, <?= $e['available_seats'] ?>)">
                                    🎟 Book Now
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- ══════════════════════════════
     BOOKING MODAL (popup)
══════════════════════════════ -->
<div id="book-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--card);border:1px solid var(--border);border-radius:16px;width:100%;max-width:460px;animation:fadeUp .3s ease">

        <!-- Modal Header -->
        <div style="padding:22px 26px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <h3 style="font-family:Syne,sans-serif;font-size:19px;font-weight:700">🎟 Book Ticket</h3>
            <button onclick="closeBook()" style="background:var(--input);border:1px solid var(--border);border-radius:7px;color:var(--gray);width:30px;height:30px;cursor:pointer;font-size:15px">✕</button>
        </div>

        <!-- Modal Body -->
        <div style="padding:26px">
            <!-- Event info display -->
            <div style="background:var(--input);border-radius:8px;padding:16px;margin-bottom:18px">
                <div style="font-family:Syne,sans-serif;font-size:17px;font-weight:700;margin-bottom:10px" id="m-name">—</div>
                <div style="font-size:13px;color:var(--gray)">
                    Price per seat: <b style="color:var(--orange)" id="m-price">—</b><br>
                    Available seats: <b id="m-avail">—</b>
                </div>
            </div>

            <!-- Booking form -->
            <form method="POST" action="book.php">
                <input type="hidden" name="event_id" id="m-event-id">

                <div class="form-group">
                    <label>Number of Seats</label>
                    <input type="number" name="seats" id="m-seats" min="1" max="10" value="1"
                           oninput="updateTotal()" required>
                </div>

                <!-- Total calculation -->
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:var(--input);border-radius:8px;margin-bottom:18px">
                    <span style="color:var(--gray);font-size:14px">Total Amount</span>
                    <span style="font-family:Syne,sans-serif;font-size:22px;font-weight:700;color:var(--orange)" id="m-total">₹0</span>
                </div>

                <div style="display:flex;gap:10px">
                    <button type="button" onclick="closeBook()" class="btn btn-outline" style="flex:1">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:2">✅ Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Store price for total calculation
var currentPrice = 0;

// Open booking modal and fill event details
function openBook(id, name, price, avail) {
    currentPrice = price;
    document.getElementById('m-event-id').value = id;
    document.getElementById('m-name').textContent = name;
    document.getElementById('m-price').textContent = price > 0 ? '₹' + price.toLocaleString('en-IN') : 'FREE';
    document.getElementById('m-avail').textContent = avail;
    document.getElementById('m-seats').max = Math.min(avail, 10);
    document.getElementById('m-seats').value = 1;
    updateTotal();
    document.getElementById('book-modal').style.display = 'flex';
}

// Close booking modal
function closeBook() {
    document.getElementById('book-modal').style.display = 'none';
}

// Calculate and show total price
function updateTotal() {
    var seats = parseInt(document.getElementById('m-seats').value) || 1;
    var total = currentPrice * seats;
    document.getElementById('m-total').textContent = total > 0 ? '₹' + total.toLocaleString('en-IN') : 'FREE';
}

// Close modal if clicked outside
document.getElementById('book-modal').addEventListener('click', function(e) {
    if (e.target === this) closeBook();
});
</script>
</body>
</html>

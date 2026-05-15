<?php
// includes/sidebar.php - Left Sidebar Navigation
$user = getUser();
$navItems = [];
if ($user['role'] === 'admin') {
    $navItems = [
        ['/eventflow/admin/dashboard.php',  '📊', 'Dashboard'],
        ['/eventflow/admin/events.php',     '📅', 'All Events'],
        ['/eventflow/admin/bookings.php',   '🎟', 'All Bookings'],
    ];
} elseif ($user['role'] === 'organizer') {
    $navItems = [
        ['/eventflow/organizer/dashboard.php',    '🏠', 'Dashboard'],
        ['/eventflow/organizer/create-event.php', '➕', 'Create Event'],
        ['/eventflow/organizer/my-events.php',    '📅', 'My Events'],
    ];
} else {
    $navItems = [
        ['/eventflow/attendee/events.php',   '🎪', 'Browse Events'],
        ['/eventflow/attendee/bookings.php', '🎟', 'My Bookings'],
    ];
}
$cur = $_SERVER['REQUEST_URI'];
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="brand">Event<span>Flow</span></div>
        <div class="sub">Ticketing Platform</div>
    </div>
    <div class="sidebar-user">
        <div class="avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
        <div>
            <div class="user-name"><?= safe($user['name']) ?></div>
            <span class="role-badge <?= $user['role'] ?>"><?= $user['role'] ?></span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Navigation</div>
        <?php foreach($navItems as [$url,$icon,$label]): ?>
        <a href="<?= $url ?>" class="nav-item <?= strpos($cur, basename($url,'.php')) !== false ? 'active' : '' ?>">
            <span class="ni"><?= $icon ?></span><?= $label ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-foot">
        <a href="/eventflow/logout.php" class="logout-link"><span>🚪</span> Sign Out</a>
    </div>
</aside>

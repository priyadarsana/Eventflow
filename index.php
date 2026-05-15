<?php
require_once 'includes/functions.php';
startSession();
if (!isset($_SESSION['user_id'])) {
    header("Location: /eventflow/login.php");
} else {
    $r = $_SESSION['user_role'];
    if ($r === 'admin')     header("Location: /eventflow/admin/dashboard.php");
    elseif ($r === 'organizer') header("Location: /eventflow/organizer/dashboard.php");
    else header("Location: /eventflow/attendee/events.php");
}
exit();

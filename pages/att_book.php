<?php
// pages/att_book.php — Process Booking (POST only)
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('attendee');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: att_events.php");
    exit;
}

$attendee_id = $_SESSION['user_id'];
$event_id    = intval($_POST['event_id']);
$seats       = intval($_POST['seats']);

if ($seats < 1 || $seats > 10) {
    header("Location: att_events.php?err=Please+book+between+1+and+10+seats.");
    exit;
}

// Get event
$ev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id=$event_id"));
if (!$ev) {
    header("Location: att_events.php?err=Event+not+found.");
    exit;
}

// Check seats available
if ($ev['available_seats'] < $seats) {
    header("Location: att_events.php?err=Only+{$ev['available_seats']}+seats+available.");
    exit;
}

// Check duplicate booking
$dup = mysqli_query($conn,"SELECT id FROM bookings WHERE attendee_id=$attendee_id AND event_id=$event_id AND status='confirmed'");
if (mysqli_num_rows($dup) > 0) {
    header("Location: att_events.php?err=You+already+booked+this+event.");
    exit;
}

// Generate booking reference
$ref    = 'EF-' . strtoupper(substr(md5(uniqid()), 0, 8));
$amount = $ev['price'] * $seats;

// Insert booking
mysqli_query($conn,"INSERT INTO bookings (attendee_id,event_id,seats_booked,total_amount,booking_ref)
                    VALUES ($attendee_id,$event_id,$seats,$amount,'$ref')");

// Reduce available seats
mysqli_query($conn,"UPDATE events SET available_seats=available_seats-$seats WHERE id=$event_id");

// Store confirmation in session and redirect
$_SESSION['last_booking'] = [
    'ref'    => $ref,
    'event'  => $ev['name'],
    'seats'  => $seats,
    'amount' => $amount,
];
header("Location: att_confirm.php");
exit;
?>

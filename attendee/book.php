<?php
// attendee/book.php - Handles the actual booking when form is submitted
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireRole('attendee');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /eventflow/attendee/events.php");
    exit();
}

$eventId    = intval($_POST['event_id'] ?? 0);
$seats      = intval($_POST['seats'] ?? 1);
$attendeeId = $_SESSION['user_id'];

// Validate seats
if ($seats < 1 || $seats > 10) {
    header("Location: /eventflow/attendee/events.php?error=" . urlencode("Please book between 1 and 10 seats."));
    exit();
}

// Get event from database
$stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $eventId);
mysqli_stmt_execute($stmt);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$event) {
    header("Location: /eventflow/attendee/events.php?error=" . urlencode("Event not found."));
    exit();
}

// Check if enough seats are available
if ($event['available_seats'] < $seats) {
    header("Location: /eventflow/attendee/events.php?error=" . urlencode("Only {$event['available_seats']} seats available."));
    exit();
}

// Check if user already booked this event
$check = mysqli_prepare($conn, "SELECT id FROM bookings WHERE attendee_id = ? AND event_id = ? AND status = 'confirmed'");
mysqli_stmt_bind_param($check, 'ii', $attendeeId, $eventId);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) {
    header("Location: /eventflow/attendee/events.php?error=" . urlencode("You have already booked this event."));
    exit();
}

// Calculate total amount
$totalAmount = $event['price'] * $seats;

// Generate unique booking reference
$ref = '';
do {
    $ref = generateRef();
    $refCheck = mysqli_prepare($conn, "SELECT id FROM bookings WHERE booking_ref = ?");
    mysqli_stmt_bind_param($refCheck, 's', $ref);
    mysqli_stmt_execute($refCheck);
    mysqli_stmt_store_result($refCheck);
} while (mysqli_stmt_num_rows($refCheck) > 0); // Regenerate if ref already exists

// Begin transaction - ensures both queries succeed or both fail
mysqli_begin_transaction($conn);
try {
    // 1. Insert booking record
    $insert = mysqli_prepare($conn,
        "INSERT INTO bookings (attendee_id, event_id, seats_booked, total_amount, booking_ref)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($insert, 'iiids', $attendeeId, $eventId, $seats, $totalAmount, $ref);
    mysqli_stmt_execute($insert);

    // 2. Reduce available seats in events table
    $update = mysqli_prepare($conn, "UPDATE events SET available_seats = available_seats - ? WHERE id = ?");
    mysqli_stmt_bind_param($update, 'ii', $seats, $eventId);
    mysqli_stmt_execute($update);

    // Commit both changes
    mysqli_commit($conn);

    // Redirect to confirmation page
    header("Location: /eventflow/attendee/confirmation.php?ref=" . urlencode($ref));
    exit();

} catch (Exception $ex) {
    // If anything fails, undo all changes
    mysqli_rollback($conn);
    header("Location: /eventflow/attendee/events.php?error=" . urlencode("Booking failed. Please try again."));
    exit();
}
?>

<?php
// includes/functions.php
// Helper functions used across the whole project

// Always call this at the top of every page
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in - if not, send to login page
function requireLogin() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        header("Location: /eventflow/login.php");
        exit();
    }
}

// Check if user has the right role
function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header("Location: /eventflow/login.php?error=access_denied");
        exit();
    }
}

// Get logged in user's info
function getUser() {
    startSession();
    if (isset($_SESSION['user_id'])) {
        return [
            'id'   => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role'],
            'email'=> $_SESSION['user_email']
        ];
    }
    return null;
}

// Make text safe to show in HTML (prevents hacking)
function safe($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Format date nicely: 2025-08-15 → 15 Aug 2025
function niceDate($date) {
    return date("d M Y", strtotime($date));
}

// Format time nicely: 09:00:00 → 9:00 AM
function niceTime($time) {
    return date("g:i A", strtotime($time));
}

// Format money: 1500 → ₹1,500
function money($amount) {
    if ($amount == 0) return "FREE";
    return "₹" . number_format($amount, 0, '.', ',');
}

// Generate unique booking reference like EF-2025-X7K9
function generateRef() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $rand = '';
    for ($i = 0; $i < 6; $i++) {
        $rand .= $chars[rand(0, strlen($chars) - 1)];
    }
    return 'EF-' . date('Y') . '-' . $rand;
}
?>

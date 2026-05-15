<?php
// includes/auth.php — Login session helpers

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser() {
    return [
        'id'   => $_SESSION['user_id']   ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
    ];
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /eventflow/index.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header("Location: /eventflow/index.php?error=access_denied");
        exit;
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>

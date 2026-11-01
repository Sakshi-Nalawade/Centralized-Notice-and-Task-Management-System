<?php
/**
 * Employee-Only Authentication Guard
 * ONLY employees can access this module.
 * Admins are redirected to their own dashboard.
 * Unauthenticated users are redirected to login page.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Not logged in at all → go to login
if (!isset($_SESSION['userid']) || !isset($_SESSION['role'])) {
    header('location:index.php');
    exit();
}

$role = trim(strtolower($_SESSION['role']));

// Admin trying to access employee IT Assets → redirect to admin dashboard
if ($role === 'admin') {
    header('location:manage-circulars.php');
    exit();
}

// Only employee role is allowed
if ($role !== 'employee') {
    header('location:index.php');
    exit();
}

// Ensure branch_name is set in session
if (!isset($_SESSION['branch_name']) || empty($_SESSION['branch_name'])) {
    header('location:index.php');
    exit();
}
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once(__DIR__ . '/security.php');

if (!isset($_SESSION['userid'])) {
    header('location:index.php');
    exit();
}

if (!isset($_SESSION['role'])) {
    header('location:index.php');
    exit();
}

$role = trim(strtolower($_SESSION['role']));

if ($role !== 'admin' && $role !== 'employee') {
    header('location:index.php');
    exit();
}
?>
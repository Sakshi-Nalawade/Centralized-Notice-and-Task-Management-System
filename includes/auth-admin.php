<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once(__DIR__ . '/security.php');

if (
    !isset($_SESSION['userid']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('location:index.php');
    exit();
}
?>
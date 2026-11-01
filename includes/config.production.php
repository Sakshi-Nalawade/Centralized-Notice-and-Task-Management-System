<?php
/**
 * Database Configuration — Production
 * 
 * INSTRUCTIONS:
 * 1. Copy this file as 'config.php' in the includes/ directory
 * 2. Replace the values below with your hosting provider's database credentials
 * 3. You'll find these credentials in your hosting control panel (cPanel/Plesk)
 */

// Database credentials — CHANGE THESE to your hosting values
$db_host = "localhost";          // Usually "localhost" on shared hosting
$db_user = "your_db_username";   // e.g., "u123456789_admin"
$db_pass = "your_db_password";   // Your database password
$db_name = "your_db_name";       // e.g., "u123456789_demo_project"

$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$con) {
    // In production, don't expose error details
    die("Database connection failed. Please contact the administrator.");
}

mysqli_set_charset($con, "utf8mb4");
?>

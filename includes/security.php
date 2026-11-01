<?php
/**
 * Security Helper — CSRF Token Protection
 * Include this file after session_start() in any page that needs CSRF protection.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF token and store it in the session.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field for use in forms.
 */
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Validate the submitted CSRF token against the session token.
 * Returns true if valid, false otherwise.
 */
function csrf_verify() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Set secure HTTP headers for all pages.
 */
function set_security_headers() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    // Enable XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Automatically set security headers when this file is included
set_security_headers();
?>

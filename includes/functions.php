<?php
// Common functions for the CRM system

// Start session if not already started
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    startSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect to login if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format date for display
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Format datetime for display
function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

// Get status badge class for Bootstrap
function getStatusBadgeClass($status) {
    switch($status) {
        case 'new':
        case 'pending':
            return 'badge-primary';
        case 'contacted':
        case 'in_progress':
            return 'badge-warning';
        case 'qualified':
        case 'completed':
            return 'badge-success';
        case 'converted':
            return 'badge-info';
        case 'lost':
        case 'cancelled':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}

// Get priority badge class
function getPriorityBadgeClass($priority) {
    switch($priority) {
        case 'high':
            return 'badge-danger';
        case 'medium':
            return 'badge-warning';
        case 'low':
            return 'badge-success';
        default:
            return 'badge-secondary';
    }
}

// Generate random password
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

// Send email (basic implementation - can be enhanced with PHPMailer)
function sendEmail($to, $subject, $message) {
    $headers = "From: noreply@crm.com\r\n";
    $headers .= "Reply-To: noreply@crm.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Log activity (basic implementation)
function logActivity($user_id, $action, $details = '') {
    // This could be enhanced to log to database or file
    error_log("User $user_id: $action - $details");
}

// Pagination helper
function getPaginationData($total_records, $records_per_page, $current_page) {
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'records_per_page' => $records_per_page,
        'total_records' => $total_records
    ];
}
?>

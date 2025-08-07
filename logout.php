<?php
require_once 'includes/functions.php';

startSession();

if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'Logout', 'User logged out');
}

// Destroy session
session_destroy();

// Redirect to login
header('Location: login.php');
exit();
?>

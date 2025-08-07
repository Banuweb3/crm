<?php
require_once 'includes/functions.php';

startSession();

// Redirect to dashboard if logged in, otherwise to login
if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
?>

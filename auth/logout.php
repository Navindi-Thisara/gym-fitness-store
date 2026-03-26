<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
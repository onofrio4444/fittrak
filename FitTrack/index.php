<?php
require_once 'includes/config.php';

if (is_logged_in()) {
    header("Location: pages/dashboard.php");
    exit();
} else {
    header("Location: auth/login.php");
    exit();
}
?>
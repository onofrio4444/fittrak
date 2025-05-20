<?php
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit();
} else {
    header('Location: auth/login.php');
    exit();
}
?>
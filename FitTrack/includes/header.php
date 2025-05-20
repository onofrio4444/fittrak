<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - <?php echo $page_title ?? 'Monitora le tue attività'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>FitTrack</h1>
        </div>
        <nav class="user-menu">
            <span>Ciao, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
            <div class="dropdown">
                <button class="dropbtn">
                    <i class="fas fa-user-circle"></i>
                </button>
                <div class="dropdown-content">
                    <a href="../pages/profile.php"><i class="fas fa-user"></i> Profilo</a>
                    <a href="../pages/settings.php"><i class="fas fa-cog"></i> Impostazioni</a>
                    <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </nav>
    </header>
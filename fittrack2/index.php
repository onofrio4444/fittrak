<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Benvenuto su FitTrack</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Benvenuto su <span style="color:#1e88e5;">FitTrack</span></h1>
    <p>Traccia e analizza le tue attività fisiche con semplicità.</p>
    <a href="login.php" class="btn">Login</a>
    <a href="registrazione.php" class="btn">Registrati</a>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
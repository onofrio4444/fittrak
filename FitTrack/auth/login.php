<?php
require_once '../includes/config.php';

if (isLoggedIn()) {
    header("Location: ../pages/dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = 'Inserisci username e password';
    } elseif (loginUser($username, $password)) {
        header("Location: ../pages/dashboard.php");
        exit();
    } else {
        $error = 'Credenziali non valide';
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h1>FitTrack</h1>
        <form method="POST" action="login.php">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Accedi</button>
        </form>
        
        <p>Non hai un account? <a href="register.php">Registrati</a></p>
    </div>
</body>
</html>
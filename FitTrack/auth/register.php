<?php
// Modifica questa riga:
require_once '../includes/config.php';

// Aggiungi anche:
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header("Location: ../pages/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    
    // Validazione
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Tutti i campi sono obbligatori';
    } elseif ($password !== $confirm_password) {
        $error = 'Le password non coincidono';
    } elseif (strlen($password) < 8) {
        $error = 'La password deve essere di almeno 8 caratteri';
    } else {
        // Verifica se username o email esistono già
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username o email già in uso';
        } else {
            // Registra l'utente
            if (registerUser($username, $email, $password, $first_name, $last_name)) {
                $success = 'Registrazione completata! <a href="login.php">Accedi ora</a>';
            } else {
                $error = 'Errore durante la registrazione';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Registrazione</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h1>Registrati a FitTrack</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">Nome</label>
                    <input type="text" id="first_name" name="first_name">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Cognome</label>
                    <input type="text" id="last_name" name="last_name">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Conferma Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Registrati</button>
            </form>
            
            <p>Hai già un account? <a href="login.php">Accedi</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
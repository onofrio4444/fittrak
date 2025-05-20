<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/db.php';

$error = '';
$success = false;
$token = $_GET['token'] ?? '';

// Verifica il token
if (empty($token)) {
    $error = 'Token non valido';
} else {
    $stmt = $db->prepare("SELECT user_id FROM users WHERE reset_token = :token AND reset_token_expires > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = 'Token non valido o scaduto';
    }
}

// Processa il form di reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($password)) {
        $error = 'Password obbligatoria';
    } elseif (strlen($password) < 8) {
        $error = 'Password troppo corta (min 8 caratteri)';
    } elseif ($password !== $confirm_password) {
        $error = 'Le password non coincidono';
    } else {
        // Aggiorna la password e cancella il token
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("UPDATE users SET password_hash = :password_hash, reset_token = NULL, reset_token_expires = NULL WHERE user_id = :user_id");
        $stmt->execute([
            ':password_hash' => $password_hash,
            ':user_id' => $user['user_id']
        ]);
        
        $success = true;
    }
}

include '../includes/header.php';
?>

<div class="reset-password-container">
    <h1>Reimposta la tua password</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Password reimpostata con successo! <a href="login.php">Accedi ora</a>
        </div>
    <?php elseif (empty($error)): ?>
        <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="password">Nuova Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-hint">Minimo 8 caratteri</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Conferma Nuova Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Reimposta Password</button>
        </form>
    <?php else: ?>
        <div class="back-to-login">
            <a href="forgot_password.php">Richiedi un nuovo link</a> o <a href="login.php">torna al login</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Inserisci il tuo indirizzo email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Indirizzo email non valido';
    } else {
        // Verifica se l'email esiste nel database
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Genera un token univoco
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Salva il token nel database
            $stmt = $db->prepare("UPDATE users SET reset_token = :token, reset_token_expires = :expires WHERE user_id = :user_id");
            $stmt->execute([
                ':token' => $token,
                ':expires' => $expires,
                ':user_id' => $user['user_id']
            ]);
            
            // Invia l'email di reset (simulato in questo esempio)
            $reset_link = BASE_URL . "auth/reset_password.php?token=$token";
            
            // In un'applicazione reale, qui invieresti l'email
            $message = "Abbiamo inviato un link per il reset della password al tuo indirizzo email. <a href='$reset_link'>Clicca qui</a> se vuoi procedere ora.";
            
            // Per debug puoi visualizzare il link nella pagina
            $message .= "<div class='debug-info'><strong>DEBUG:</strong> $reset_link</div>";
        } else {
            $message = "Se l'email esiste nel nostro sistema, ti abbiamo inviato un link per il reset della password.";
        }
    }
}

include '../includes/header.php';
?>

<div class="forgot-password-container">
    <h1>Password dimenticata?</h1>
    <p>Inserisci il tuo indirizzo email e ti invieremo un link per reimpostare la tua password.</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php else: ?>
        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label for="email">Indirizzo Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Invia Link di Reset</button>
            
            <div class="back-to-login">
                <a href="login.php">Torna al login</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
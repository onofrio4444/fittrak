<?php
require_once '../includes/config.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);

$error = '';
$success = '';

// Gestione cambio password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Tutti i campi sono obbligatori';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Le nuove password non coincidono';
    } elseif (strlen($new_password) < 8) {
        $error = 'La nuova password deve essere di almeno 8 caratteri';
    } else {
        // Verifica la password corrente
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        
        if ($user_data && password_verify($current_password, $user_data['password_hash'])) {
            // Aggiorna la password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            
            if ($stmt->execute([$new_password_hash, $user_id])) {
                $success = 'Password cambiata con successo!';
            } else {
                $error = 'Errore durante l\'aggiornamento della password';
            }
        } else {
            $error = 'La password corrente non è corretta';
        }
    }
}

// Gestione preferenze notifiche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET 
            email_notifications = ?, 
            push_notifications = ? 
            WHERE user_id = ?");
        
        $stmt->execute([$email_notifications, $push_notifications, $user_id]);
        $success = 'Preferenze notifiche aggiornate!';
    } catch (PDOException $e) {
        $error = "Errore durante l'aggiornamento: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Impostazioni</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <h1>Impostazioni</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="settings-tabs">
                <div class="tab-header">
                    <button class="tab-link active" onclick="openTab(event, 'password-tab')">Cambia Password</button>
                    <button class="tab-link" onclick="openTab(event, 'notifications-tab')">Notifiche</button>
                    <button class="tab-link" onclick="openTab(event, 'account-tab')">Account</button>
                </div>
                
                <div id="password-tab" class="tab-content active">
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group">
                            <label for="current_password">Password corrente</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Nuova password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Conferma nuova password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Cambia Password</button>
                    </form>
                </div>
                
                <div id="notifications-tab" class="tab-content">
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="update_notifications" value="1">
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="email_notifications" name="email_notifications" 
                                   <?php echo ($user['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                            <label for="email_notifications">Notifiche via email</label>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="push_notifications" name="push_notifications" 
                                   <?php echo ($user['push_notifications'] ?? 1) ? 'checked' : ''; ?>>
                            <label for="push_notifications">Notifiche push</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Salva Preferenze</button>
                    </form>
                </div>
                
                <div id="account-tab" class="tab-content">
                    <div class="danger-zone">
                        <h3>Area pericolosa</h3>
                        <p>Queste azioni sono irreversibili. Procedi con cautela.</p>
                        
                        <button id="deleteAccountBtn" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Elimina Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/settings.js"></script>
</body>
</html>
<?php
require_once __DIR__ . '/../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gestione modifica impostazioni
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
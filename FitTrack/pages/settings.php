<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Gestione notifiche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_settings'])) {
    // Qui salveresti le preferenze di notifica nel database
    $success = 'Impostazioni notifiche aggiornate!';
}

// Gestione privacy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['privacy_settings'])) {
    // Qui salveresti le preferenze di privacy nel database
    $success = 'Impostazioni privacy aggiornate!';
}

include '../includes/header.php';
?>

<div class="container">
    <h1>Impostazioni</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="settings-tabs">
        <div class="tab-header">
            <button class="tab-link active" data-tab="notifications">Notifiche</button>
            <button class="tab-link" data-tab="privacy">Privacy</button>
            <button class="tab-link" data-tab="account">Account</button>
        </div>
        
        <div id="notifications" class="tab-content active">
            <form method="post">
                <h2>Impostazioni Notifiche</h2>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="activity_reminders" checked>
                        <span>Promemoria attività</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="goal_alerts" checked>
                        <span>Avvisi obiettivi</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="weekly_reports" checked>
                        <span>Report settimanali</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="notification_settings" class="btn">Salva</button>
                </div>
            </form>
        </div>
        
        <div id="privacy" class="tab-content">
            <form method="post">
                <h2>Impostazioni Privacy</h2>
                
                <div class="form-group">
                    <label>Profilo pubblico</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="profile_visibility" value="public" checked>
                            <span>Pubblico</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="profile_visibility" value="private">
                            <span>Privato</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_on_leaderboards" checked>
                        <span>Mostra nelle classifiche</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="share_with_friends" checked>
                        <span>Condividi attività con amici</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="privacy_settings" class="btn">Salva</button>
                </div>
            </form>
        </div>
        
        <div id="account" class="tab-content">
            <div class="account-actions">
                <h2>Azioni Account</h2>
                
                <div class="action-item">
                    <h3>Esporta dati</h3>
                    <p>Scarica una copia di tutti i tuoi dati in formato JSON o CSV</p>
                    <a href="../api/export_data.php" class="btn btn-secondary">Esporta dati</a>
                </div>
                
                <div class="action-item">
                    <h3>Elimina account</h3>
                    <p>Elimina permanentemente il tuo account e tutti i dati associati</p>
                    <button id="deleteAccountBtn" class="btn btn-danger">Elimina account</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gestione tab
document.querySelectorAll('.tab-link').forEach(tab => {
    tab.addEventListener('click', () => {
        // Nascondi tutti i tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Rimuovi active da tutti i tab links
        document.querySelectorAll('.tab-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Mostra il tab content selezionato
        const tabId = tab.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');
        tab.classList.add('active');
    });
});

// Conferma eliminazione account
document.getElementById('deleteAccountBtn').addEventListener('click', () => {
    if (confirm('Sei sicuro di voler eliminare il tuo account? Questa azione è irreversibile.')) {
        window.location.href = '../auth/delete_account.php';
    }
});
</script>

<?php include '../includes/footer.php'; ?>
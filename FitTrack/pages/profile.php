<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($user_id);

if (!$user) {
    redirect('../auth/logout.php'); // Forza logout se i dati utente non sono validi
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validazione
        if (empty($_POST['first_name']) || empty($_POST['email'])) {
            throw new Exception('Nome e email sono obbligatori');
        }
        
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email non valida');
        }
        
        // Verifica se l'email è già usata da un altro utente
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$_POST['email'], $user_id]);
        
        if ($stmt->fetch()) {
            throw new Exception('Questa email è già in uso da un altro account');
        }
        
        // Aggiornamento dati
        $update_fields = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'] ?? null,
            'email' => $_POST['email'],
            'birth_date' => !empty($_POST['birth_date']) ? $_POST['birth_date'] : null,
            'gender' => $_POST['gender'] ?? null,
            'height' => !empty($_POST['height']) ? (float)$_POST['height'] : null,
            'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null
        ];
        
        // Gestione upload immagine profilo
        if (!empty($_FILES['profile_pic']['name'])) {
            $upload_dir = '../assets/uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_ext, $allowed_ext)) {
                throw new Exception('Formato file non supportato. Usa JPG, PNG o GIF');
            }
            
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
                // Elimina la vecchia immagine se esiste
                if (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])) {
                    unlink('../' . $user['profile_pic']);
                }
                
                $update_fields['profile_pic'] = 'assets/uploads/profiles/' . $filename;
            }
        }
        
        // Costruisci la query di aggiornamento
        $set_parts = [];
        $params = [];
        
        foreach ($update_fields as $field => $value) {
            $set_parts[] = "$field = ?";
            $params[] = $value;
        }
        
        $params[] = $user_id;
        
        $query = "UPDATE users SET " . implode(', ', $set_parts) . " WHERE user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        $success = 'Profilo aggiornato con successo!';
        $user = array_merge($user, $update_fields); // Aggiorna i dati locali
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container">
    <h1>Il tuo Profilo</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-picture">
                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="../<?= $user['profile_pic'] ?>" alt="Foto profilo">
                <?php else: ?>
                    <div class="profile-picture-default">
                        <?= strtoupper(substr($user['first_name'] ?? $user['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="profile-stats">
                <h3>Statistiche</h3>
                <?php
                // Query per statistiche utente
                $stmt = $pdo->prepare("SELECT 
                    COUNT(*) as total_activities,
                    SUM(distance) as total_distance
                    FROM activities 
                    WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $stats = $stmt->fetch();
                ?>
                
                <div class="stat-item">
                    <span class="stat-label">Attività totali</span>
                    <span class="stat-value"><?= $stats['total_activities'] ?? 0 ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Km totali</span>
                    <span class="stat-value"><?= $stats['total_distance'] ?? 0 ?></span>
                </div>
            </div>
        </div>
        
        <div class="profile-content">
            <form method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Nome *</label>
                        <input type="text" id="first_name" name="first_name" required
                               value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Cognome</label>
                        <input type="text" id="last_name" name="last_name"
                               value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="birth_date">Data di nascita</label>
                        <input type="date" id="birth_date" name="birth_date"
                               value="<?= $user['birth_date'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Genere</label>
                        <select id="gender" name="gender">
                            <option value="">Seleziona...</option>
                            <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Maschio</option>
                            <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Femmina</option>
                            <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Altro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_pic">Foto profilo</label>
                        <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="height">Altezza (cm)</label>
                        <input type="number" id="height" name="height" step="0.1"
                               value="<?= $user['height'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Peso (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.1"
                               value="<?= $user['weight'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Salva modifiche</button>
                    <a href="change_password.php" class="btn btn-secondary">Cambia password</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
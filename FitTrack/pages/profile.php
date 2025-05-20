<?php
require_once '../includes/config.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);

// Gestione aggiornamento profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $birth_date = trim($_POST['birth_date']);
    $gender = trim($_POST['gender']);
    $height = trim($_POST['height']);
    $weight = trim($_POST['weight']);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            birth_date = ?, 
            gender = ?, 
            height = ?, 
            weight = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?");
        
        $stmt->execute([
            $first_name,
            $last_name,
            $birth_date ?: null,
            $gender ?: null,
            $height ?: null,
            $weight ?: null,
            $user_id
        ]);
        
        $success = "Profilo aggiornato con successo!";
        $user = getUserData($user_id); // Ricarica i dati aggiornati
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
    <title>FitTrack - Il mio Profilo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <h1>Il mio Profilo</h1>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-picture">
                        <img src="<?php echo $user['profile_picture'] ? '../uploads/' . $user['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
                             alt="Foto profilo">
                        <button id="changePhotoBtn" class="btn btn-sm">Cambia foto</button>
                        <input type="file" id="profilePhotoInput" accept="image/*" style="display: none;">
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                        <p>Membro dal <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
                
                <form method="POST" action="profile.php" class="profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Nome</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Cognome</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="birth_date">Data di nascita</label>
                            <input type="date" id="birth_date" name="birth_date" 
                                   value="<?php echo $user['birth_date'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="gender">Genere</label>
                            <select id="gender" name="gender">
                                <option value="">Seleziona...</option>
                                <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Maschio</option>
                                <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Femmina</option>
                                <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Altro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="height">Altezza (cm)</label>
                            <input type="number" id="height" name="height" step="0.01" 
                                   value="<?php echo $user['height'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="weight">Peso (kg)</label>
                            <input type="number" id="weight" name="weight" step="0.01" 
                                   value="<?php echo $user['weight'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Salva modifiche</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/profile.js"></script>
</body>
</html>
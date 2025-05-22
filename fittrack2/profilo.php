<?php
session_start();
require 'connessione.php';

// Verifica accesso
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

$utente_id = $_SESSION['utente_id'];
$errori = [];
$successo = false;

// Recupera dati utente
$sql_utente = "SELECT * FROM utenti WHERE id = ?";
$stmt_utente = $conn->prepare($sql_utente);
$stmt_utente->bind_param("i", $utente_id);
$stmt_utente->execute();
$utente = $stmt_utente->get_result()->fetch_assoc();
$stmt_utente->close();

// Gestione modifica profilo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifica_profilo'])) {
    $nome = secure_input($_POST['nome']);
    $cognome = secure_input($_POST['cognome']);
    $email = filter_var(secure_input($_POST['email']), FILTER_SANITIZE_EMAIL);
    $data_nascita = $_POST['data_nascita'];
    $sesso = $_POST['sesso'];

    // Validazione
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errori[] = "Email non valida";
    }

    if (empty($errori)) {
        $sql_update = "UPDATE utenti SET nome = ?, cognome = ?, email = ?, data_nascita = ?, sesso = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssi", $nome, $cognome, $email, $data_nascita, $sesso, $utente_id);
        
        if ($stmt_update->execute()) {
            $successo = "Profilo aggiornato con successo!";
            // Ricarica i dati utente
            $stmt_utente = $conn->prepare($sql_utente);
            $stmt_utente->bind_param("i", $utente_id);
            $stmt_utente->execute();
            $utente = $stmt_utente->get_result()->fetch_assoc();
            $stmt_utente->close();
        } else {
            $errori[] = "Errore durante l'aggiornamento del profilo";
        }
        $stmt_update->close();
    }
}

// Gestione cambio password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambio_password'])) {
    $vecchia_password = $_POST['vecchia_password'];
    $nuova_password = $_POST['nuova_password'];
    $conferma_password = $_POST['conferma_password'];

    // Verifica vecchia password
    if (!password_verify($vecchia_password, $utente['password'])) {
        $errori[] = "La password attuale non è corretta";
    }

    if (strlen($nuova_password) < 8) {
        $errori[] = "La nuova password deve avere almeno 8 caratteri";
    }

    if ($nuova_password !== $conferma_password) {
        $errori[] = "Le nuove password non coincidono";
    }

    if (empty($errori)) {
        $nuova_password_hash = password_hash($nuova_password, PASSWORD_BCRYPT);
        $sql_password = "UPDATE utenti SET password = ? WHERE id = ?";
        $stmt_password = $conn->prepare($sql_password);
        $stmt_password->bind_param("si", $nuova_password_hash, $utente_id);
        
        if ($stmt_password->execute()) {
            $successo = "Password cambiata con successo!";
        } else {
            $errori[] = "Errore durante il cambio password";
        }
        $stmt_password->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo - Fittrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?= strtoupper(substr($utente['nome'] ?? '', 0, 1)) . strtoupper(substr($utente['cognome'] ?? '', 0, 1)) ?>
                        </div>
                        <h4><?= htmlspecialchars($utente['nome'] ?? '') . ' ' . htmlspecialchars($utente['cognome'] ?? '') ?></h4>
                        <p class="text-muted">@<?= htmlspecialchars($utente['username']) ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Statistiche</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Membro dal:</small>
                            <p><?= date('d/m/Y', strtotime($utente['data_registrazione'])) ?></p>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Email:</small>
                            <p><?= htmlspecialchars($utente['email']) ?></p>
                        </div>
                        <?php if ($utente['data_nascita']): ?>
                        <div class="mb-2">
                            <small class="text-muted">Età:</small>
                            <p>
                                <?php 
                                $data_nascita = new DateTime($utente['data_nascita']);
                                $oggi = new DateTime();
                                $eta = $oggi->diff($data_nascita)->y;
                                echo $eta . ' anni';
                                ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Modifica Profilo</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($successo && isset($_POST['modifica_profilo'])): ?>
                            <div class="alert alert-success"><?= $successo ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errori) && isset($_POST['modifica_profilo'])): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errori as $errore): ?>
                                    <p class="mb-0"><?= $errore ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($utente['nome'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cognome</label>
                                    <input type="text" name="cognome" class="form-control" value="<?= htmlspecialchars($utente['cognome'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($utente['email']) ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Data di nascita</label>
                                    <input type="date" name="data_nascita" class="form-control" value="<?= $utente['data_nascita'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sesso</label>
                                    <select name="sesso" class="form-select">
                                        <option value="">Seleziona...</option>
                                        <option value="M" <?= ($utente['sesso'] ?? '') == 'M' ? 'selected' : '' ?>>Maschio</option>
                                        <option value="F" <?= ($utente['sesso'] ?? '') == 'F' ? 'selected' : '' ?>>Femmina</option>
                                        <option value="Altro" <?= ($utente['sesso'] ?? '') == 'Altro' ? 'selected' : '' ?>>Altro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" name="modifica_profilo" class="btn btn-primary">Salva modifiche</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Cambio Password</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($successo && isset($_POST['cambio_password'])): ?>
                            <div class="alert alert-success"><?= $successo ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errori) && isset($_POST['cambio_password'])): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errori as $errore): ?>
                                    <p class="mb-0"><?= $errore ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Password attuale</label>
                                <input type="password" name="vecchia_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nuova password (min. 8 caratteri)</label>
                                <input type="password" name="nuova_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Conferma nuova password</label>
                                <input type="password" name="conferma_password" class="form-control" required>
                            </div>
                            <button type="submit" name="cambio_password" class="btn btn-primary">Cambia password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
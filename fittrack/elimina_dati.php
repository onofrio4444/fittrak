<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

// Verifica se è stata confermata l'eliminazione
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conferma_eliminazione'])) {
    try {
        // Inizia una transazione
        $conn->begin_transaction();
        
        // Elimina tutte le attività dell'utente
        $sql1 = "DELETE FROM attivita WHERE id_utente = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $id_utente);
        $stmt1->execute();
        
        // Elimina tutti gli obiettivi dell'utente
        $sql2 = "DELETE FROM obiettivi WHERE id_utente = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $id_utente);
        $stmt2->execute();
        
        // Elimina le impostazioni dell'utente
        $sql3 = "DELETE FROM impostazioni_utente WHERE id_utente = ?";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("i", $id_utente);
        $stmt3->execute();
        
        // Commit della transazione
        $conn->commit();
        
        $_SESSION['messaggio_successo'] = "Tutti i tuoi dati sono stati eliminati con successo.";
        header("Location: impostazioni.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback in caso di errore
        $conn->rollback();
        $_SESSION['messaggio_errore'] = "Si è verificato un errore durante l'eliminazione dei dati: " . $e->getMessage();
        header("Location: impostazioni.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elimina Dati - FitTrack</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding-top: 50px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .warning-box {
            border-left: 4px solid #e74c3c;
            background-color: #f8d7da;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Elimina Tutti i Dati</h1>
        
        <div class="warning-box">
            <h4><i class="bi bi-exclamation-triangle-fill"></i> Attenzione!</h4>
            <p>Stai per eliminare definitivamente tutti i tuoi dati di allenamento e obiettivi. Questa azione è irreversibile.</p>
        </div>
        
        <p>Dopo questa operazione:</p>
        <ul>
            <li>Tutte le tue attività registrate verranno cancellate</li>
            <li>Tutti i tuoi obiettivi verranno rimossi</li>
            <li>Le tue impostazioni verranno reimpostate</li>
        </ul>
        
        <p class="text-danger"><strong>Questa azione non può essere annullata.</strong></p>
        
        <form method="POST">
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="conferma" name="conferma" required>
                <label class="form-check-label" for="conferma">
                    Confermo di voler eliminare definitivamente tutti i miei dati
                </label>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="impostazioni.php" class="btn btn-secondary">Annulla</a>
                <button type="submit" name="conferma_eliminazione" class="btn btn-danger">Elimina Dati</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
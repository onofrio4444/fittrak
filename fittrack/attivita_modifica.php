<?php
session_start();
require 'connessione.php';

if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

$id_utente = $_SESSION['utente_id'];
$errori = [];
$successo = false;

// Tipi di attività predefiniti
$tipi_attivita = ['Corsa', 'Ciclismo', 'Nuoto', 'Camminata', 'Palestra', 'Yoga', 'Altro'];

// Recupera attività da modificare
if (!isset($_GET['id'])) {
    header("Location: attivita.php");
    exit();
}

$id_attivita = $_GET['id'];
$sql = "SELECT * FROM attivita WHERE id = ? AND id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_attivita, $id_utente);
$stmt->execute();
$attivita = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$attivita) {
    header("Location: attivita.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_attivita = trim($_POST['tipo_attivita']);
    $durata = (int)$_POST['durata'];
    $distanza = !empty($_POST['distanza']) ? (float)$_POST['distanza'] : null;
    $calorie = !empty($_POST['calorie']) ? (int)$_POST['calorie'] : null;
    $data_attivita = $_POST['data_attivita'];
    $descrizione = trim($_POST['descrizione']);

    // Validazione
    if (empty($tipo_attivita)) $errori[] = "Il tipo di attività è obbligatorio";
    if ($durata <= 0) $errori[] = "La durata deve essere maggiore di 0";
    if (!empty($distanza) && $distanza <= 0) $errori[] = "La distanza deve essere maggiore di 0";
    if (!empty($calorie) && $calorie <= 0) $errori[] = "Le calorie devono essere maggiori di 0";

    if (empty($errori)) {
        $sql = "UPDATE attivita SET 
                tipo_attivita = ?, 
                durata = ?, 
                distanza = ?, 
                calorie = ?, 
                data_attivita = ?, 
                descrizione = ? 
                WHERE id = ? AND id_utente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiissii", $tipo_attivita, $durata, $distanza, $calorie, $data_attivita, $descrizione, $id_attivita, $id_utente);
        
        if ($stmt->execute()) {
            $successo = true;
            $_SESSION['messaggio_successo'] = "Attività modificata con successo!";
            header("Location: attivita.php");
            exit();
        } else {
            $errori[] = "Errore durante l'aggiornamento: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Attività - FitTrack</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <style>
        /* Stile identico a attivita_aggiungi.php */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #95a5a6;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .error {
            color: #e74c3c;
            margin-top: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

         /* Navbar Styles */
        .navbar {
            background-color: #2c3e50;
            padding: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-welcome {
            color: white;
            font-size: 14px;
        }
        
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .navbar-link {
            display: flex;
            align-items: center;
            gap: 5px;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-size: 14px;
        }
        
        .navbar-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .navbar-link.logout {
            background-color: #e74c3c;
        }
        
        .navbar-link.logout:hover {
            background-color: #c0392b;
        }
        
        .icon {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        
        .riepilogo {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .attivita-box {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .attivita {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }
        
        .link-rapidi {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
<!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">FitTrack</a>
            
            <div class="navbar-user">
                <span class="user-welcome">Benvenuto, <?php echo htmlspecialchars($_SESSION['utente_username']); ?>!</span>
                
                <div class="navbar-links">
                    <a href="profilo.php" class="navbar-link">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        Profilo
                    </a>
                    
                    <a href="impostazioni.php" class="navbar-link">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.82,11.69,4.82,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                        </svg>
                        Impostazioni
                    </a>
                    
                    <a href="logout.php" class="navbar-link logout">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

<div class="container">
    <h1>Modifica Attività</h1>
    
    <?php if ($successo): ?>
        <div class="success">Attività modificata con successo!</div>
    <?php endif; ?>
    
    <?php if (!empty($errori)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errori as $errore): ?>
                    <li><?= htmlspecialchars($errore) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label for="tipo_attivita">Tipo di attività *</label>
                <select id="tipo_attivita" name="tipo_attivita" required>
                    <option value="">Seleziona...</option>
                    <?php foreach ($tipi_attivita as $tipo): ?>
                        <option value="<?= $tipo ?>" <?= ($attivita['tipo_attivita'] == $tipo) ? 'selected' : '' ?>>
                            <?= $tipo ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="durata">Durata (minuti) *</label>
                <input type="number" id="durata" name="durata" min="1" required 
                       value="<?= htmlspecialchars($attivita['durata']) ?>">
            </div>
            
            <div class="form-group">
                <label for="distanza">Distanza (km)</label>
                <input type="number" id="distanza" name="distanza" min="0" step="0.01"
                       value="<?= htmlspecialchars($attivita['distanza'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="calorie">Calorie bruciate</label>
                <input type="number" id="calorie" name="calorie" min="0"
                       value="<?= htmlspecialchars($attivita['calorie'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="data_attivita">Data e Ora *</label>
                <input type="datetime-local" id="data_attivita" name="data_attivita" required
                       value="<?= date('Y-m-d\TH:i', strtotime($attivita['data_attivita'])) ?>">
            </div>
            
            <div class="form-group">
                <label for="descrizione">Descrizione</label>
                <textarea id="descrizione" name="descrizione"><?= htmlspecialchars($attivita['descrizione'] ?? '') ?></textarea>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn">Salva Modifiche</button>
                <a href="attivita.php" class="btn btn-secondary">Annulla</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
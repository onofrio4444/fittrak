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
        $sql = "INSERT INTO attivita (id_utente, tipo_attivita, durata, distanza, calorie, data_attivita, descrizione)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiiiss", $id_utente, $tipo_attivita, $durata, $distanza, $calorie, $data_attivita, $descrizione);

        if ($stmt->execute()) {
            $successo = true;
            $_SESSION['messaggio_successo'] = "Attività aggiunta con successo!";
            header("Location: attivita.php");
            exit();
        } else {
            $errori[] = "Errore durante il salvataggio: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Aggiungi Attività - FitTrack</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        

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

        

       

       

        
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-activity me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2"/>
            </svg>
            <strong>FitTrack</strong>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'attivita.php' ? 'active' : '' ?>" href="attivita.php">Attività</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'calendario.php' ? 'active' : '' ?>" href="calendario.php">Calendario</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'obiettivi.php' ? 'active' : '' ?>" href="obiettivi.php">Obiettivi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'progressi.php' ? 'active' : '' ?>" href="progressi.php">Progressi</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h1>Aggiungi Nuova Attività</h1>

    <?php if ($successo): ?>
        <div class="success">Attività aggiunta con successo!</div>
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
                        <option value="<?= $tipo ?>" <?= isset($tipo_attivita) && $tipo_attivita == $tipo ? 'selected' : '' ?>>
                            <?= $tipo ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="durata">Durata (minuti) *</label>
                <input type="number" id="durata" name="durata" min="1" required
                       value="<?= isset($durata) ? $durata : '' ?>">
            </div>

            <div class="form-group">
                <label for="distanza">Distanza (km)</label>
                <input type="number" id="distanza" name="distanza" min="0" step="0.01"
                       value="<?= isset($distanza) ? $distanza : '' ?>">
            </div>

            <div class="form-group">
                <label for="calorie">Calorie bruciate</label>
                <input type="number" id="calorie" name="calorie" min="0"
                       value="<?= isset($calorie) ? $calorie : '' ?>">
            </div>

            <div class="form-group">
                <label for="data_attivita">Data e Ora *</label>
                <input type="datetime-local" id="data_attivita" name="data_attivita" required
                       value="<?= isset($data_attivita) ? $data_attivita : date('Y-m-d\TH:i') ?>">
            </div>

            <div class="form-group">
                <label for="descrizione">Descrizione</label>
                <textarea id="descrizione" name="descrizione"><?= isset($descrizione) ? htmlspecialchars($descrizione) : '' ?></textarea>
            </div>

            <div class="button-group">
                <button type="submit" class="btn">Salva Attività</button>
                <a href="attivita.php" class="btn btn-secondary">Annulla</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}
include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

// Gestione aggiornamento impostazioni
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $messaggio = '';
    $tipo_messaggio = '';
    
    if (isset($_POST['aggiorna_profilo'])) {
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']);
        $email = trim($_POST['email']);
        $data_nascita = $_POST['data_nascita'];
        $sesso = $_POST['sesso'];
        $altezza = floatval($_POST['altezza']);
        $peso = floatval($_POST['peso']);
        
        $sql = "UPDATE utenti SET nome=?, cognome=?, email=?, data_nascita=?, sesso=?, altezza=?, peso=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssddi", $nome, $cognome, $email, $data_nascita, $sesso, $altezza, $peso, $id_utente);
        
        if ($stmt->execute()) {
            $messaggio = "Profilo aggiornato con successo!";
            $tipo_messaggio = "success";
        } else {
            $messaggio = "Errore nell'aggiornamento del profilo.";
            $tipo_messaggio = "error";
        }
        $stmt->close();
    }
    
    if (isset($_POST['aggiorna_obiettivi'])) {
        $obiettivo_peso = floatval($_POST['obiettivo_peso']);
        $obiettivo_calorie = intval($_POST['obiettivo_calorie']);
        $obiettivo_passi = intval($_POST['obiettivo_passi']);
        $obiettivo_attivita = intval($_POST['obiettivo_attivita']);
        
        // Verifica se esistono già obiettivi per questo utente
        $check_sql = "SELECT id FROM obiettivi WHERE id_utente = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id_utente);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Aggiorna obiettivi esistenti
            $sql = "UPDATE obiettivi SET obiettivo_peso=?, obiettivo_calorie=?, obiettivo_passi=?, obiettivo_attivita=? WHERE id_utente=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("diiii", $obiettivo_peso, $obiettivo_calorie, $obiettivo_passi, $obiettivo_attivita, $id_utente);
        } else {
            // Inserisci nuovi obiettivi
            $sql = "INSERT INTO obiettivi (id_utente, obiettivo_peso, obiettivo_calorie, obiettivo_passi, obiettivo_attivita) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idiii", $id_utente, $obiettivo_peso, $obiettivo_calorie, $obiettivo_passi, $obiettivo_attivita);
        }
        
        if ($stmt->execute()) {
            $messaggio = "Obiettivi aggiornati con successo!";
            $tipo_messaggio = "success";
        } else {
            $messaggio = "Errore nell'aggiornamento degli obiettivi.";
            $tipo_messaggio = "error";
        }
        $stmt->close();
        $check_stmt->close();
    }
    
    if (isset($_POST['aggiorna_notifiche'])) {
        $notifiche_email = isset($_POST['notifiche_email']) ? 1 : 0;
        $notifiche_promemoria = isset($_POST['notifiche_promemoria']) ? 1 : 0;
        $notifiche_obiettivi = isset($_POST['notifiche_obiettivi']) ? 1 : 0;
        
        // Verifica se esistono già impostazioni per questo utente
        $check_sql = "SELECT id FROM impostazioni_utente WHERE id_utente = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id_utente);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $sql = "UPDATE impostazioni_utente SET notifiche_email=?, notifiche_promemoria=?, notifiche_obiettivi=? WHERE id_utente=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $notifiche_email, $notifiche_promemoria, $notifiche_obiettivi, $id_utente);
        } else {
            $sql = "INSERT INTO impostazioni_utente (id_utente, notifiche_email, notifiche_promemoria, notifiche_obiettivi) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $id_utente, $notifiche_email, $notifiche_promemoria, $notifiche_obiettivi);
        }
        
        if ($stmt->execute()) {
            $messaggio = "Impostazioni notifiche aggiornate!";
            $tipo_messaggio = "success";
        } else {
            $messaggio = "Errore nell'aggiornamento delle notifiche.";
            $tipo_messaggio = "error";
        }
        $stmt->close();
        $check_stmt->close();
    }
    
    if (isset($_POST['cambia_password'])) {
        $password_attuale = $_POST['password_attuale'];
        $nuova_password = $_POST['nuova_password'];
        $conferma_password = $_POST['conferma_password'];
        
        // Verifica password attuale
        $sql = "SELECT password FROM utenti WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $utente = $result->fetch_assoc();
        
        if (password_verify($password_attuale, $utente['password'])) {
            if ($nuova_password === $conferma_password) {
                if (strlen($nuova_password) >= 6) {
                    $password_hash = password_hash($nuova_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE utenti SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $password_hash, $id_utente);
                    
                    if ($stmt->execute()) {
                        $messaggio = "Password cambiata con successo!";
                        $tipo_messaggio = "success";
                    } else {
                        $messaggio = "Errore nel cambio password.";
                        $tipo_messaggio = "error";
                    }
                } else {
                    $messaggio = "La nuova password deve essere di almeno 6 caratteri.";
                    $tipo_messaggio = "error";
                }
            } else {
                $messaggio = "Le password non coincidono.";
                $tipo_messaggio = "error";
            }
        } else {
            $messaggio = "Password attuale non corretta.";
            $tipo_messaggio = "error";
        }
        $stmt->close();
    }
}

// Carica dati utente
$sql = "SELECT * FROM utenti WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Carica obiettivi
$sql = "SELECT * FROM obiettivi WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$obiettivi = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Carica impostazioni notifiche
$sql = "SELECT * FROM impostazioni_utente WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$impostazioni = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>FitTrack - Impostazioni</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
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
        
        .navbar-link.active {
            background-color: rgba(255,255,255,0.2);
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
        
        .impostazioni-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .sidebar {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 10px 15px;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: #3498db;
            color: white;
        }
        
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section {
            display: none;
        }
        
        .section.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-danger {
            background-color: #e74c3c;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .messaggio {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .messaggio.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .messaggio.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .impostazioni-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .navbar-container {
                width: 95%;
                flex-direction: column;
                gap: 10px;
            }
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
                    
                    <a href="impostazioni.php" class="navbar-link active">
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
        <h1>Impostazioni</h1>
        
        <?php if (isset($messaggio)): ?>
            <div class="messaggio <?php echo $tipo_messaggio; ?>">
                <?php echo htmlspecialchars($messaggio); ?>
            </div>
        <?php endif; ?>

        <div class="impostazioni-container">
            <div class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="#" onclick="showSection('profilo')" class="menu-link active">Profilo Personale</a></li>
                    <li><a href="#" onclick="showSection('obiettivi')" class="menu-link">Obiettivi</a></li>
                    <li><a href="#" onclick="showSection('notifiche')" class="menu-link">Notifiche</a></li>
                    <li><a href="#" onclick="showSection('sicurezza')" class="menu-link">Sicurezza</a></li>
                    <li><a href="#" onclick="showSection('account')" class="menu-link">Account</a></li>
                </ul>
            </div>

            <div class="content">
                <!-- Sezione Profilo -->
                <div id="profilo" class="section active">
                    <h2>Informazioni Personali</h2>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($utente['nome'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="cognome">Cognome</label>
                                <input type="text" id="cognome" name="cognome" value="<?php echo htmlspecialchars($utente['cognome'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($utente['email']); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_nascita">Data di Nascita</label>
                                <input type="date" id="data_nascita" name="data_nascita" value="<?php echo $utente['data_nascita'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="sesso">Sesso</label>
                                <select id="sesso" name="sesso">
                                    <option value="">Seleziona</option>
                                    <option value="M" <?php echo ($utente['sesso'] ?? '') == 'M' ? 'selected' : ''; ?>>Maschio</option>
                                    <option value="F" <?php echo ($utente['sesso'] ?? '') == 'F' ? 'selected' : ''; ?>>Femmina</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="altezza">Altezza (cm)</label>
                                <input type="number" id="altezza" name="altezza" value="<?php echo $utente['altezza'] ?? ''; ?>" step="0.1" min="50" max="250">
                            </div>
                            <div class="form-group">
                                <label for="peso">Peso (kg)</label>
                                <input type="number" id="peso" name="peso" value="<?php echo $utente['peso'] ?? ''; ?>" step="0.1" min="20" max="300">
                            </div>
                        </div>
                        
                        <button type="submit" name="aggiorna_profilo" class="btn">Aggiorna Profilo</button>
                    </form>
                </div>

                <!-- Sezione Obiettivi -->
                <div id="obiettivi" class="section">
                    <h2>I Tuoi Obiettivi</h2>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="obiettivo_peso">Peso Obiettivo (kg)</label>
                                <input type="number" id="obiettivo_peso" name="obiettivo_peso" value="<?php echo $obiettivi['obiettivo_peso'] ?? ''; ?>" step="0.1" min="20" max="300">
                            </div>
                            <div class="form-group">
                                <label for="obiettivo_calorie">Calorie Giornaliere</label>
                                <input type="number" id="obiettivo_calorie" name="obiettivo_calorie" value="<?php echo $obiettivi['obiettivo_calorie'] ?? 2000; ?>" min="800" max="5000">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="obiettivo_passi">Passi Giornalieri</label>
                                <input type="number" id="obiettivo_passi" name="obiettivo_passi" value="<?php echo $obiettivi['obiettivo_passi'] ?? 10000; ?>" min="1000" max="50000">
                            </div>
                            <div class="form-group">
                                <label for="obiettivo_attivita">Attività Settimanali</label>
                                <input type="number" id="obiettivo_attivita" name="obiettivo_attivita" value="<?php echo $obiettivi['obiettivo_attivita'] ?? 3; ?>" min="1" max="14">
                            </div>
                        </div>
                        
                        <button type="submit" name="aggiorna_obiettivi" class="btn">Salva Obiettivi</button>
                    </form>
                </div>

                <!-- Sezione Notifiche -->
                <div id="notifiche" class="section">
                    <h2>Preferenze Notifiche</h2>
                    <form method="POST">
                        <div class="checkbox-group">
                            <input type="checkbox" id="notifiche_email" name="notifiche_email" <?php echo ($impostazioni['notifiche_email'] ?? 0) ? 'checked' : ''; ?>>
                            <label for="notifiche_email">Ricevi notifiche via email</label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="notifiche_promemoria" name="notifiche_promemoria" <?php echo ($impostazioni['notifiche_promemoria'] ?? 0) ? 'checked' : ''; ?>>
                            <label for="notifiche_promemoria">Promemoria attività giornaliere</label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="notifiche_obiettivi" name="notifiche_obiettivi" <?php echo ($impostazioni['notifiche_obiettivi'] ?? 0) ? 'checked' : ''; ?>>
                            <label for="notifiche_obiettivi">Notifiche raggiungimento obiettivi</label>
                        </div>
                        
                        <button type="submit" name="aggiorna_notifiche" class="btn">Salva Preferenze</button>
                    </form>
                </div>

                <!-- Sezione Sicurezza -->
                <div id="sicurezza" class="section">
                    <h2>Sicurezza Account</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="password_attuale">Password Attuale</label>
                            <input type="password" id="password_attuale" name="password_attuale" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nuova_password">Nuova Password</label>
                            <input type="password" id="nuova_password" name="nuova_password" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="conferma_password">Conferma Nuova Password</label>
                            <input type="password" id="conferma_password" name="conferma_password" required minlength="6">
                        </div>
                        
                        <button type="submit" name="cambia_password" class="btn">Cambia Password</button>
                    </form>
                </div>

                <!-- Sezione Account -->
                <div id="account" class="section">
                    <h2>Gestione Account</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php 
                                $sql = "SELECT COUNT(*) as count FROM attivita WHERE id_utente = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $id_utente);
                                $stmt->execute();
                                $result = $stmt->get_result()->fetch_assoc();
                                echo $result['count'];
                                $stmt->close();
                            ?></div>
                            <div class="stat-label">Attività Registrate</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number"><?php 
                                $sql = "SELECT DATEDIFF(CURDATE(), data_registrazione) as giorni FROM utenti WHERE id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $id_utente);
                                $stmt->execute();
                                $result = $stmt->get_result()->fetch_assoc();
                                echo $result['giorni'] ?? 0;
                                $stmt->close();
                            ?></div>
                            <div class="stat-label">Giorni su FitTrack</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number"><?php 
                                $sql = "SELECT SUM(calorie) as totale FROM attivita WHERE id_utente = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $id_utente);
                                $stmt->execute();
                                $result = $stmt->get_result()->fetch_assoc();
                                echo number_format($result['totale'] ?? 0);
                                $stmt->close();
                            ?></div>
                            <div class="stat-label">Calorie Bruciate</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <h3>Esporta Dati</h3>
                        <p>Scarica tutti i tuoi dati di allenamento in formato CSV.</p>
                        <a href="esporta_dati.php" class="btn">Esporta Dati</a>
                    </div>
                    
                    <div class="form-group" style="margin-top: 40px; border-top: 2px solid #e74c3c; padding-top: 20px;">
                        <h3 style="color: #e74c3c;">Zona Pericolosa</h3>
                        <p style="color: #666;">Attenzione: queste azioni sono irreversibili.</p>
                        
                        <div style="margin-top: 20px;">
                            <button type="button" onclick="if(confirm('Sei sicuro di voler eliminare tutti i tuoi dati di allenamento? Questa azione non può essere annullata.')) { window.location.href='elimina_dati.php'; }" class="btn btn-danger">
                                Elimina Tutti i Dati
                            </button>
                        </div>
                        
                        <div style="margin-top: 15px;">
                            <button type="button" onclick="if(confirm('Sei sicuro di voler eliminare definitivamente il tuo account? Tutti i dati verranno persi per sempre.')) { window.location.href='elimina_account.php'; }" class="btn btn-danger">
                                Elimina Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Nascondi tutte le sezioni
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            
            // Rimuovi classe active da tutti i link del menu
            const menuLinks = document.querySelectorAll('.menu-link');
            menuLinks.forEach(link => {
                link.classList.remove('active');
            });
            
            // Mostra la sezione selezionata
            document.getElementById(sectionId).classList.add('active');
            
            // Aggiungi classe active al link cliccato
            event.target.classList.add('active');
        }
        
        // Validazione password in tempo reale
        document.getElementById('conferma_password').addEventListener('input', function() {
            const nuovaPassword = document.getElementById('nuova_password').value;
            const confermaPassword = this.value;
            
            if (nuovaPassword !== confermaPassword) {
                this.setCustomValidity('Le password non coincidono');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Calcolo BMI automatico
        function calcolaBMI() {
            const peso = parseFloat(document.getElementById('peso').value);
            const altezza = parseFloat(document.getElementById('altezza').value) / 100; // converti cm in metri
            
            if (peso && altezza) {
                const bmi = peso / (altezza * altezza);
                let categoria = '';
                
                if (bmi < 18.5) categoria = 'Sottopeso';
                else if (bmi < 25) categoria = 'Normale';
                else if (bmi < 30) categoria = 'Sovrappeso';
                else categoria = 'Obeso';
                
                // Mostra BMI se non esiste già
                let bmiDisplay = document.getElementById('bmi-display');
                if (!bmiDisplay) {
                    bmiDisplay = document.createElement('div');
                    bmiDisplay.id = 'bmi-display';
                    bmiDisplay.style.marginTop = '10px';
                    bmiDisplay.style.padding = '10px';
                    bmiDisplay.style.backgroundColor = '#f8f9fa';
                    bmiDisplay.style.borderRadius = '4px';
                    document.getElementById('peso').parentNode.appendChild(bmiDisplay);
                }
                
                bmiDisplay.innerHTML = `<strong>BMI: ${bmi.toFixed(1)} (${categoria})</strong>`;
            }
        }
        
        document.getElementById('peso').addEventListener('input', calcolaBMI);
        document.getElementById('altezza').addEventListener('input', calcolaBMI);
        
        // Calcola BMI al caricamento della pagina se i dati sono presenti
        window.addEventListener('load', calcolaBMI);
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
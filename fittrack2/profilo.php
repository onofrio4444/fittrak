<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}
include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

// Carica dati utente
$sql = "SELECT * FROM utenti WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Carica obiettivi utente
$sql = "SELECT * FROM obiettivi WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$obiettivi = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Statistiche generali
$stats = [];

// Totale attività
$sql = "SELECT COUNT(*) as totale FROM attivita WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$stats['totale_attivita'] = $stmt->get_result()->fetch_assoc()['totale'];
$stmt->close();

// Calorie totali bruciate
$sql = "SELECT SUM(calorie) as totale FROM attivita WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$stats['calorie_totali'] = $stmt->get_result()->fetch_assoc()['totale'] ?? 0;
$stmt->close();

// Distanza totale
$sql = "SELECT SUM(distanza) as totale FROM attivita WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$stats['distanza_totale'] = $stmt->get_result()->fetch_assoc()['totale'] ?? 0;
$stmt->close();

// Tempo totale di allenamento
$sql = "SELECT SUM(durata) as totale FROM attivita WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$stats['tempo_totale'] = $stmt->get_result()->fetch_assoc()['totale'] ?? 0;
$stmt->close();

// Attività di questa settimana
$sql = "SELECT COUNT(*) as totale FROM attivita WHERE id_utente = ? AND YEARWEEK(data_attivita, 1) = YEARWEEK(CURDATE(), 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$stats['attivita_settimana'] = $stmt->get_result()->fetch_assoc()['totale'];
$stmt->close();

// Calorie di questa settimana
$sql = "SELECT SUM(calorie) as totale FROM attivita WHERE id_utente = ? AND YEARWEEK(data_attivita, 1) = YEARWEEK(CURDATE(), 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$stats['calorie_settimana'] = $stmt->get_result()->fetch_assoc()['totale'] ?? 0;
$stmt->close();

// Attività più praticata
$sql = "SELECT tipo_attivita, COUNT(*) as conteggio FROM attivita WHERE id_utente = ? GROUP BY tipo_attivita ORDER BY conteggio DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stats['attivita_preferita'] = $result['tipo_attivita'] ?? 'Nessuna';
$stmt->close();

// Calcolo BMI
$bmi = null;
$categoria_bmi = '';
if ($utente['peso'] && $utente['altezza']) {
    $altezza_m = $utente['altezza'] / 100;
    $bmi = $utente['peso'] / ($altezza_m * $altezza_m);
    
    if ($bmi < 18.5) $categoria_bmi = 'Sottopeso';
    elseif ($bmi < 25) $categoria_bmi = 'Normale';
    elseif ($bmi < 30) $categoria_bmi = 'Sovrappeso';
    else $categoria_bmi = 'Obeso';
}

// Calcolo età
$eta = null;
if ($utente['data_nascita']) {
    $data_nascita = new DateTime($utente['data_nascita']);
    $oggi = new DateTime();
    $eta = $oggi->diff($data_nascita)->y;
}

// Progresso obiettivi settimanali
$progresso_obiettivi = [];
if ($obiettivi) {
    // Progresso attività settimanali
    $progresso_obiettivi['attivita'] = [
        'attuale' => $stats['attivita_settimana'],
        'obiettivo' => $obiettivi['obiettivo_attivita'] ?? 0,
        'percentuale' => $obiettivi['obiettivo_attivita'] > 0 ? min(100, ($stats['attivita_settimana'] / $obiettivi['obiettivo_attivita']) * 100) : 0
    ];
    
    // Progresso calorie settimanali (obiettivo giornaliero * 7)
    $obiettivo_calorie_settimana = ($obiettivi['obiettivo_calorie'] ?? 0) * 7;
    $progresso_obiettivi['calorie'] = [
        'attuale' => $stats['calorie_settimana'],
        'obiettivo' => $obiettivo_calorie_settimana,
        'percentuale' => $obiettivo_calorie_settimana > 0 ? min(100, ($stats['calorie_settimana'] / $obiettivo_calorie_settimana) * 100) : 0
    ];
}

// Ultime 5 attività
$sql = "SELECT tipo_attivita, durata, distanza, calorie, data_attivita FROM attivita WHERE id_utente = ? ORDER BY data_attivita DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$ultime_attivita = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Dati per il grafico delle attività settimanali (ultimi 7 giorni)
$dati_grafico = [];
for ($i = 6; $i >= 0; $i--) {
    $data = date('Y-m-d', strtotime("-$i days"));
    $sql = "SELECT SUM(calorie) as calorie FROM attivita WHERE id_utente = ? AND DATE(data_attivita) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_utente, $data);
    $stmt->execute();
    $risultato = $stmt->get_result()->fetch_assoc();
    $dati_grafico[] = [
        'data' => date('d/m', strtotime($data)),
        'calorie' => $risultato['calorie'] ?? 0
    ];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>FitTrack - Profilo di <?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome']); ?></title>
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
        
        /* Profilo Header */
        .profilo-header {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
        }
        
        .profilo-info h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
        }
        
        .profilo-info p {
            margin: 5px 0;
            opacity: 0.9;
        }
        
        .profilo-badges {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .badge {
            background-color: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
        }
        
        /* Grid Layout */
        .profilo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .profilo-section {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: bold;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        /* Statistiche */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        /* Informazioni Personali */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .info-value {
            color: #666;
        }
        
        /* Progresso Obiettivi */
        .progress-item {
            margin-bottom: 20px;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: width 0.3s ease;
        }
        
        /* Grafico */
        .chart-container {
            position: relative;
            height: 250px;
            margin-top: 20px;
        }
        
        .chart {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 5px;
            padding: 0 10px;
        }
        
        .chart-bar {
            flex: 1;
            background: linear-gradient(to top, #3498db, #5dade2);
            border-radius: 3px 3px 0 0;
            position: relative;
            min-height: 5px;
            transition: all 0.3s ease;
        }
        
        .chart-bar:hover {
            opacity: 0.8;
        }
        
        .chart-label {
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: #666;
        }
        
        .chart-value {
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: #2c3e50;
            font-weight: bold;
        }
        
        /* Attività Recenti */
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-info {
            flex: 1;
        }
        
        .activity-type {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .activity-details {
            font-size: 12px;
            color: #666;
        }
        
        .activity-date {
            font-size: 11px;
            color: #999;
            text-align: right;
        }
        
        /* Bottoni */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-outline {
            background-color: transparent;
            color: #3498db;
            border: 1px solid #3498db;
        }
        
        .btn-outline:hover {
            background-color: #3498db;
            color: white;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .actions .btn {
            margin: 0 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .profilo-grid {
                grid-template-columns: 1fr;
            }
            
            .profilo-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .navbar-container {
                width: 95%;
                flex-direction: column;
                gap: 10px;
            }
            
            .navbar-links {
                flex-wrap: wrap;
                justify-content: center;
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
                    <a href="profilo.php" class="navbar-link active">
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
        <!-- Header Profilo -->
        <div class="profilo-header">
            <div class="avatar">
                <?php echo strtoupper(substr($utente['nome'], 0, 1) . substr($utente['cognome'], 0, 1)); ?>
            </div>
            <div class="profilo-info">
                <h1><?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome']); ?></h1>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($utente['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($utente['email']); ?></p>
                <?php if ($eta): ?>
                <p><strong>Età:</strong> <?php echo $eta; ?> anni</p>
                <?php endif; ?>
                
                <div class="profilo-badges">
                    <span class="badge">Membro dal <?php echo date('d/m/Y', strtotime($utente['data_registrazione'])); ?></span>
                    <?php if ($stats['attivita_preferita'] != 'Nessuna'): ?>
                    <span class="badge">Ama: <?php echo htmlspecialchars($stats['attivita_preferita']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Grid Principale -->
        <div class="profilo-grid">
            <!-- Statistiche Generali -->
            <div class="profilo-section">
                <h2 class="section-title">Statistiche Generali</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['totale_attivita']; ?></div>
                        <div class="stat-label">Attività Totali</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($stats['calorie_totali']); ?></div>
                        <div class="stat-label">Calorie Bruciate</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($stats['distanza_totale'], 1); ?></div>
                        <div class="stat-label">Km Percorsi</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($stats['tempo_totale']); ?></div>
                        <div class="stat-label">Minuti Allenamento</div>
                    </div>
                </div>
            </div>

            <!-- Informazioni Personali -->
            <div class="profilo-section">
                <h2 class="section-title">Informazioni Personali</h2>
                <div class="info-grid">
                    <div>
                        <div class="info-item">
                            <span class="info-label">Sesso:</span>
                            <span class="info-value"><?php echo $utente['sesso'] == 'M' ? 'Maschio' : ($utente['sesso'] == 'F' ? 'Femmina' : 'Non specificato'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Altezza:</span>
                            <span class="info-value"><?php echo $utente['altezza'] ? $utente['altezza'] . ' cm' : 'Non specificata'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Peso:</span>
                            <span class="info-value"><?php echo $utente['peso'] ? $utente['peso'] . ' kg' : 'Non specificato'; ?></span>
                        </div>
                    </div>
                    <div>
                        <?php if ($bmi): ?>
                        <div class="info-item">
                            <span class="info-label">BMI:</span>
                            <span class="info-value"><?php echo number_format($bmi, 1) . ' (' . $categoria_bmi . ')'; ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($obiettivi && $obiettivi['obiettivo_peso']): ?>
                        <div class="info-item">
                            <span class="info-label">Peso Obiettivo:</span>
                            <span class="info-value"><?php echo $obiettivi['obiettivo_peso']; ?> kg</span>
                        </div>
                        <?php endif; ?>
                        <div class="info-item">
                            <span class="info-label">Attività Preferita:</span>
                            <span class="info-value"><?php echo htmlspecialchars($stats['attivita_preferita']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sezione Progresso e Attività -->
        <div class="profilo-grid">
            <!-- Progresso Obiettivi -->
            <?php if ($obiettivi): ?>
            <div class="profilo-section">
                <h2 class="section-title">Progresso Settimanale</h2>
                
                <div class="progress-item">
                    <div class="progress-header">
                        <span>Attività Settimanali</span>
                        <span><?php echo $progresso_obiettivi['attivita']['attuale']; ?>/<?php echo $progresso_obiettivi['attivita']['obiettivo']; ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progresso_obiettivi['attivita']['percentuale']; ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-header">
                        <span>Calorie Settimanali</span>
                        <span><?php echo number_format($progresso_obiettivi['calorie']['attuale']); ?>/<?php echo number_format($progresso_obiettivi['calorie']['obiettivo']); ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progresso_obiettivi['calorie']['percentuale']; ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Grafico Attività -->
            <div class="profilo-section">
                <h2 class="section-title">Attività Ultimi 7 Giorni</h2>
                <div class="chart-container">
                    <div class="chart">
                        <?php 
                        $max_calorie = max(array_column($dati_grafico, 'calorie'));
                        $max_calorie = $max_calorie > 0 ? $max_calorie : 1;
                        foreach ($dati_grafico as $giorno): 
                            $altezza = ($giorno['calorie'] / $max_calorie) * 200;
                        ?>
                        <div class="chart-bar" style="height: <?php echo $altezza; ?>px;">
                            <span class="chart-value"><?php echo $giorno['calorie']; ?></span>
                            <span class="chart-label"><?php echo $giorno['data']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attività Recenti -->
        <div class="profilo-section">
            <h2 class="section-title">Attività Recenti</h2>
            <?php if (!empty($ultime_attivita)): ?>
            <ul class="activity-list">
                <?php foreach ($ultime_attivita as $attivita): ?>
                <li class="activity-item">
                    <div class="activity-info">
                        <div class="activity-type"><?php echo htmlspecialchars($attivita['tipo_attivita']); ?></div>
                        <div class="activity-details">
                            <?php echo $attivita['durata']; ?> min • 
                            <?php echo $attivita['distanza']; ?> km • 
                            <?php echo $attivita['calorie']; ?> cal
                        </div>
                    </div>
                    <div class="activity-date">
                        <?php echo date('d/m/Y H:i', strtotime($attivita['data_attivita'])); ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p style="text-align: center; color: #666; margin-top: 40px;">
                Nessuna attività registrata ancora.<br>
                <a href="attivita.php" class="btn" style="margin-top: 15px;">Aggiungi la tua prima attività</a>
            </p>
            <?php endif; ?>
        </div>

        <!-- Azioni -->
        <div class="actions">
            <a href="attivita.php" class="btn">Gestisci Attività</a>
            <a href="impostazioni.php" class="btn btn-outline">Modifica Profilo</a>
            <a href="calendario.php" class="btn btn-outline">Visualizza Calendario</a>
        </div>
    </div>

    <script>
        // Animazione delle barre del progresso
        window.addEventListener('load', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        });

        // Animazione delle barre del grafico
        window.addEventListener('load', function() {
            const chartBars = document.querySelectorAll('.chart-bar');
            chartBars.forEach((bar, index) => {
                const height = bar.style.height;
                bar.style.height = '5px';
                setTimeout(() => {
                    bar.style.height = height;
                }, 800 + (index * 100));
            });
        });

        // Tooltip per le barre del grafico
        const chartBars = document.querySelectorAll('.chart-bar');
        chartBars.forEach(bar => {
            bar.style.cursor = 'pointer';
            bar.addEventListener('mouseenter', function() {
                const value = this.querySelector('.chart-value');
                if (value) {
                    value.style.opacity = '1';
                    value.style.transform = 'translateX(-50%) scale(1.1)';
                }
            });
            
            bar.addEventListener('mouseleave', function() {
                const value = this.querySelector('.chart-value');
                if (value) {
                    value.style.opacity = '0.7';
                    value.style.transform = 'translateX(-50%) scale(1)';
                }
            });
        });

        // Calcolo dinamico del BMI se si modificano peso/altezza (per future implementazioni)
        function calcolaBMI(peso, altezza) {
            if (peso && altezza) {
                const altezza_m = altezza / 100;
                const bmi = peso / (altezza_m * altezza_m);
                
                let categoria = '';
                if (bmi < 18.5) categoria = 'Sottopeso';
                else if (bmi < 25) categoria = 'Normale';
                else if (bmi < 30) categoria = 'Sovrappeso';
                else categoria = 'Obeso';
                
                return {
                    valore: bmi.toFixed(1),
                    categoria: categoria
                };
            }
            return null;
        }

        // Funzione per condividere i risultati (per future implementazioni)
        function condividiRisultati() {
            if (navigator.share) {
                navigator.share({
                    title: 'I miei progressi su FitTrack',
                    text: `Ho completato ${<?php echo $stats['totale_attivita']; ?>} attività e bruciato ${<?php echo $stats['calorie_totali']; ?>} calorie!`,
                    url: window.location.href
                });
            } else {
                // Fallback per browser che non supportano Web Share API
                const text = `Ho completato ${<?php echo $stats['totale_attivita']; ?>} attività e bruciato ${<?php echo $stats['calorie_totali']; ?>} calorie su FitTrack!`;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text);
                    alert('Testo copiato negli appunti!');
                }
            }
        }

        // Aggiunta del pulsante condividi (opzionale)
        const actions = document.querySelector('.actions');
        if (actions && <?php echo $stats['totale_attivita']; ?> > 0) {
            const shareBtn = document.createElement('button');
            shareBtn.className = 'btn btn-outline';
            shareBtn.textContent = 'Condividi Progressi';
            shareBtn.onclick = condividiRisultati;
            actions.appendChild(shareBtn);
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
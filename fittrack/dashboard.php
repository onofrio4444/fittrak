<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}
include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

// Statistiche attività
$stats = [
    'totale_attivita' => 0,
    'calorie_totali' => 0,
    'distanza_totale' => 0,
    'ultima_attivita' => 'Nessuna'
];

$sql_stats = "SELECT 
    COUNT(*) as totale_attivita,
    SUM(calorie) as calorie_totali,
    SUM(distanza) as distanza_totale
    FROM attivita 
    WHERE id_utente = ?";
$stmt = $conn->prepare($sql_stats);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats = array_merge($stats, $row);
}
$stmt->close();

// Ultima attività
$sql_ultima = "SELECT tipo_attivita, data_attivita FROM attivita 
               WHERE id_utente = ? 
               ORDER BY data_attivita DESC 
               LIMIT 1";
$stmt = $conn->prepare($sql_ultima);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['ultima_attivita'] = $row['tipo_attivita'] . ' (' . date('d/m/Y', strtotime($row['data_attivita'])) . ')';
}
$stmt->close();

// Attività recenti
$sql_attivita = "SELECT * FROM attivita 
                 WHERE id_utente = ? 
                 ORDER BY data_attivita DESC 
                 LIMIT 5";
$stmt = $conn->prepare($sql_attivita);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$attivita_recenti = $stmt->get_result();

// Tipi di attività più frequenti
$sql_tipi = "SELECT tipo_attivita, COUNT(*) as conteggio 
             FROM attivita 
             WHERE id_utente = ? 
             GROUP BY tipo_attivita 
             ORDER BY conteggio DESC 
             LIMIT 5";
$stmt = $conn->prepare($sql_tipi);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$tipi_frequenti = $stmt->get_result();

// Prepara i dati per i grafici
$giorni_settimana = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
$dati_settimana = array_fill(0, 7, 0);

$sql_grafico = "SELECT DAYOFWEEK(data_attivita) AS giorno, COUNT(*) as numero
                FROM attivita
                WHERE id_utente = ? AND WEEK(data_attivita) = WEEK(NOW()) AND YEAR(data_attivita) = YEAR(NOW())
                GROUP BY giorno";
$stmt = $conn->prepare($sql_grafico);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
while ($riga = $result->fetch_assoc()) {
    $indice = ($riga['giorno'] + 5) % 7; // Da 1-7 (DOM-SAB) a 0-6 (LUN-DOM)
    $dati_settimana[$indice] = $riga['numero'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Dashboard</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: var(--secondary);
            padding-top: 70px;
        }
        
        .navbar {
            background-color: var(--secondary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
            height: 100%;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .stat-card .stat-label {
            color: var(--secondary);
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .activity-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            border-left: 3px solid var(--primary);
            transition: transform 0.3s;
        }
        
        .activity-card:hover {
            transform: translateY(-3px);
        }
        
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .quick-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: white;
            border-radius: 8px;
            text-decoration: none;
            color: var(--secondary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s;
            margin-bottom: 10px;
        }
        
        .quick-link:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .section-title {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            font-weight: 600;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
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
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'progressi.php' ? 'active' : '' ?>" href="sincronizazione.php">Sincronizza Dispositivo</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['utente_username'])): ?>
                        <span class="text-white me-3 d-none d-md-inline">Benvenuto, <?= htmlspecialchars($_SESSION['utente_username']) ?></span>
                        <a href="impostazioni.php" class="btn btn-outline-light btn-sm me-2" title="Impostazioni">
                            <i class="bi bi-gear"></i>
                        </a>
                        <a href="profilo.php" class="btn btn-outline-light btn-sm me-2" title="Profilo">
                            <i class="bi bi-person"></i>
                        </a>
                        <a href="logout.php" class="btn btn-danger btn-sm" title="Logout">
                            <i class="bi bi-box-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <h1 class="section-title">Dashboard Attività</h1>
        
        <!-- Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['totale_attivita'] ?></div>
                    <div class="stat-label">Attività totali</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['calorie_totali'] ?? 0 ?></div>
                    <div class="stat-label">Calorie bruciate</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['distanza_totale'] ?? 0 ?></div>
                    <div class="stat-label">Distanza (km)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?= explode(' ', $stats['ultima_attivita'])[0] ?></div>
                    <div class="stat-label">Ultima attività</div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Colonna sinistra -->
            <div class="col-lg-8">
                <!-- Grafico attività settimanali -->
                <div class="chart-container">
                    <h5 class="section-title">Andamento Settimanale</h5>
                    <div class="chart-wrapper">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
                
                <!-- Attività recenti -->
                <div class="chart-container">
                    <h5 class="section-title">Attività Recenti</h5>
                    <?php if ($attivita_recenti->num_rows > 0): ?>
                        <?php while ($att = $attivita_recenti->fetch_assoc()): ?>
                            <div class="activity-card">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($att['tipo_attivita']) ?></strong>
                                        <div class="text-muted small">
                                            <?= date('d/m/Y H:i', strtotime($att['data_attivita'])) ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div><?= $att['durata'] ?> min</div>
                                        <?php if ($att['distanza']): ?>
                                            <div><?= $att['distanza'] ?> km</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Nessuna attività registrata</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Colonna destra -->
            <div class="col-lg-4">
                <!-- Tipi di attività più frequenti -->
                <div class="chart-container">
                    <h5 class="section-title">Attività Preferite</h5>
                    <div class="chart-wrapper">
                        <canvas id="activityTypesChart"></canvas>
                    </div>
                </div>
                
                <!-- Link rapidi -->
                <div class="chart-container">
                    <h5 class="section-title">Azioni Rapide</h5>
                    <a href="attivita_aggiungi.php" class="quick-link">
                        <i class="bi bi-plus-circle"></i> Nuova attività
                    </a>
                    <a href="calendario.php" class="quick-link">
                        <i class="bi bi-calendar"></i> Calendario
                    </a>
                    <a href="obiettivi.php" class="quick-link">
                        <i class="bi bi-bullseye"></i> Obiettivi
                    </a>
                    <a href="progressi.php" class="quick-link">
                        <i class="bi bi-graph-up"></i> Progressi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Grafico a barre - Attività settimanali
        const ctx1 = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?= json_encode($giorni_settimana) ?>,
                datasets: [{
                    label: 'Numero attività',
                    data: <?= json_encode($dati_settimana) ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Grafico a torta - Tipi di attività
        const ctx2 = document.getElementById('activityTypesChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    mysqli_data_seek($tipi_frequenti, 0);
                    while($row = $tipi_frequenti->fetch_assoc()) {
                        echo '"' . htmlspecialchars($row['tipo_attivita']) . '",';
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        mysqli_data_seek($tipi_frequenti, 0);
                        while($row = $tipi_frequenti->fetch_assoc()) {
                            echo $row['conteggio'] . ',';
                        }
                        ?>
                    ],
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
    </script>

    
<?php include 'footer.php'; ?>
</body>
</html>
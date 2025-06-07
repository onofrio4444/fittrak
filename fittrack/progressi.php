<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

// Recupera statistiche generali (versione modificata senza data_creazione)
$sql_stats = "SELECT 
    COUNT(*) as total_goals,
    SUM(CASE WHEN completato = 1 OR stato = 'completato' THEN 1 ELSE 0 END) as completed_goals
    FROM obiettivi 
    WHERE id_utente = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $id_utente);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

// Percentuale completamento
$completion_rate = $stats['total_goals'] > 0 ? round(($stats['completed_goals'] / $stats['total_goals']) * 100) : 0;

// Recupera obiettivi recenti (versione modificata)
$sql_recent = "SELECT * FROM obiettivi 
              WHERE id_utente = ? 
              ORDER BY data_scadenza DESC 
              LIMIT 5";
$stmt_recent = $conn->prepare($sql_recent);
$stmt_recent->bind_param("i", $id_utente);
$stmt_recent->execute();
$recent_goals = $stmt_recent->get_result();

// Recupera progressi per tipo (versione modificata)
$sql_by_type = "SELECT 
    COUNT(*) as count,
    CASE 
        WHEN obiettivo_peso IS NOT NULL THEN 'Peso'
        WHEN obiettivo_calorie IS NOT NULL THEN 'Calorie'
        WHEN obiettivo_passi IS NOT NULL THEN 'Passi'
        WHEN obiettivo_attivita IS NOT NULL THEN 'Attività'
        ELSE 'Altro'
    END as goal_type
    FROM obiettivi
    WHERE id_utente = ?
    GROUP BY goal_type";
$stmt_by_type = $conn->prepare($sql_by_type);
$stmt_by_type->bind_param("i", $id_utente);
$stmt_by_type->execute();
$goals_by_type = $stmt_by_type->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progressi - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
        }
        
        .progress-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .progress-card:hover {
            transform: translateY(-5px);
        }
        
        .completion-ring {
            width: 120px;
            height: 120px;
        }
        
        .goal-card {
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        
        .goal-card.completed {
            border-left-color: var(--success);
            background-color: rgba(46, 204, 113, 0.05);
        }
        
        .goal-card.expired {
            border-left-color: var(--danger);
        }
        
        .chart-container {
            height: 300px;
        }
    </style>
</head>
<body>
    <?php
    // Include navbar (usa navbar-attivita.php se esiste, altrimenti navbar.php)
    if (file_exists('novbar-attivita.php')) {
        include 'novbar-attivita.php';
    } else {
        // Optionally, include a default navbar or handle the case where neither file exists
        echo "<p>Navbar not found.</p>";
    }
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">I Tuoi Progressi</h1>
            <a href="obiettivo_aggiungi.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuovo Obiettivo
            </a>
        </div>
        
        <!-- Statistiche principali -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card progress-card h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="completion-ring">
                                <canvas id="completionChart"></canvas>
                            </div>
                        </div>
                        <h3 class="mb-1"><?= $completion_rate ?>%</h3>
                        <p class="text-muted mb-0">Completamento Obiettivi</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card progress-card h-100">
                    <div class="card-body text-center">
                        <h1 class="display-4 mb-1"><?= $stats['completed_goals'] ?></h1>
                        <p class="text-muted mb-1">Obiettivi Completati</p>
                        <small class="text-muted">su <?= $stats['total_goals'] ?> totali</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grafico distribuzione per tipo -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Distribuzione per Tipo di Obiettivo</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Obiettivi recenti -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Obiettivi Recenti</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_goals->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($goal = $recent_goals->fetch_assoc()): ?>
                            <?php
                            $is_completed = isset($goal['completato']) ? $goal['completato'] == 1 : 
                                          (isset($goal['stato']) ? $goal['stato'] == 'completato' : false);
                            $is_expired = !$is_completed && strtotime($goal['data_scadenza']) < time();
                            ?>
                            <div class="list-group-item goal-card <?= $is_completed ? 'completed' : '' ?> <?= $is_expired ? 'expired' : '' ?>">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($goal['nome'] ?? $goal['titolo'] ?? 'Obiettivo senza nome') ?></h6>
                                        <small class="text-muted">
                                            Scadenza: <?= date('d/m/Y', strtotime($goal['data_scadenza'])) ?>
                                            <?php if ($is_expired): ?>
                                                <span class="badge bg-danger ms-2">Scaduto</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <?php if ($is_completed): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Completato
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-arrow-repeat"></i> In corso
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <?php if (!empty($goal['obiettivo_peso'])): ?>
                                        <span class="badge bg-primary me-1">Peso: <?= $goal['obiettivo_peso'] ?> kg</span>
                                    <?php endif; ?>
                                    <?php if (!empty($goal['obiettivo_passi'])): ?>
                                        <span class="badge bg-info me-1">Passi: <?= $goal['obiettivo_passi'] ?>/giorno</span>
                                    <?php endif; ?>
                                    <?php if (!empty($goal['obiettivo_attivita'])): ?>
                                        <span class="badge bg-secondary">Attività: <?= $goal['obiettivo_attivita'] ?> min</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-emoji-frown display-4 text-muted mb-3"></i>
                        <h5 class="text-secondary">Nessun obiettivo trovato</h5>
                        <p class="text-muted">Crea il tuo primo obiettivo per iniziare a monitorare i tuoi progressi</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Grafico completamento (anello)
        const completionCtx = document.getElementById('completionChart').getContext('2d');
        const completionChart = new Chart(completionCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [<?= $completion_rate ?>, <?= 100 - $completion_rate ?>],
                    backgroundColor: ['#2ecc71', '#ecf0f1'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
        
        // Grafico distribuzione per tipo
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        const typeChart = new Chart(typeCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    $goals_by_type->data_seek(0);
                    while ($type = $goals_by_type->fetch_assoc()): 
                        echo "'".$type['goal_type']."',";
                    endwhile;
                    ?>
                ],
                datasets: [{
                    label: 'Numero di Obiettivi',
                    data: [
                        <?php 
                        $goals_by_type->data_seek(0);
                        while ($type = $goals_by_type->fetch_assoc()): 
                            echo $type['count'].",";
                        endwhile;
                        ?>
                    ],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#f39c12',
                        '#9b59b6',
                        '#e74c3c'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
    </script>
</body>
</html>
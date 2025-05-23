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
    <title>FitTrack - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Stili */
        body {
            background-color: #f8f9fa;
        }
        .stat-card, .activity-card, .chart-container, .quick-link {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card {
            border-left: 4px solid #3498db;
        }
        .quick-link {
            display: block;
            text-decoration: none;
            color: #2c3e50;
            transition: 0.3s;
        }
        .quick-link:hover {
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body>
<?php include 'novbar.php'; ?>
<div class="container mt-4 mb-5">
    <div class="row g-4 mb-4">
        <div class="col-md-3 stat-card">
            <h5><?= $stats['totale_attivita'] ?></h5><p>Attività totali</p>
        </div>
        <div class="col-md-3 stat-card">
            <h5><?= $stats['calorie_totali'] ?? 0 ?></h5><p>Calorie bruciate</p>
        </div>
        <div class="col-md-3 stat-card">
            <h5><?= $stats['distanza_totale'] ?? 0 ?> km</h5><p>Distanza totale</p>
        </div>
        <div class="col-md-3 stat-card">
            <h5><?= explode(' ', $stats['ultima_attivita'])[0] ?></h5><p>Ultima attività</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-container mb-4">
                <h5>Andamento settimanale</h5>
                <canvas id="activityChart" height="150"></canvas>
            </div>
            <div class="chart-container">
                <h5>Attività recenti</h5>
                <?php while ($a = $attivita_recenti->fetch_assoc()): ?>
                    <div class="activity-card mb-2">
                        <strong><?= htmlspecialchars($a['tipo_attivita']) ?></strong><br>
                        <small><?= date('d/m/Y H:i', strtotime($a['data_attivita'])) ?></small><br>
                        <span><?= $a['durata'] ?> min - <?= $a['distanza'] ?> km</span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container mb-4">
                <h5>Attività preferite</h5>
                <canvas id="activityTypesChart" height="200"></canvas>
            </div>
            <div class="chart-container">
                <h5>Azioni rapide</h5>
                <a href="attivita_aggiungi.php" class="quick-link mb-2">➕ Nuova attività</a>
                <a href="calendario.php" class="quick-link mb-2">📅 Calendario</a>
                <a href="obiettivi.php" class="quick-link mb-2">🎯 Obiettivi</a>
                <a href="progressi.php" class="quick-link mb-2">📈 Progressi</a>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    // Chart 1 - Attività per giorno
    new Chart(document.getElementById('activityChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($giorni_settimana) ?>,
            datasets: [{
                label: 'Attività',
                data: <?= json_encode($dati_settimana) ?>,
                backgroundColor: '#3498db'
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Chart 2 - Tipi più frequenti
    new Chart(document.getElementById('activityTypesChart'), {
        type: 'doughnut',
        data: {
            labels: [<?php while($row = $tipi_frequenti->fetch_assoc()) echo '"' . $row['tipo_attivita'] . '",'; ?>],
            datasets: [{
                data: [<?php mysqli_data_seek($tipi_frequenti, 0); while($row = $tipi_frequenti->fetch_assoc()) echo $row['conteggio'] . ','; ?>],
                backgroundColor: ['#3498db', '#2ecc71', '#f1c40f', '#e67e22', '#9b59b6']
            }]
        }
    });
</script>
</body>
</html>

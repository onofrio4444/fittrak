<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}
include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

// Recupera attività per il calendario
$sql = "SELECT id, tipo_attivita, data_attivita, durata FROM attivita WHERE id_utente = ? ORDER BY data_attivita";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$attivita = $stmt->get_result();
$eventi = [];
while($row = $attivita->fetch_assoc()) {
    $eventi[] = [
        'title' => $row['tipo_attivita'] . ' (' . $row['durata'] . ' min)',
        'start' => $row['data_attivita'],
        'url' => 'attivita_modifica.php?id=' . $row['id'],
        'color' => '#3498db'
    ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario - FitTrack</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --light: #ecf0f1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-top: 0px;
        }
        
        .calendar-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .fc-event {
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .fc-event:hover {
            transform: scale(1.02);
        }
        
        .fc-toolbar-title {
            font-size: 1.25rem;
            color: var(--secondary);
        }
    </style>
</head>
<body>
    <?php 
    $current_page = 'calendario';
    include 'novbar-attivita.php'; 
    ?>

    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Calendario Attività</h1>
            <a href="attivita_aggiungi.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Nuova Attività
            </a>
        </div>
        
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/it.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'it',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: <?= json_encode($eventi) ?>,
            eventClick: function(info) {
                window.location.href = info.event.url;
            },
            eventDisplay: 'block',
            height: 'auto'
        });
        calendar.render();
    });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
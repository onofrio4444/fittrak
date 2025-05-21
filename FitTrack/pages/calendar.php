<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
include '../includes/header.php';
?>

<div class="container">
    <h1>Calendario Attività</h1>
    
    <div class="calendar-actions">
        <a href="add_event.php" class="btn">+ Aggiungi Evento</a>
    </div>
    
    <div id="calendar"></div>
</div>

<!-- FullCalendar CSS -->
<link href='../assets/vendor/fullcalendar/main.min.css' rel='stylesheet' />

<!-- FullCalendar JS -->
<script src='../assets/vendor/fullcalendar/main.min.js'></script>
<script src='../assets/vendor/fullcalendar/locales/it.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '../api/get_calendar_events.php',
        eventClick: function(info) {
            window.location.href = 'view_event.php?id=' + info.event.id;
        },
        dateClick: function(info) {
            window.location.href = 'add_event.php?date=' + info.dateStr;
        },
        locale: 'it',
        eventColor: '#378006',
        eventTimeFormat: { 
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }
    });
    calendar.render();
});
</script>

<?php include '../includes/footer.php'; ?>
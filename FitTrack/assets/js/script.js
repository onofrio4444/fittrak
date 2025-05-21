// Funzioni AJAX
function fetchActivities() {
    $.ajax({
        url: '../api/get_activities.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            updateActivitiesList(data);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching activities:', error);
        }
    });
}

function updateActivitiesList(activities) {
    const listContainer = $('.activities-list');
    listContainer.empty();
    
    if (activities.length === 0) {
        listContainer.append('<p>Nessuna attività trovata.</p>');
        return;
    }
    
    activities.forEach(activity => {
        const activityCard = `
            <div class="activity-card">
                <h3>${activity.title}</h3>
                <p>${activity.activity_type} - ${new Date(activity.start_time).toLocaleString()}</p>
                <p>Distanza: ${activity.distance} km - Durata: ${formatDuration(activity.duration)}</p>
            </div>
        `;
        listContainer.append(activityCard);
    });
}

function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

// Inizializzazione FullCalendar
$(document).ready(function() {
    if ($('#calendar').length) {
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
                alert('Event: ' + info.event.title);
            },
            dateClick: function(info) {
                // Apri modal per aggiungere nuovo evento
                $('#newEventModal').modal('show');
                $('#eventDate').val(info.dateStr);
            },
            locale: 'it'
        });
        calendar.render();
    }
    
    // Aggiorna attività ogni 30 secondi
    setInterval(fetchActivities, 30000);
    
    // Inizializza i tooltip
    $('[data-toggle="tooltip"]').tooltip();
});
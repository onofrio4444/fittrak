// Funzioni JavaScript per FitTracker

$(document).ready(function() {
    // Gestione form attività
    $('#activity-form').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '../includes/add_activity.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Attività aggiunta con successo!');
                    window.location.reload();
                } else {
                    alert('Errore: ' + response.message);
                }
            },
            error: function() {
                alert('Errore durante il salvataggio dell\'attività');
            }
        });
    });
    
    // Gestione calendario principale
    $('#full-calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'month',
        editable: true,
        events: '../includes/get_events.php',
        eventClick: function(calEvent, jsEvent, view) {
            // Mostra dettagli evento
            $('#event-modal .modal-title').text(calEvent.title);
            $('#event-modal .modal-body').html(`
                <p><strong>Tipo:</strong> ${calEvent.type}</p>
                <p><strong>Inizio:</strong> ${calEvent.start.format('DD/MM/YYYY HH:mm')}</p>
                <p><strong>Fine:</strong> ${calEvent.end.format('DD/MM/YYYY HH:mm')}</p>
            `);
            $('#event-modal').modal('show');
        },
        dayClick: function(date, jsEvent, view) {
            // Aggiungi nuovo evento
            $('#new-event-modal input[name="start_datetime"]').val(date.format('YYYY-MM-DD HH:mm'));
            $('#new-event-modal input[name="end_datetime"]').val(date.add(1, 'hours').format('YYYY-MM-DD HH:mm'));
            $('#new-event-modal').modal('show');
        }
    });
    
    // Gestione submit nuovo evento
    $('#new-event-form').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '../includes/add_event.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#new-event-modal').modal('hide');
                    $('#full-calendar').fullCalendar('refetchEvents');
                } else {
                    alert('Errore: ' + response.message);
                }
            },
            error: function() {
                alert('Errore durante il salvataggio dell\'evento');
            }
        });
    });
});

// Funzione per calcolare la durata tra due orari
function calculateDuration(startTime, endTime) {
    const start = new Date('1970-01-01T' + startTime + 'Z');
    const end = new Date('1970-01-01T' + endTime + 'Z');
    const diff = end - start;
    
    const hours = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
}
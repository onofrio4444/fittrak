document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'it',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '../api/calendar_events.php',
        eventClick: function(info) {
            // Mostra dettagli evento
            alert('Evento: ' + info.event.title + '\n' + 
                  'Inizio: ' + info.event.start.toLocaleString() + '\n' +
                  'Descrizione: ' + (info.event.extendedProps.description || 'Nessuna descrizione'));
        },
        dateClick: function(info) {
            // Aggiungi nuovo evento
            const title = prompt("Inserisci il titolo per la nuova attività/evento:");
            if (title) {
                addNewEvent(title, info.dateStr);
            }
        },
        editable: true,
        eventDrop: function(info) {
            updateEvent(info.event);
        },
        eventResize: function(info) {
            updateEvent(info.event);
        }
    });
    
    calendar.render();
    
    function addNewEvent(title, date) {
        fetch('../api/calendar_events.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: title,
                start: date,
                allDay: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                calendar.refetchEvents();
            } else {
                alert('Errore: ' + data.message);
            }
        })
        .catch(error => console.error('Errore:', error));
    }
    
    function updateEvent(event) {
        const eventData = {
            id: event.id,
            title: event.title,
            start: event.start.toISOString(),
            end: event.end ? event.end.toISOString() : null,
            allDay: event.allDay
        };
        
        fetch('../api/calendar_events.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(eventData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                alert('Errore durante l\'aggiornamento: ' + data.message);
                calendar.refetchEvents();
            }
        })
        .catch(error => {
            console.error('Errore:', error);
            calendar.refetchEvents();
        });
    }
});
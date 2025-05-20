document.addEventListener('DOMContentLoaded', function() {
    // Inizializza DataTable
    $('#activitiesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/it-IT.json'
        },
        order: [[0, 'desc']]
    });
    
    // Gestione eliminazione attività
    $('.delete-activity').click(function(e) {
        e.preventDefault();
        const activityId = $(this).data('id');
        
        if (confirm('Sei sicuro di voler eliminare questa attività?')) {
            deleteActivity(activityId);
        }
    });
    
    function deleteActivity(activityId) {
        $.ajax({
            url: '../api/activities.php',
            type: 'DELETE',
            data: { id: activityId },
            success: function(response) {
                if (response.status === 'success') {
                    alert('Attività eliminata con successo');
                    location.reload();
                } else {
                    alert('Errore: ' + response.message);
                }
            },
            error: function() {
                alert('Errore durante la richiesta');
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    // Inizializza il grafico
    const ctx = document.getElementById('weeklyStatsChart').getContext('2d');
    
    // Dati di esempio (sostituire con dati reali da AJAX)
    const weeklyStatsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'],
            datasets: [{
                label: 'Distanza (km)',
                data: [5, 7, 3, 8, 4, 10, 6],
                backgroundColor: 'rgba(74, 111, 165, 0.7)',
                borderColor: 'rgba(74, 111, 165, 1)',
                borderWidth: 1
            }, {
                label: 'Durata (min)',
                data: [30, 45, 20, 50, 35, 60, 40],
                backgroundColor: 'rgba(22, 96, 136, 0.7)',
                borderColor: 'rgba(22, 96, 136, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Carica dati reali via AJAX
    loadDashboardData();
});

function loadDashboardData() {
    fetch('../api/dashboard.php')
        .then(response => response.json())
        .then(data => {
            // Aggiorna il grafico con i dati reali
            // Aggiorna altre parti della dashboard
        })
        .catch(error => console.error('Errore:', error));
}
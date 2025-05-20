<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/activity_functions.php';

checkAuth();

$user_id = $_SESSION['user_id'];
$recent_activities = getUserActivities($user_id, 5);

include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="welcome-section">
        <h1>Benvenuto, <?php echo $_SESSION['username']; ?></h1>
        <p>Ecco le tue attività recenti e statistiche</p>
    </div>
    
    <div class="dashboard-grid">
        <div class="stats-card">
            <h2>Statistiche Settimanali</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-value" id="weekly-distance">0</span>
                    <span class="stat-label">km</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="weekly-duration">0:00</span>
                    <span class="stat-label">ore</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="weekly-calories">0</span>
                    <span class="stat-label">kcal</span>
                </div>
            </div>
        </div>
        
        <div class="recent-activities">
            <h2>Attività Recenti</h2>
            <div class="activities-list">
                <?php if (empty($recent_activities)): ?>
                    <p>Nessuna attività registrata. <a href="../pages/activities.php">Aggiungi la tua prima attività!</a></p>
                <?php else: ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-type <?php echo strtolower($activity['activity_type']); ?>">
                                <?php echo $activity['activity_type']; ?>
                            </div>
                            <div class="activity-details">
                                <h3><?php echo $activity['title']; ?></h3>
                                <p><?php echo date('d M Y', strtotime($activity['start_time'])); ?></p>
                                <div class="activity-stats">
                                    <span><?php echo $activity['distance']; ?> km</span>
                                    <span><?php echo $activity['duration']; ?></span>
                                    <span><?php echo $activity['calories']; ?> kcal</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="calendar-widget">
            <h2>Calendario</h2>
            <div id="mini-calendar"></div>
        </div>
    </div>
</div>

<script>
// Carica le statistiche settimanali via AJAX
$(document).ready(function() {
    $.ajax({
        url: '../includes/get_weekly_stats.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#weekly-distance').text(data.total_distance || '0');
            $('#weekly-duration').text(data.total_duration || '0:00');
            $('#weekly-calories').text(data.total_calories || '0');
        }
    });
    
    // Inizializza mini calendario
    $('#mini-calendar').fullCalendar({
        header: {
            left: 'prev',
            center: 'title',
            right: 'next'
        },
        defaultView: 'month',
        height: 'auto',
        events: '../includes/get_events.php'
    });
});
</script>

<?php include '../includes/footer.php'; ?>
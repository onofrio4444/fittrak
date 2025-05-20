<?php
require_once __DIR__ . '/../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);
$recent_activities = getUserActivities($user_id, 5);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="sidebar">
            <nav>
                <ul>
                    <li class="active"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="activities.php"><i class="fas fa-running"></i> Attività</a></li>
                    <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendario</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profilo</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Impostazioni</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <h1>Benvenuto, <?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?></h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Attività Recenti</h3>
                    <div class="activities-list">
                        <?php if (empty($recent_activities)): ?>
                            <p>Nessuna attività recente</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                        <span><?php echo $activity['activity_type']; ?> - <?php echo $activity['distance']; ?> km</span>
                                        <small><?php echo date('d/m/Y', strtotime($activity['start_time'])); ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <h3>Statistiche</h3>
                    <canvas id="weeklyStatsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
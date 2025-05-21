<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($user_id);

// Verifica che $user sia un array valido
if (!$user || !is_array($user)) {
    // Logga l'errore e mostra un messaggio generico
    error_log("Errore: Impossibile ottenere i dati dell'utente con ID: $user_id");
    $user = ['username' => 'Utente']; // Valore di fallback
}

// Ottieni attività recenti
$stmt = $pdo->prepare("SELECT * FROM activities WHERE user_id = ? ORDER BY start_time DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_activities = $stmt->fetchAll();

// Ottieni statistiche - inizializza con valori di default
$stats = [
    'total_activities' => 0,
    'total_duration' => 0,
    'total_distance' => 0,
    'total_calories' => 0
];

try {
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_activities,
        SUM(duration) as total_duration,
        SUM(distance) as total_distance,
        SUM(calories) as total_calories
        FROM activities 
        WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        $stats = array_merge($stats, $result);
    }
} catch (PDOException $e) {
    // Logga l'errore se necessario
    error_log("Errore nel recupero delle statistiche: " . $e->getMessage());
}

// Ottieni passi di oggi
$today = date('Y-m-d');
$steps = 0;
try {
    $stmt = $pdo->prepare("SELECT steps FROM daily_steps WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $today]);
    $steps_result = $stmt->fetchColumn();
    if ($steps_result !== false) {
        $steps = $steps_result;
    }
} catch (PDOException $e) {
    error_log("Errore nel recupero dei passi: " . $e->getMessage());
}

// Includi header
include '../includes/header.php';
?>

<div class="dashboard-container">
    <h1>Benvenuto, <?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?></h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Attività Totali</h3>
            <p><?php echo $stats['total_activities']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Distanza Totale</h3>
            <p><?php echo $stats['total_distance'] ?? 0; ?> km</p>
        </div>
        <div class="stat-card">
            <h3>Durata Totale</h3>
            <p><?php echo gmdate("H:i:s", $stats['total_duration']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Calorie Totali</h3>
            <p><?php echo $stats['total_calories']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Passi Oggi</h3>
            <p><?php echo $steps; ?></p>
        </div>
    </div>
    
    <h2>Attività Recenti</h2>
    <div class="activities-list">
        <?php if (empty($recent_activities)): ?>
            <p>Nessuna attività recente. <a href="activities.php">Aggiungi un'attività</a></p>
        <?php else: ?>
            <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-card">
                    <h3><?php echo htmlspecialchars($activity['title']); ?></h3>
                    <p><?php echo ucfirst($activity['activity_type']); ?> - 
                       <?php echo date('d/m/Y H:i', strtotime($activity['start_time'])); ?></p>
                    <p>Distanza: <?php echo $activity['distance']; ?> km - 
                       Durata: <?php echo gmdate("H:i:s", $activity['duration']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
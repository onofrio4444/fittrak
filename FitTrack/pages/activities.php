<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($user_id);

// Filtri
$activity_type = $_GET['type'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

// Query base
$query = "SELECT * FROM activities WHERE user_id = ?";
$params = [$user_id];

// Aggiungi filtri
if (!empty($activity_type)) {
    $query .= " AND activity_type = ?";
    $params[] = $activity_type;
}

if (!empty($date_from)) {
    $query .= " AND DATE(start_time) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(start_time) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY start_time DESC";

// Esegui query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$activities = $stmt->fetchAll();

// Tipi di attività per il filtro
$activity_types = [
    'running' => 'Corsa',
    'cycling' => 'Ciclismo',
    'swimming' => 'Nuoto',
    'walking' => 'Camminata',
    'hiking' => 'Escursionismo',
    'gym' => 'Palestra',
    'other' => 'Altro'
];

include '../includes/header.php';
?>

<div class="container">
    <h1>Le tue Attività</h1>
    
    <!-- Filtri -->
    <div class="filters card">
        <h3>Filtra attività</h3>
        <form method="get" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Tipo attività</label>
                    <select id="type" name="type">
                        <option value="">Tutte</option>
                        <?php foreach ($activity_types as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $activity_type === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="from">Da</label>
                    <input type="date" id="from" name="from" value="<?= $date_from ?>">
                </div>
                
                <div class="form-group">
                    <label for="to">A</label>
                    <input type="date" id="to" name="to" value="<?= $date_to ?>">
                </div>
            </div>
            
            <button type="submit" class="btn">Applica filtri</button>
            <a href="activities.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
    
    <!-- Lista attività -->
    <div class="activities-container">
        <div class="activities-header">
            <h2>Lista attività</h2>
            <a href="add_activity.php" class="btn">+ Aggiungi attività</a>
        </div>
        
        <?php if (empty($activities)): ?>
            <div class="no-results">
                <p>Nessuna attività trovata.</p>
            </div>
        <?php else: ?>
            <div class="activities-list">
                <?php foreach ($activities as $activity): ?>
                    <div class="activity-item card">
                        <div class="activity-header">
                            <h3><?= htmlspecialchars($activity['title']) ?></h3>
                            <span class="activity-type <?= $activity['activity_type'] ?>">
                                <?= $activity_types[$activity['activity_type']] ?? ucfirst($activity['activity_type']) ?>
                            </span>
                        </div>
                        
                        <div class="activity-details">
                            <div class="detail">
                                <span class="label">Data</span>
                                <span class="value"><?= date('d/m/Y H:i', strtotime($activity['start_time'])) ?></span>
                            </div>
                            
                            <div class="detail">
                                <span class="label">Durata</span>
                                <span class="value"><?= gmdate("H:i:s", $activity['duration']) ?></span>
                            </div>
                            
                            <div class="detail">
                                <span class="label">Distanza</span>
                                <span class="value"><?= $activity['distance'] ?> km</span>
                            </div>
                            
                            <div class="detail">
                                <span class="label">Calorie</span>
                                <span class="value"><?= $activity['calories'] ?? 'N/A' ?></span>
                            </div>
                        </div>
                        
                        <div class="activity-actions">
                            <a href="view_activity.php?id=<?= $activity['activity_id'] ?>" class="btn btn-sm">Dettagli</a>
                            <a href="edit_activity.php?id=<?= $activity['activity_id'] ?>" class="btn btn-sm btn-secondary">Modifica</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
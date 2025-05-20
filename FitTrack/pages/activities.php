<?php
require_once '../includes/config.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$activities = getUserActivities($user_id);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Le mie Attività</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Le mie Attività</h1>
                <a href="add_activity.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Aggiungi Attività
                </a>
            </div>
            
            <div class="activities-table">
                <table id="activitiesTable" class="display">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Titolo</th>
                            <th>Distanza (km)</th>
                            <th>Durata</th>
                            <th>Calorie</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($activity['start_time'])); ?></td>
                                <td><?php echo ucfirst($activity['activity_type']); ?></td>
                                <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                <td><?php echo $activity['distance'] ?? '0.00'; ?></td>
                                <td><?php echo $activity['duration']; ?></td>
                                <td><?php echo $activity['calories'] ?? '-'; ?></td>
                                <td>
                                    <a href="activity_detail.php?id=<?php echo $activity['activity_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger delete-activity" data-id="<?php echo $activity['activity_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="../assets/js/activities.js"></script>
</body>
</html>
<?php
require_once '../includes/config.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// Ottieni dati per il grafico (settimana corrente)
$start_of_week = date('Y-m-d', strtotime('monday this week'));
$end_of_week = date('Y-m-d', strtotime('sunday this week'));

$stmt = $pdo->prepare("SELECT 
    DAYNAME(start_time) as day, 
    SUM(distance) as total_distance, 
    SUM(TIME_TO_SEC(duration)/60 as total_duration_minutes
    FROM activities 
    WHERE user_id = ? 
    AND DATE(start_time) BETWEEN ? AND ?
    GROUP BY DAYNAME(start_time)
    ORDER BY start_time");
$stmt->execute([$user_id, $start_of_week, $end_of_week]);
$activity_data = $stmt->fetchAll();

// Formatta i dati per il grafico
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$formatted_data = [
    'labels' => ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'],
    'distance' => array_fill(0, 7, 0),
    'duration' => array_fill(0, 7, 0)
];

foreach ($activity_data as $data) {
    $index = array_search($data['day'], $days);
    if ($index !== false) {
        $formatted_data['distance'][$index] = (float)$data['total_distance'];
        $formatted_data['duration'][$index] = (float)$data['total_duration_minutes'];
    }
}

echo json_encode([
    'status' => 'success',
    'data' => $formatted_data
]);
?>
<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-t');

try {
    $stmt = $pdo->prepare("SELECT 
        event_id as id,
        title,
        start_datetime as start,
        end_datetime as end,
        color,
        event_type
        FROM calendar_events 
        WHERE user_id = ? AND 
        ((start_datetime BETWEEN ? AND ?) OR 
        (end_datetime BETWEEN ? AND ?) OR 
        (start_datetime <= ? AND end_datetime >= ?))");
    
    $stmt->execute([$user_id, $start, $end, $start, $end, $start, $end]);
    
    $events = $stmt->fetchAll();
    
    // Formatta per FullCalendar
    $formattedEvents = array_map(function($event) {
        return [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $event['start'],
            'end' => $event['end'],
            'color' => $event['color'],
            'extendedProps' => [
                'type' => $event['event_type']
            ]
        ];
    }, $events);
    
    echo json_encode($formattedEvents);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
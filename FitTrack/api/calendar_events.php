<?php
require_once '../includes/config.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// Ottieni eventi (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-t');
    
    $stmt = $pdo->prepare("SELECT 
        event_id as id,
        title,
        start_datetime as start,
        end_datetime as end,
        event_type,
        description,
        color
        FROM calendar_events 
        WHERE user_id = ? 
        AND (
            (start_datetime BETWEEN ? AND ?) 
            OR (end_datetime BETWEEN ? AND ?) 
            OR (start_datetime <= ? AND end_datetime >= ?)
        )");
    $stmt->execute([$user_id, $start, $end, $start, $end, $start, $end]);
    $events = $stmt->fetchAll();
    
    // Formatta per FullCalendar
    $formatted_events = [];
    foreach ($events as $event) {
        $formatted_events[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $event['start'],
            'end' => $event['end'],
            'color' => $event['color'],
            'description' => $event['description'],
            'extendedProps' => [
                'type' => $event['event_type']
            ]
        ];
    }
    
    echo json_encode($formatted_events);
    exit();
}

// Aggiungi evento (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['title']) || empty($input['start'])) {
        echo json_encode(['status' => 'error', 'message' => 'Titolo e data di inizio sono obbligatori']);
        exit();
    }
    
    $start = date('Y-m-d H:i:s', strtotime($input['start']));
    $end = isset($input['end']) ? date('Y-m-d H:i:s', strtotime($input['end'])) : $start;
    
    $stmt = $pdo->prepare("INSERT INTO calendar_events 
        (user_id, title, start_datetime, end_datetime, event_type, color)
        VALUES (?, ?, ?, ?, 'activity', ?)");
    
    if ($stmt->execute([$user_id, $input['title'], $start, $end, '#4a6fa5'])) {
        echo json_encode(['status' => 'success', 'event_id' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Errore durante il salvataggio']);
    }
    exit();
}

// Aggiorna evento (PUT)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID evento mancante']);
        exit();
    }
    
    $start = date('Y-m-d H:i:s', strtotime($input['start']));
    $end = isset($input['end']) ? date('Y-m-d H:i:s', strtotime($input['end'])) : $start;
    
    $stmt = $pdo->prepare("UPDATE calendar_events 
        SET title = ?, start_datetime = ?, end_datetime = ?
        WHERE event_id = ? AND user_id = ?");
    
    if ($stmt->execute([$input['title'], $start, $end, $input['id'], $user_id])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Errore durante l\'aggiornamento']);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Metodo non supportato']);
?>
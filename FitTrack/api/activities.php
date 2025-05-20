<?php
require_once '../includes/config.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// Ottieni tutte le attività (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE user_id = ? ORDER BY start_time DESC");
    $stmt->execute([$user_id]);
    $activities = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'data' => $activities]);
    exit();
}

// Aggiungi nuova attività (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required_fields = ['activity_type', 'title', 'start_time', 'end_time', 'duration'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['status' => 'error', 'message' => "Il campo $field è obbligatorio"]);
            exit();
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activities 
            (user_id, activity_type, title, description, distance, duration, calories, 
            elevation_gain, start_time, end_time, avg_heart_rate, max_heart_rate, weather_conditions)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            $user_id,
            $input['activity_type'],
            $input['title'],
            $input['description'] ?? null,
            $input['distance'] ?? null,
            $input['duration'],
            $input['calories'] ?? null,
            $input['elevation_gain'] ?? null,
            $input['start_time'],
            $input['end_time'],
            $input['avg_heart_rate'] ?? null,
            $input['max_heart_rate'] ?? null,
            $input['weather_conditions'] ?? null
        ]);
        
        if ($success) {
            echo json_encode([
                'status' => 'success',
                'activity_id' => $pdo->lastInsertId()
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Errore durante il salvataggio']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// Elimina attività (DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $activity_id = $input['id'] ?? null;
    
    if (!$activity_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID attività mancante']);
        exit();
    }
    
    // Verifica che l'attività appartenga all'utente
    $stmt = $pdo->prepare("SELECT user_id FROM activities WHERE activity_id = ?");
    $stmt->execute([$activity_id]);
    $activity = $stmt->fetch();
    
    if (!$activity || $activity['user_id'] != $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Attività non trovata o non autorizzata']);
        exit();
    }
    
    $stmt = $pdo->prepare("DELETE FROM activities WHERE activity_id = ?");
    if ($stmt->execute([$activity_id])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Errore durante l\'eliminazione']);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Metodo non supportato']);
?>
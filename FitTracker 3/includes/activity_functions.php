<?php
require_once 'db.php';

// Aggiungi una nuova attività
function addActivity($user_id, $activity_data) {
    global $db;
    
    $stmt = $db->prepare("INSERT INTO activities 
                          (user_id, activity_type, title, description, distance, duration, calories, 
                           elevation_gain, start_time, end_time, avg_heart_rate, max_heart_rate, weather_conditions, notes)
                          VALUES 
                          (:user_id, :activity_type, :title, :description, :distance, :duration, :calories, 
                           :elevation_gain, :start_time, :end_time, :avg_heart_rate, :max_heart_rate, :weather_conditions, :notes)");
    
    return $stmt->execute($activity_data);
}

// Ottieni tutte le attività di un utente
function getUserActivities($user_id, $limit = null) {
    global $db;
    
    $query = "SELECT * FROM activities WHERE user_id = :user_id ORDER BY start_time DESC";
    
    if ($limit) {
        $query .= " LIMIT :limit";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}

// Ottieni attività per il calendario
function getCalendarEvents($user_id, $start, $end) {
    global $db;
    
    $stmt = $db->prepare("SELECT 
                          event_id as id, 
                          title, 
                          start_datetime as start, 
                          end_datetime as end, 
                          event_type as type,
                          color
                          FROM calendar_events 
                          WHERE user_id = :user_id 
                          AND (
                              (start_datetime BETWEEN :start AND :end)
                              OR (end_datetime BETWEEN :start AND :end)
                              OR (start_datetime <= :start AND end_datetime >= :end)
                          )");
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':start' => $start,
        ':end' => $end
    ]);
    
    return $stmt->fetchAll();
}

// Aggiungi un evento al calendario
function addCalendarEvent($user_id, $event_data) {
    global $db;
    
    $stmt = $db->prepare("INSERT INTO calendar_events 
                          (user_id, title, description, start_datetime, end_datetime, event_type, color, is_recurring, recurring_pattern)
                          VALUES 
                          (:user_id, :title, :description, :start_datetime, :end_datetime, :event_type, :color, :is_recurring, :recurring_pattern)");
    
    return $stmt->execute($event_data);
}
?>
<?php
require_once 'config.php';

// Registrazione utente
function registerUser($username, $email, $password, $first_name, $last_name) {
    global $pdo;
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name) 
                          VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$username, $email, $password_hash, $first_name, $last_name]);
}

// Login utente
function loginUser($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT user_id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

// Ottieni dati utente
function getUserData($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Aggiungi attività
function addActivity($user_id, $data) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO activities 
                          (user_id, activity_type, title, description, distance, duration, calories, 
                          elevation_gain, start_time, end_time, avg_heart_rate, max_heart_rate, weather_conditions)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $user_id,
        $data['activity_type'],
        $data['title'],
        $data['description'],
        $data['distance'],
        $data['duration'],
        $data['calories'],
        $data['elevation_gain'],
        $data['start_time'],
        $data['end_time'],
        $data['avg_heart_rate'],
        $data['max_heart_rate'],
        $data['weather_conditions']
    ]);
}

// Ottieni attività utente
function getUserActivities($user_id, $limit = null) {
    global $pdo;
    
    $sql = "SELECT * FROM activities WHERE user_id = ? ORDER BY start_time DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Altre funzioni per obiettivi, eventi calendario, statistiche...
?>
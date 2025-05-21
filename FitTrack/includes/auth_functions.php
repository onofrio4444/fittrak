<?php
require_once 'config.php';

function register_user($username, $email, $password) {
    global $pdo;
    
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $email, $hashed_password]);
}

function login_user($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

function get_user_data($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        return $user ? $user : false;
    } catch (PDOException $e) {
        error_log("Errore database in get_user_data(): " . $e->getMessage());
        return false;
    }
}
?>
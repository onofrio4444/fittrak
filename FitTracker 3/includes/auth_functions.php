<?php
require_once 'db.php';

// Registrazione utente
function registerUser($username, $email, $password, $first_name, $last_name) {
    global $db;
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name) 
                          VALUES (:username, :email, :password_hash, :first_name, :last_name)");
    
    return $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $password_hash,
        ':first_name' => $first_name,
        ':last_name' => $last_name
    ]);
}

// Login utente
function loginUser($username, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT user_id, username, password_hash FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

// Controlla se l'username esiste già
function usernameExists($username) {
    global $db;
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    return $stmt->fetchColumn() > 0;
}

// Controlla se l'email esiste già
function emailExists($email) {
    global $db;
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    return $stmt->fetchColumn() > 0;
}

// Genera un token per il reset password
function generatePasswordResetToken($email) {
    global $db;
    
    // Verifica se l'email esiste
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return false; // Per sicurezza non riveliamo che l'email non esiste
    }
    
    // Genera token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Salva nel database
    $stmt = $db->prepare("UPDATE users SET reset_token = :token, reset_token_expires = :expires WHERE user_id = :user_id");
    $result = $stmt->execute([
        ':token' => $token,
        ':expires' => $expires,
        ':user_id' => $user['user_id']
    ]);
    
    return $result ? $token : false;
}

// Verifica la validità del token di reset
function validatePasswordResetToken($token) {
    global $db;
    
    $stmt = $db->prepare("SELECT user_id FROM users WHERE reset_token = :token AND reset_token_expires > NOW()");
    $stmt->execute([':token' => $token]);
    return $stmt->fetch();
}

// Resetta la password
function resetPassword($token, $new_password) {
    global $db;
    
    // Verifica il token
    $user = validatePasswordResetToken($token);
    if (!$user) {
        return false;
    }
    
    // Aggiorna la password
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("UPDATE users SET password_hash = :password_hash, reset_token = NULL, reset_token_expires = NULL WHERE user_id = :user_id");
    return $stmt->execute([
        ':password_hash' => $password_hash,
        ':user_id' => $user['user_id']
    ]);
}
?>


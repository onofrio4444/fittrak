<?php
// Configurazioni del database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fitrack_db');

// Configurazioni del sito
define('SITE_NAME', 'FitTrack');
define('SITE_URL', 'http://localhost/FitTrack');

// Avvia la sessione
session_start();

// Connessione al database
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connessione al database fallita: " . $e->getMessage());
}

// Funzioni di utilità
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
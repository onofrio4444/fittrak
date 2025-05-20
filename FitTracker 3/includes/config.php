<?php
// Configurazioni base
define('BASE_URL', 'http://localhost/FitTracker/');
define('SITE_NAME', 'FitTracker');

// Impostazioni di sessione
session_start();

// Controlla se l'utente è loggato per le pagine che richiedono autenticazione
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit();
    }
}
?>
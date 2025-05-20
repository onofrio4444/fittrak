<?php
require_once 'includes/config.php';

// Se l'utente è già loggato, reindirizza alla dashboard
if (isLoggedIn()) {
    header("Location: pages/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Monitora le tue attività</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="landing-header">
        <div class="logo">
            <h1>FitTrack</h1>
        </div>
        <nav>
            <a href="auth/login.php" class="btn btn-outline">Accedi</a>
            <a href="auth/register.php" class="btn btn-primary">Registrati</a>
        </nav>
    </header>

    <main class="landing-container">
        <section class="hero">
            <div class="hero-content">
                <h2>Monitora le tue attività, raggiungi i tuoi obiettivi</h2>
                <p>FitTrack ti aiuta a tenere traccia di tutte le tue attività fisiche, analizzare le tue performance e migliorare i tuoi risultati.</p>
                <div class="hero-buttons">
                    <a href="auth/register.php" class="btn btn-primary btn-large">Inizia ora</a>
                    <a href="#features" class="btn btn-outline btn-large">Scopri di più</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/fitness-tracker.png" alt="Monitoraggio attività">
            </div>
        </section>

        <section id="features" class="features">
            <h2>Le nostre funzionalità</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-running"></i>
                    <h3>Tracciamento attività</h3>
                    <p>Registra corsa, ciclismo, nuoto e altro ancora con dati dettagliati.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Calendario integrato</h3>
                    <p>Organizza e pianifica i tuoi allenamenti con il nostro calendario.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bullseye"></i>
                    <h3>Obiettivi personalizzati</h3>
                    <p>Imposta obiettivi e monitora i tuoi progressi nel tempo.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Statistiche avanzate</h3>
                    <p>Analisi dettagliate delle tue performance con grafici intuitivi.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="footer-content">
            <div class="footer-logo">
                <h3>FitTrack</h3>
                <p>Il tuo compagno di allenamento personale</p>
            </div>
            <div class="footer-links">
                <div class="link-group">
                    <h4>Navigazione</h4>
                    <a href="index.php">Home</a>
                    <a href="#features">Funzionalità</a>
                    <a href="auth/login.php">Accedi</a>
                    <a href="auth/register.php">Registrati</a>
                </div>
                <div class="link-group">
                    <h4>Legal</h4>
                    <a href="#">Termini di servizio</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Cookie Policy</a>
                </div>
                <div class="link-group">
                    <h4>Contatti</h4>
                    <a href="mailto:support@fittrack.example">Supporto</a>
                    <a href="#">Facebook</a>
                    <a href="#">Instagram</a>
                    <a href="#">Twitter</a>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <p>&copy; <?php echo date('Y'); ?> FitTrack. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
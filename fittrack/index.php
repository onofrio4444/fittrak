<?php
session_start();
require 'connessione.php';

// Recupera statistiche utente (se loggato)
$statistiche = [];
if (isset($_SESSION['utente_id'])) {
    $id_utente = $_SESSION['utente_id'];
    
    // Totale attività
    $sql = "SELECT COUNT(*) as totale FROM attivita WHERE id_utente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $statistiche['totale_attivita'] = $stmt->get_result()->fetch_assoc()['totale'];
    $stmt->close();
    
    // Ultima attività
    $sql = "SELECT tipo_attivita, data_attivita FROM attivita 
            WHERE id_utente = ? 
            ORDER BY data_attivita DESC 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $ultima = $stmt->get_result()->fetch_assoc();
    $statistiche['ultima_attivita'] = $ultima ? $ultima['tipo_attivita'] : 'Nessuna';
    $statistiche['ultima_data'] = $ultima ? date('d/m/Y', strtotime($ultima['data_attivita'])) : '-';
    $stmt->close();
    
    // Calorie totali
    $sql = "SELECT SUM(calorie) as calorie FROM attivita WHERE id_utente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $statistiche['calorie_totali'] = $stmt->get_result()->fetch_assoc()['calorie'] ?? 0;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Il tuo compagno fitness</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: var(--secondary);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 5rem 0;
            margin-bottom: 3rem;
            border-radius: 0 0 20px 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .cta-section {
            background-color: var(--secondary);
            color: white;
            padding: 4rem 0;
            margin-top: 3rem;
            border-radius: 20px 20px 0 0;
        }
        
        .btn-custom {
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary-custom {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-custom {
            border: 2px solid white;
            color: white;
        }
        
        .btn-outline-custom:hover {
            background-color: white;
            color: var(--secondary);
        }
        
        .activity-badge {
            background-color: var(--light);
            color: var(--dark);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-block;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Il tuo compagno di fitness personale</h1>
            <p class="lead mb-5">Monitora i tuoi progressi, raggiungi i tuoi obiettivi e trasforma il tuo stile di vita</p>
            
            <?php if (isset($_SESSION['utente_id'])): ?>
                <a href="dashboard.php" class="btn btn-light btn-lg btn-custom me-2">
                    <i class="fas fa-tachometer-alt me-2"></i>Vai alla Dashboard
                </a>
                <a href="attivita_aggiungi.php" class="btn btn-outline-light btn-lg btn-custom">
                    <i class="fas fa-plus me-2"></i>Aggiungi Attività
                </a>
            <?php else: ?>
                <a href="registrazione.php" class="btn btn-light btn-lg btn-custom me-2">
                    <i class="fas fa-user-plus me-2"></i>Registrati
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg btn-custom">
                    <i class="fas fa-sign-in-alt me-2"></i>Accedi
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Statistiche (visibili solo se loggato) -->
    <?php if (isset($_SESSION['utente_id'])): ?>
    <section class="container mb-5">
        <h2 class="text-center mb-4">Le tue statistiche</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-running"></i>
                    <h3><?= $statistiche['totale_attivita'] ?></h3>
                    <p class="text-muted">Attività registrate</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-fire"></i>
                    <h3><?= $statistiche['calorie_totali'] ?></h3>
                    <p class="text-muted">Calorie bruciate</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-history"></i>
                    <h3><?= $statistiche['ultima_attivita'] ?></h3>
                    <p class="text-muted">Ultima attività (<?= $statistiche['ultima_data'] ?>)</p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Features -->
    <section class="container mb-5">
        <h2 class="text-center mb-5">Perché scegliere FitTrack?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Monitoraggio Completo</h3>
                    <p>Registra ogni tipo di attività fisica e monitora i tuoi progressi nel tempo con grafici dettagliati.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Obiettivi Personalizzati</h3>
                    <p>Imposta obiettivi di allenamento e ricevi promemoria per mantenerti motivato.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Accesso Ovunque</h3>
                    <p>Accedi ai tuoi dati da qualsiasi dispositivo, quando e dove vuoi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tipi di attività -->
    <section class="container mb-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="mb-4">Supportiamo tutti i tuoi allenamenti</h2>
                <p>FitTrack ti permette di registrare qualsiasi tipo di attività fisica, dalla corsa al nuoto, dal sollevamento pesi allo yoga.</p>
                
                <div class="mt-4">
                    <span class="activity-badge"><i class="fas fa-running me-2"></i>Corsa</span>
                    <span class="activity-badge"><i class="fas fa-biking me-2"></i>Ciclismo</span>
                    <span class="activity-badge"><i class="fas fa-swimmer me-2"></i>Nuoto</span>
                    <span class="activity-badge"><i class="fas fa-dumbbell me-2"></i>Palestra</span>
                    <span class="activity-badge"><i class="fas fa-spa me-2"></i>Yoga</span>
                    <span class="activity-badge"><i class="fas fa-walking me-2"></i>Camminata</span>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" 
                     alt="Attività fisiche" class="img-fluid rounded-3 shadow">
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section text-center">
        <div class="container">
            <h2 class="display-5 fw-bold mb-4">Pronto a trasformare il tuo allenamento?</h2>
            <p class="lead mb-5">Unisciti a migliaia di persone che hanno già migliorato le loro prestazioni con FitTrack</p>
            
            <?php if (isset($_SESSION['utente_id'])): ?>
                <a href="attivita_aggiungi.php" class="btn btn-primary btn-custom btn-primary-custom me-2">
                    <i class="fas fa-plus me-2"></i>Aggiungi la tua prima attività
                </a>
            <?php else: ?>
                <a href="registrazione.php" class="btn btn-primary btn-custom btn-primary-custom me-2">
                    <i class="fas fa-user-plus me-2"></i>Crea un account gratuito
                </a>
                <a href="login.php" class="btn btn-outline-custom btn-custom">
                    <i class="fas fa-sign-in-alt me-2"></i>Accedi
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animazioni semplici
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = 1;
                }, 200 * index);
            });
        });
    </script>
</body>
</html>
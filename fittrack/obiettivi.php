<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}
include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

// Prima verifichiamo la struttura della tabella obiettivi
$check_columns = "SHOW COLUMNS FROM obiettivi";
$result_columns = $conn->query($check_columns);
$columns = [];
while ($row = $result_columns->fetch_assoc()) {
    $columns[] = $row['Field'];
}

// Determina quale colonna usare per l'ordinamento
$order_column = 'id'; // default
if (in_array('data_scadenza', $columns)) {
    $order_column = 'data_scadenza';
} elseif (in_array('scadenza', $columns)) {
    $order_column = 'scadenza';
} elseif (in_array('data_creazione', $columns)) {
    $order_column = 'data_creazione';
}

// Recupera obiettivi
$sql = "SELECT * FROM obiettivi WHERE id_utente = ? ORDER BY $order_column";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$obiettivi = $stmt->get_result();

// Calcola progresso
$sql_progresso = "SELECT
    COUNT(*) as completati,
    (SELECT COUNT(*) FROM obiettivi WHERE id_utente = ?) as totali
    FROM obiettivi
    WHERE id_utente = ? AND (completato = 1 OR stato = 'completato')";
$stmt_p = $conn->prepare($sql_progresso);
$stmt_p->bind_param("ii", $id_utente, $id_utente);
$stmt_p->execute();
$progresso = $stmt_p->get_result()->fetch_assoc();
$percentuale = $progresso['totali'] > 0 ? round(($progresso['completati'] / $progresso['totali']) * 100) : 0;

// Funzione per formattare la data
function formatDate($date_string, $columns) {
    if (empty($date_string)) return 'Non specificata';

    // Prova diversi formati di data
    $timestamp = strtotime($date_string);
    if ($timestamp !== false) {
        return date('d/m/Y', $timestamp);
    }
    return $date_string;
}

// Funzione per verificare se un obiettivo è completato
function isCompleted($obiettivo, $columns) {
    if (in_array('completato', $columns) && $obiettivo['completato'] == 1) {
        return true;
    }
    if (in_array('stato', $columns) && $obiettivo['stato'] == 'completato') {
        return true;
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obiettivi - FitTrack</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #3498db;
            --success: #2ecc71;
            --light: #ecf0f1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .goal-card {
            border-left: 4px solid var(--primary);
            transition: transform 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            background: white;
        }

        .goal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .completed {
            border-left-color: var(--success);
            background-color: rgba(46, 204, 113, 0.05);
        }

        .progress-container {
            height: 10px;
            border-radius: 5px;
            background-color: var(--light);
        }

        .progress-bar {
            height: 100%;
            border-radius: 5px;
            background-color: var(--success);
            transition: width 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .text-primary {
            color: var(--primary) !important;
        }

        .badge.bg-success {
            background-color: var(--success) !important;
        }

        .badge.bg-primary {
            background-color: var(--primary) !important;
        }
    </style>
</head>
<body>
    <?php
    // Include navbar (usa navbar-attivita.php se esiste, altrimenti navbar.php)
    if (file_exists('novbar-attivita.php')) {
        include 'novbar-attivita.php';
    } else {
        // Optionally, include a default navbar or handle the case where neither file exists
        echo "<p>Navbar not found.</p>";
    }
    ?>

    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">I Tuoi Obiettivi</h1>
            <a href="obiettivo_aggiungi.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Nuovo Obiettivo
            </a>
        </div>

        <!-- Progresso complessivo -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-secondary">Progresso Generale</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><?= $percentuale ?>% completati</span>
                    <span class="text-muted"><?= $progresso['completati'] ?> di <?= $progresso['totali'] ?></span>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?= $percentuale ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Lista obiettivi -->
        <div class="row g-4">
            <?php if ($obiettivi->num_rows > 0): ?>
                <?php while ($ob = $obiettivi->fetch_assoc()): ?>
                    <?php $completato = isCompleted($ob, $columns); ?>
                    <div class="col-md-6">
                        <div class="card goal-card mb-3 <?= $completato ? 'completed' : '' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">
                                        <?= htmlspecialchars($ob['titolo'] ?? $ob['nome'] ?? 'Obiettivo') ?>
                                    </h5>
                                    <?php if ($completato): ?>
                                        <span class="badge bg-success rounded-pill">
                                            <i class="bi bi-check-circle-fill"></i> Completato
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary rounded-pill">
                                            <i class="bi bi-arrow-repeat"></i> In corso
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (isset($ob['descrizione']) && !empty($ob['descrizione'])): ?>
                                    <p class="card-text text-muted"><?= htmlspecialchars($ob['descrizione']) ?></p>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i>
                                        <?php
                                        $date_field = '';
                                        if (isset($ob['data_scadenza'])) $date_field = $ob['data_scadenza'];
                                        elseif (isset($ob['scadenza'])) $date_field = $ob['scadenza'];
                                        elseif (isset($ob['data_creazione'])) $date_field = $ob['data_creazione'];
                                        echo formatDate($date_field, $columns);
                                        ?>
                                    </small>
                                    <div>
                                        <?php if (file_exists('obiettivo_modifica.php')): ?>
                                            <a href="obiettivo_modifica.php?id=<?= $ob['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Modifica">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!$completato): ?>
                                            <?php if (file_exists('obiettivo_completa.php')): ?>
                                                <a href="obiettivo_completa.php?id=<?= $ob['id'] ?>" class="btn btn-sm btn-outline-success" title="Segna come completato">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                            <?php else: ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="completa_obiettivo" value="<?= $ob['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Segna come completato">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <a href="?elimina=<?= $ob['id'] ?>" class="btn btn-sm btn-outline-danger ms-1"
                                           onclick="return confirm('Sei sicuro di voler eliminare questo obiettivo?')" title="Elimina">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-emoji-frown display-4 text-muted mb-3"></i>
                            <h5 class="text-secondary">Nessun obiettivo trovato</h5>
                            <p class="text-muted">Crea il tuo primo obiettivo per iniziare a monitorare i tuoi progressi</p>
                            <a href="obiettivo_aggiungi.php" class="btn btn-primary mt-3">
                                <i class="bi bi-plus-circle"></i> Crea Obiettivo
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // Gestione eliminazione obiettivo
    if (isset($_GET['elimina'])) {
        $id_elimina = (int)$_GET['elimina'];
        $sql_elimina = "DELETE FROM obiettivi WHERE id = ? AND id_utente = ?";
        $stmt_elimina = $conn->prepare($sql_elimina);
        $stmt_elimina->bind_param("ii", $id_elimina, $id_utente);
        if ($stmt_elimina->execute()) {
            echo "<script>alert('Obiettivo eliminato con successo!'); window.location.href='obiettivi.php';</script>";
        }
    }

    // Gestione completamento obiettivo
    if (isset($_POST['completa_obiettivo'])) {
        $id_completa = (int)$_POST['completa_obiettivo'];

        if (in_array('completato', $columns)) {
            $sql_completa = "UPDATE obiettivi SET completato = 1 WHERE id = ? AND id_utente = ?";
        } elseif (in_array('stato', $columns)) {
            $sql_completa = "UPDATE obiettivi SET stato = 'completato' WHERE id = ? AND id_utente = ?";
        } else {
            $sql_completa = "UPDATE obiettivi SET completato = 1 WHERE id = ? AND id_utente = ?";
        }

        $stmt_completa = $conn->prepare($sql_completa);
        $stmt_completa->bind_param("ii", $id_completa, $id_utente);
        if ($stmt_completa->execute()) {
            echo "<script>alert('Obiettivo completato!'); window.location.href='obiettivi.php';</script>";
        }
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'footer.php'; ?>
</body>
</html>

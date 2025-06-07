<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politica sulla Privacy - FitTrack</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h2 {
            color: #2c3e50;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
    </style>
</head>
<body>
   

    <div class="container">
        <h1>Politica sulla Privacy</h1>

        <div class="card">
            <h2>Introduzione</h2>
            <p>
                Benvenuti nella Politica sulla Privacy di FitTrack. Questa pagina vi informa sulle nostre politiche riguardanti la raccolta, l'uso e la divulgazione delle informazioni personali quando si utilizza il nostro servizio.
            </p>
        </div>

        <div class="card">
            <h2>Informazioni che raccogliamo</h2>
            <p>
                Raccogliamo diverse tipologie di informazioni per fornire e migliorare il nostro servizio. Queste includono informazioni personali fornite volontariamente dagli utenti, come nome, indirizzo email, e dati di fitness.
            </p>
        </div>

        <div class="card">
            <h2>Utilizzo delle Informazioni</h2>
            <p>
                Le informazioni raccolte vengono utilizzate per fornire, mantenere e migliorare il nostro servizio, per rispondere alle richieste degli utenti, e per fornire assistenza clienti.
            </p>
        </div>

        <div class="card">
            <h2>Condivisione delle Informazioni</h2>
            <p>
                Non vendiamo, scambiamo o trasferiamo a terzi le informazioni personali degli utenti. Questo non include terze parti fidate che ci assistono nell'operare il nostro sito web o nel condurre la nostra attività, purché queste parti accettino di mantenere riservate queste informazioni.
            </p>
        </div>

        <div class="card">
            <h2>Sicurezza delle Informazioni</h2>
            <p>
                Implementiamo una varietà di misure di sicurezza per mantenere sicure le informazioni personali degli utenti quando vengono inserite, inviate o accessibili.
            </p>
        </div>

        <div class="card">
            <h2>Diritto degli Utenti</h2>
            <p>
                Gli utenti hanno il diritto di accedere, correggere o cancellare le loro informazioni personali raccolte da noi. Per esercitare questi diritti, è possibile contattarci tramite le informazioni fornite nella nostra pagina dei contatti.
            </p>
        </div>

        <div class="button-group">
            <a href="dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
        </div>
    </div>
</body>
</html>

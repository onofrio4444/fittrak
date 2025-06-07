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
    <title>Chi Siamo - FitTrack</title>
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

        h1,
        h2 {
            color: #2c3e50;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .team-member {
            text-align: center;
            margin-bottom: 20px;
        }

        .team-member img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
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
        <h1>Chi Siamo</h1>

        <div class="card">
            <h2>La Nostra Missione</h2>

            <p>

            </p>
            <p>
                FitTrack è dedicato a migliorare la salute e il benessere delle persone attraverso la tecnologia.
                Crediamo che ogni individuo meriti di vivere una vita sana e attiva e il nostro obiettivo
                è fornire gli strumenti necessari per raggiungere questo scopo.
            </p>
        </div>




        <div class="card">
            <h2>La Nostra Storia</h2>
            <p>
                Questo sito è il prodotto di un compito sui sistemi Wearable assegnato alla nostra classe dell'ITIS "Nervi-Galilei" di Altamura dalla professoressa Incampo Angela .
            </p>
            <p>
                <strong>FitTrack</strong> è stato fondato nel 2025 con l'obiettivo di creare una piattaforma che rendesse il fitness accessibile a tutti.
                Da allora, abbiamo aiutato migliaia di persone a raggiungere i propri obiettivi di salute e forma fisica, tracciando i loro allenamenti.
            </p>

            <p>
                L'idea è nata da me, <strong>Onofrio Cutecchia</strong>: desideravo realizzare un'app che mi permettesse di tenere traccia dei miei allenamenti,
                programmarli su un calendario e utilizzare un dispositivo <em>wearable</em>, come uno smartwatch, per inviare i dati delle attività sportive all'applicazione.
            </p>

            <p>
                Per sviluppare questo sito web, ho utilizzato un database <strong>MySQL</strong> per la gestione dei dati e un server <strong>Apache</strong> per l'esecuzione in locale.
                Ho scritto il codice con linguaggi come <strong>PHP</strong>, <strong>HTML</strong> e <strong>CSS</strong>.
            </p>

            <p>
                Nella realizzazione di questo progetto non mi sono arreso davanti ai numerosi ostacoli incontrati lungo il percorso e ho cercato di rendere il sito
                 accattivante e facilmente fruibile da tutti i ragazzi che come me amano lo sport.
                Ringrazio la professoressa Incampo per averci lasciato libertà creativa, permettendoci di esprimere appieno la nostra fantasia nello sviluppo del sito.
            </p>

        </div>

        <div class="button-group">
            <a href="dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>
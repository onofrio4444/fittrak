<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

$errori = [];
$successo = false;

// Simulate synchronization process
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sincronizza'])) {
    // Here you would typically have code to interact with the wearable device API
    // For example, fetching data from the device and storing it in the database

    // Simulate a successful sync
    $successo = true;
    $_SESSION['messaggio_successo'] = "Sincronizzazione completata con successo!";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronizza Dispositivo - FitTrack</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
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

        .error {
            color: #e74c3c;
            margin-top: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    

    <div class="container">
        <h1>Sincronizza il Tuo Dispositivo Wearable</h1>

        <?php if ($successo): ?>
            <div class="success">Sincronizzazione completata con successo!</div>
        <?php endif; ?>

        <?php if (!empty($errori)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errori as $errore): ?>
                        <li><?= htmlspecialchars($errore) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <p>Collega il tuo dispositivo wearable e clicca il pulsante sottostante per sincronizzare i dati.</p>

            <div id="syncing" style="display: none;">
                <div class="spinner"></div>
                <p>Attendere il collegamento del dispositivo...</p>
            </div>

            <div class="button-group">
                <form method="POST">
                    <button type="button" id="sincronizza" class="btn">Sincronizza Dispositivo</button>
                </form>
                <a href="dashboard.php" class="btn btn-secondary">Indietro</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sincronizza').addEventListener('click', function() {
            document.getElementById('syncing').style.display = 'block';
            this.style.display = 'none';

            // Simulate the synchronization process
            setTimeout(function() {
                document.querySelector('form').submit();
            }, 1000000); // Simulate a 3-second delay
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>

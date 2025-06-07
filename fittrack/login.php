<?php
session_start();

include 'connessione.php';

// Inizializza variabili per messaggi di errore/successo
$errori = [];
$login_success = false;

// Gestione del form di login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validazione
    if (empty($username)) $errori[] = "Username obbligatorio.";
    if (empty($password)) $errori[] = "Password obbligatoria.";

    if (empty($errori)) {
        // Query per verificare l'utente
        $sql = "SELECT id, username, password FROM utenti WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $utente = $result->fetch_assoc();

            // Verifica la password
            if (password_verify($password, $utente['password'])) {
                // Login riuscito: crea la sessione
                $_SESSION['utente_id'] = $utente['id'];
                $_SESSION['utente_username'] = $utente['username'];
                $login_success = true;

                // Reindirizza alla dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $errori[] = "Password errata.";
            }
        } else {
            $errori[] = "Utente non trovato.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitTrack</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #1e88e5;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #1565c0;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .text-center {
            text-align: center;
            margin-top: 20px;
        }
        .text-center a {
            color: #1e88e5;
            text-decoration: none;
        }
        .text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Accedi a FitTrack</h2>

        <!-- Mostra messaggi di errore -->
        <?php if (!empty($errori)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errori as $errore): ?>
                        <li><?php echo htmlspecialchars($errore); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Mostra messaggio di successo dalla registrazione -->
        <?php if (isset($_SESSION['messaggio_successo'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['messaggio_successo']); ?>
                <?php unset($_SESSION['messaggio_successo']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Accedi</button>
        </form>

        <div class="text-center">
            <p>Non hai un account? <a href="registrazione.php">Registrati qui</a></p>
            <p><a href="password_dimenticata.php">Password dimenticata?</a></p>
        </div>
    </div>
</body>
</html>

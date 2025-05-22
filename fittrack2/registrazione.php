<?php
session_start();
include 'connessione.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$errori = [];
$successo = false;

// Gestione del form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    // Recupera e sanitizza i dati
    $username = trim($_POST['username']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $conferma_password = $_POST['conferma_password'];
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $data_nascita = $_POST['data_nascita'];
    $sesso = $_POST['sesso'];

    // Validazione
    if (empty($username)) $errori[] = "Username obbligatorio.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errori[] = "Email non valida.";
    if (strlen($password) < 8) $errori[] = "Password troppo corta (min. 8 caratteri).";
    if ($password !== $conferma_password) $errori[] = "Le password non coincidono.";
    if (!empty($data_nascita) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_nascita)) {
        $errori[] = "Formato data non valido (usa YYYY-MM-DD).";
    }

    // Controlla se username/email esistono già
    $sql_check = "SELECT id FROM utenti WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) $errori[] = "Username o email già registrati.";
    $stmt_check->close();

    // Registrazione se non ci sono errori
    if (empty($errori)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql_insert = "INSERT INTO utenti (username, email, password, nome, cognome, data_nascita, sesso)
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssssss", $username, $email, $password_hash, $nome, $cognome, $data_nascita, $sesso);

        if ($stmt_insert->execute()) {
            // Crea record nelle impostazioni di default per l'utente
            $id_utente = $stmt_insert->insert_id;
            $sql_impostazioni = "INSERT INTO impostazioni (id_utente) VALUES (?)";
            $stmt_impostazioni = $conn->prepare($sql_impostazioni);
            $stmt_impostazioni->bind_param("i", $id_utente);
            $stmt_impostazioni->execute();
            $stmt_impostazioni->close();

            $_SESSION['messaggio_successo'] = "Registrazione completata! Ora puoi accedere.";
            header("Location: login.php");
            exit();
        } else {
            $errori[] = "Errore durante la registrazione: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - FitTrack</title>
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
            max-width: 600px;
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
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select {
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
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: -20px;
        }
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Crea il tuo account FitTrack</h2>
<br>
        <?php if (!empty($errori)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errori as $errore): ?>
                        <li><?php echo htmlspecialchars($errore); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="registrazione.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">Username*</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
            </div>
<br>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password* (min. 8 caratteri)</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="conferma_password">Conferma Password*</label>
                        <input type="password" id="conferma_password" name="conferma_password" required>
                    </div>
                </div>
            </div>
<br>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cognome">Cognome</label>
                        <input type="text" id="cognome" name="cognome">
                    </div>
                </div>
            </div>
                        <br>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="data_nascita">Data di nascita</label>
                        <input type="date" id="data_nascita" name="data_nascita">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sesso">Sesso</label>
                        <select id="sesso" name="sesso">
                            <option value="" selected>Seleziona...</option>
                            <option value="M">Maschio</option>
                            <option value="F">Femmina</option>
                            <option value="Altro">Altro</option>
                        </select>
                    </div>
                </div>
            </div>
            <br>

            <button type="submit">Registrati</button>
        </form>

        <div class="text-center">
            <p>Hai già un account? <a href="login.php">Accedi qui</a></p>
        </div>
    </div>
</body>
</html>

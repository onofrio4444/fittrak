<?php
session_start();
include 'connessione.php';
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
    <title>Registrazione - Fittrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 15px; box-shadow: 0 6px 10px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #007bff, #00bfff); color: white; }
        .btn-primary { background: linear-gradient(135deg, #007bff, #00bfff); border: none; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center py-3">
                        <h2>Crea il tuo account Fittrack</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errori)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errori as $errore): ?>
                                        <li><?php echo htmlspecialchars($errore); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="registrazione.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username*</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email*</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password* (min. 8 caratteri)</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="conferma_password" class="form-label">Conferma Password*</label>
                                    <input type="password" class="form-control" id="conferma_password" name="conferma_password" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cognome" class="form-label">Cognome</label>
                                    <input type="text" class="form-control" id="cognome" name="cognome">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="data_nascita" class="form-label">Data di nascita</label>
                                    <input type="date" class="form-control" id="data_nascita" name="data_nascita">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sesso" class="form-label">Sesso</label>
                                    <select class="form-select" id="sesso" name="sesso">
                                        <option value="" selected>Seleziona...</option>
                                        <option value="M">Maschio</option>
                                        <option value="F">Femmina</option>
                                        <option value="Altro">Altro</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mt-3">Registrati</button>
                        </form>

                        <div class="text-center mt-3">
                            <p>Hai già un account? <a href="login.php">Accedi qui</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
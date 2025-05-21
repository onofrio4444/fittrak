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
    <title>Login - Fittrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 15px; box-shadow: 0 6px 10px rgba(0,0,0,0.1); }
        .card-header { 
            background: linear-gradient(135deg, #007bff, #00bfff); 
            color: white; 
            border-radius: 15px 15px 0 0 !important;
        }
        .btn-primary { 
            background: linear-gradient(135deg, #007bff, #00bfff); 
            border: none; 
        }
        .btn-primary:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center py-3">
                        <h2>Accedi a Fittrack</h2>
                    </div>
                    <div class="card-body p-4">
                        <!-- Mostra messaggi di errore -->
                        <?php if (!empty($errori)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
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
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Accedi</button>
                        </form>

                        <div class="text-center mt-3">
                            <p>Non hai un account? <a href="registrazione.php">Registrati qui</a></p>
                            <p><a href="password_dimenticata.php">Password dimenticata?</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
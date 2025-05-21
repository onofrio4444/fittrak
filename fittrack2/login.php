<?php
session_start();
include 'connessione.php';

$errore = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM utenti WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $utente = $res->fetch_assoc();
        if (password_verify($password, $utente['password'])) {
            $_SESSION['id_utente'] = $utente['id'];
            $_SESSION['username'] = $utente['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $errore = "Password errata.";
        }
    } else {
        $errore = "Email non trovata.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - FitTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Login</h1>
    <?php if ($errore) echo "<p style='color:red;'>$errore</p>"; ?>
    <form method="POST" action="login.php">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" class="btn">Accedi</button>
    </form>
    <p>Non hai un account? <a href="registrazione.php">Registrati</a></p>
</div>
<?php include 'footer.php'; ?>
</body>
</html>

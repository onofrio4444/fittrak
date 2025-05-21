<?php
include 'connessione.php';
$errore = $successo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "SELECT id FROM utenti WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errore = "Email già registrata.";
    } else {
        $sql = "INSERT INTO utenti (username, email, password, data_registrazione) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $successo = "Registrazione avvenuta con successo! <a href='login.php'>Accedi ora</a>";
        } else {
            $errore = "Errore nella registrazione.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione - FitTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Registrazione</h1>
    <?php
    if ($errore) echo "<p style='color:red;'>$errore</p>";
    if ($successo) echo "<p style='color:green;'>$successo</p>";
    ?>
    <form method="POST" action="registrazione.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" class="btn">Registrati</button>
    </form>
    <p>Hai già un account? <a href="login.php">Accedi</a></p>
</div>
<?php include 'footer.php'; ?>
</body>
</html>

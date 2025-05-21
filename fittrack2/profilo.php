<?php
session_start();
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit();
}
include 'connessione.php';
$id_utente = $_SESSION['id_utente'];

// Recupera dati utente
$sql = "SELECT username, email, nome, cognome, data_nascita, sesso, data_registrazione FROM utenti WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo - FitTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Profilo Utente</h1>

    <div class="profilo-box">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($utente['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($utente['email']); ?></p>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($utente['nome']); ?></p>
        <p><strong>Cognome:</strong> <?php echo htmlspecialchars($utente['cognome']); ?></p>
        <p><strong>Data di nascita:</strong> <?php echo $utente['data_nascita'] ? date('d/m/Y', strtotime($utente['data_nascita'])) : '-'; ?></p>
        <p><strong>Sesso:</strong> <?php echo $utente['sesso']; ?></p>
        <p><strong>Registrato dal:</strong> <?php echo date('d/m/Y', strtotime($utente['data_registrazione'])); ?></p>
    </div>

    <a href="modifica_profilo.php" class="btn">Modifica Profilo</a>
</div>

<?php include 'footer.php'; ?>
</body>
</html>

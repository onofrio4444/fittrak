<?php
session_start();
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit();
}
include 'connessione.php';
$id_utente = $_SESSION['id_utente'];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>FitTrack - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Benvenuto in FitTrack, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

    <section class="riepilogo">
        <h2>Ultime attività</h2>
        <div class="attivita-box">
            <?php
            $sql = "SELECT tipo_attivita, durata, distanza, calorie, data_attivita 
                    FROM attivita 
                    WHERE id_utente = ? 
                    ORDER BY data_attivita DESC 
                    LIMIT 5";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_utente);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($riga = $result->fetch_assoc()) {
                    echo "<div class='attivita'>
                            <strong>" . htmlspecialchars($riga['tipo_attivita']) . "</strong><br>
                            Durata: " . $riga['durata'] . " min<br>
                            Distanza: " . $riga['distanza'] . " km<br>
                            Calorie: " . $riga['calorie'] . "<br>
                            Data: " . date('d/m/Y H:i', strtotime($riga['data_attivita'])) . "
                          </div>";
                }
            } else {
                echo "<p>Nessuna attività registrata.</p>";
            }
            ?>
        </div>
    </section>

    <section class="link-rapidi">
        <a href="attivita.php" class="btn">Gestisci Attività</a>
        <a href="calendario.php" class="btn">Calendario</a>
        <a href="profilo.php" class="btn">Profilo Utente</a>
        <a href="impostazioni.php" class="btn">Impostazioni</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </section>
</div>

<?php include 'footer.php'; ?>
</body>
</html>

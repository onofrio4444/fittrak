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
    <title>Le tue Attività - FitTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Le tue Attività</h1>

    <button id="syncBtn" class="btn">Simula Sincronizzazione</button>

    <div id="listaAttivita" class="attivita-box" style="margin-top: 2rem;">
        <!-- Le attività verranno caricate qui via AJAX -->
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    caricaAttivita();

    document.getElementById("syncBtn").addEventListener("click", function () {
        fetch('sync_attivita.php', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                caricaAttivita();
            } else {
                alert("Errore nella sincronizzazione");
            }
        });
    });

    function caricaAttivita() {
        fetch('carica_attivita.php')
            .then(res => res.text())
            .then(html => {
                document.getElementById('listaAttivita').innerHTML = html;
            });
    }
});
</script>

</body>
</html>

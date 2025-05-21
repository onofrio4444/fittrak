<?php
session_start();
if (!isset($_SESSION['id_utente'])) exit;
include 'connessione.php';
$id_utente = $_SESSION['id_utente'];

$sql = "SELECT tipo_attivita, durata, distanza, calorie, data_attivita 
        FROM attivita 
        WHERE id_utente = ? 
        ORDER BY data_attivita DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();

while ($riga = $result->fetch_assoc()) {
    echo "<div class='attivita'>
            <strong>" . htmlspecialchars($riga['tipo_attivita']) . "</strong><br>
            Durata: {$riga['durata']} min<br>
            Distanza: {$riga['distanza']} km<br>
            Calorie: {$riga['calorie']}<br>
            Data: " . date('d/m/Y H:i', strtotime($riga['data_attivita'])) . "
          </div>";
}

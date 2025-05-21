<?php
session_start();
if (!isset($_SESSION['id_utente'])) {
    echo json_encode(['success' => false]);
    exit;
}
include 'connessione.php';
$id_utente = $_SESSION['id_utente'];

$attivita = ["Corsa", "Bici", "Nuoto", "Camminata", "Escursionismo"];
$tipo = $attivita[array_rand($attivita)];
$durata = rand(20, 90);
$distanza = round($durata * (rand(5, 12) / 60), 2); // km/h simulata
$calorie = rand(200, 900);
$data = date('Y-m-d H:i:s');

$sql = "INSERT INTO attivita (id_utente, tipo_attivita, durata, distanza, calorie, data_attivita) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isiiis", $id_utente, $tipo, $durata, $distanza, $calorie, $data);
$eseguito = $stmt->execute();

echo json_encode(['success' => $eseguito]);

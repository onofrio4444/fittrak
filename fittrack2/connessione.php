<?php
$host = "localhost";
$db = "fittrack";
$user = "root";
$pass = ""; // se usi XAMPP lascia vuoto

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>

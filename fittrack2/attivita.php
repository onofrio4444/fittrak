<?php
session_start();
require 'connessione.php';

if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

$id_utente = $_SESSION['utente_id'];
$messaggio = '';

// Eliminazione attività
if (isset($_GET['elimina'])) {
    $id_attivita = $_GET['elimina'];
    $sql = "DELETE FROM attivita WHERE id = ? AND id_utente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_attivita, $id_utente);
    
    if ($stmt->execute()) {
        $messaggio = '<div class="success">Attività eliminata con successo!</div>';
    } else {
        $messaggio = '<div class="error">Errore durante l\'eliminazione</div>';
    }
}

// Ricerca e filtri
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_data = $_GET['data'] ?? '';

$sql = "SELECT * FROM attivita WHERE id_utente = ?";
$params = array($id_utente);
$types = "i";

if (!empty($filtro_tipo)) {
    $sql .= " AND tipo_attivita = ?";
    $params[] = $filtro_tipo;
    $types .= "s";
}

if (!empty($filtro_data)) {
    $sql .= " AND DATE(data_attivita) = ?";
    $params[] = $filtro_data;
    $types .= "s";
}

$sql .= " ORDER BY data_attivita DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$attivita = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Le tue attività - FitTrack</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .filtri {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .filtri label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .filtri select, .filtri input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filtri button {
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filtri button:hover {
            background-color: #2980b9;
        }
        .btn-nuova {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn-nuova:hover {
            background-color: #27ae60;
        }
        .tabella-attivita {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .tabella-attivita th, 
        .tabella-attivita td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .tabella-attivita th {
            background-color: #3498db;
            color: white;
        }
        .tabella-attivita tr:hover {
            background-color: #f9f9f9;
        }
        .azioni a {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
        }
        .modifica {
            background-color: #f39c12;
            color: white;
        }
        .modifica:hover {
            background-color: #e67e22;
        }
        .elimina {
            background-color: #e74c3c;
            color: white;
        }
        .elimina:hover {
            background-color: #c0392b;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

         
    </style>
</head>
<body>
<?php include "novbar-attivita.php"?>

<div class="container">
    <h1>Le tue attività</h1>
    
    <?php echo $messaggio; ?>
    
    <div class="filtri">
        <div>
            <label for="tipo">Filtra per tipo:</label>
            <select id="tipo" name="tipo" onchange="applicaFiltri()">
                <option value="">Tutti i tipi</option>
                <option value="Corsa" <?= $filtro_tipo == 'Corsa' ? 'selected' : '' ?>>Corsa</option>
                <option value="Ciclismo" <?= $filtro_tipo == 'Ciclismo' ? 'selected' : '' ?>>Ciclismo</option>
                <option value="Nuoto" <?= $filtro_tipo == 'Nuoto' ? 'selected' : '' ?>>Nuoto</option>
                <option value="Palestra" <?= $filtro_tipo == 'Palestra' ? 'selected' : '' ?>>Palestra</option>
            </select>
        </div>
        <div>
            <label for="data">Filtra per data:</label>
            <input type="date" id="data" name="data" value="<?= $filtro_data ?>" onchange="applicaFiltri()">
        </div>
        <div style="align-self: flex-end;">
            <button onclick="resettaFiltri()">Reset filtri</button>
        </div>
    </div>
    
    <a href="attivita_aggiungi.php" class="btn-nuova">+ Nuova Attività</a>
    
    <table class="tabella-attivita">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Durata (min)</th>
                <th>Distanza (km)</th>
                <th>Calorie</th>
                <th>Data/Ora</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($attivita->num_rows > 0): ?>
                <?php while ($att = $attivita->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($att['tipo_attivita']) ?></td>
                        <td><?= $att['durata'] ?></td>
                        <td><?= $att['distanza'] ?? '-' ?></td>
                        <td><?= $att['calorie'] ?? '-' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($att['data_attivita'])) ?></td>
                        <td class="azioni">
                            <a href="attivita_modifica.php?id=<?= $att['id'] ?>" class="modifica">Modifica</a>
                            <a href="attivita.php?elimina=<?= $att['id'] ?>" class="elimina" 
                               onclick="return confirm('Sei sicuro di voler eliminare questa attività?')">Elimina</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Nessuna attività trovata</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

<script>
function applicaFiltri() {
    const tipo = document.getElementById('tipo').value;
    const data = document.getElementById('data').value;
    
    let url = 'attivita.php?';
    if (tipo) url += `tipo=${tipo}&`;
    if (data) url += `data=${data}`;
    
    window.location.href = url;
}

function resettaFiltri() {
    window.location.href = 'attivita.php';
}
</script>
</body>
</html>